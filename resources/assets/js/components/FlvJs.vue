<template>
    <div class="layout">
        <Layout>
            <player-header :other-player="'/videojs/' + liveId" :video-url="streamPath"></player-header>
            <Content style="padding: 16px;">
                <div class="player-container">
                    <Spin size="large" fix v-if="spinShow"></Spin>

                    <Card>
                        <p slot="title">{{subTitle}}</p>
                        <p slot="extra">{{title}}</p>

                        <Carousel class="video" v-if="isRadio" autoplay loop :autoplay-speed="8000">
                            <CarouselItem  v-for="picture in pictures" :key="picture">
                                <img class="picture" :src="picture">
                            </CarouselItem>
                        </Carousel>

                        <video class="video" id="liveVideo" ref="video" v-else></video>

                        <player-controls ref="controls" :show-play-button="isReview" :is-muted="isMuted"
                                :show-progress="isReview"
                                :is-playing="isPlaying" :volume-disabled="volumeDisabled"
                                @play="play" @pause="pause" @mute="mute" @unmute="unmute" @progress="progressChange"
                                @volume="volumeChange"
                                :current-time="currentTime"
                                :duration="duration"></player-controls>
                    </Card>

                    <Card style="flex: 1 0 auto;margin-left: 16px;">
                        <p slot="title">弹幕</p>
                        <barrage ref="barrage" class="barrage-container"></barrage>
                    </Card>
                </div>
            </Content>
        </Layout>
    </div>
</template>

<script>
    import PlayerHeader from './PlayerHeader';
    import PlayerControls from './PlayerControls';
    import ChatRoom from '../48chatroom';
    import Barrage from "./Barrage";
    import Casitem from "iview/src/components/cascader/casitem";
    import Tools from "../tools";

    const STATUS_PLAYING = 1;
    const STATUS_PREPARED = 0;

    export default {
        name:'FlvJs',
        components:{Casitem, Barrage, PlayerControls, PlayerHeader},
        data(){
            return {
                spinShow:true,
                liveId:'',
                streamPath:'',
                flvPlayer:null,
                barrageUrl:'',
                title:'',
                subTitle:'',
                status:STATUS_PREPARED,
                isMuted:false,
                volumeDisabled:false,
                duration:0,
                currentTime:0,
                isReview:true,      //是否回放
                volume:0,
                currentBarrage:{},
                finalBarrageList:[],
                barrageList:[],
                roomId:'',
                isRadio:false,
                pictures:[],
                pictureIndex:0,
            }
        },
        computed:{
            isPlaying:function(){
                return this.status === STATUS_PLAYING;
            }
        },
        watch:{
            volume:function(newVolume){
                this.flvPlayer.volume = newVolume * 0.01;
            }
        },
        created:function(){
            this.$Notice.config({
                top:80
            });
            this.liveId = this.$route.params.liveId;
            this.getOne();
        },
        methods:{
            getOne:function(){
                axios.get('/api/live/' + this.liveId).then(res =>{
                    if(res.data.errorCode === 0){
                        this.streamPath = res.data.data.streamPath;
                        this.title = res.data.data.title;
                        this.subTitle = res.data.data.subTitle;
                        this.isReview = res.data.data.isReview;
                        this.barrageUrl = 'http://source.48.cn' + res.data.data.lrcPath;
                        this.roomId = res.data.data.roomId;
                        this.isRadio = res.data.data.liveType == 2;

                        this.pictures = Tools.pictureUrls(res.data.data.picPath);

                        this.init();
                    }else{
                        this.$Message.error(res.data.msg);
                    }
                }).catch(error =>{
                    this.spinShow = false;
                    console.log(error);
                });
            },
            init:function(){
                if(this.$flvjs.isSupported()){
                    const videoElement = document.getElementById('liveVideo');
                    this.flvPlayer = this.$flvjs.createPlayer({
                        type:this.getType(this.streamPath),
                        url:this.streamPath
                    });
                    this.flvPlayer.attachMediaElement(videoElement);
                    this.flvPlayer.volume = this.$refs.controls.volume * 0.01;

                    //当前进度
                    this.$refs.video.addEventListener('timeupdate', event =>{
                        this.currentTime = event.target.currentTime;
                        //弹幕
                        if(this.isReview){  //录播
                            this.loadBarrages();
                        }

                    });
                    //播放结束
                    this.$refs.video.addEventListener('ended', () =>{
                        this.status = STATUS_PREPARED;
                        this.$Notice.info({
                            title:'播放完毕',
                            desc:''
                        });
                    });

                    if(this.isReview){  //录播
                        this.getBarrages();
                    }else{              //直播
                        this.connectChatRoom();
                    }
                }
            },
            getBarrages:function(){
                axios.get('/api/barrage', {
                    params:{
                        barrageUrl:this.barrageUrl
                    }
                }).then(res =>{
                    if(res.data.errorCode == 0){
                        this.finalBarrageList = this.barrageList = res.data.data.barrages;
                        this.currentBarrage = this.barrageList.shift();

                        this.$Notice.success({
                            title:'弹幕已加载',
                            desc:''
                        });
                    }else{
                        this.$Notice.error({
                            title:res.data.msg,
                            desc:''
                        });
                    }
                    //时长
                    this.flvPlayer.on(this.$flvjs.Events.MEDIA_INFO, media =>{
                        this.duration = media.duration / 1000;
                    });
                    this.flvPlayer.load();
                    this.spinShow = false;
                }).catch(error =>{
                    this.$Notice.error('弹幕加载失败');
                    console.log(error);
                });
            },
            play:function(){
                this.flvPlayer.play();
                this.status = STATUS_PLAYING;
            },
            pause:function(){
                this.flvPlayer.pause();
                this.status = STATUS_PREPARED;
            },
            mute:function(){
                this.flvPlayer.volume = 0;
                this.isMuted = true;
                this.volumeDisabled = true;
            },
            unmute:function(){
                this.flvPlayer.volume = this.volume * 0.01;
                this.isMuted = false;
                this.volumeDisabled = false;
            },
            getType:function(url){
                if(url.includes('.mp4')){
                    return 'mp4';
                }else if(url.includes('.flv')){
                    return 'flv';
                }
            },
            progressChange:function(progress){
                this.flvPlayer.currentTime = progress;
                //重新加载弹幕
                this.barrageList = [];
                this.finalBarrageList.forEach(item =>{
                    if(this.timeToSecond(item.time) - 2 > progress){
                        this.barrageList.push(item);
                    }
                });
                this.currentBarrage = this.barrageList.shift();
            },
            volumeChange:function(volume){
                this.volume = volume;
            },
            timeToSecond:function(time){
                if(!time) return;
                const hours = time.split(':')[0];
                const minutes = time.split(':')[1];
                const seconds = time.split(':')[2];
                return Number(hours) * 3600 + Number(minutes) * 60 + Number(seconds);
            },
            loadBarrages:function(){
                const barrageTime = this.timeToSecond(this.currentBarrage.time);
                if(barrageTime > this.currentTime - 1 && barrageTime < this.currentTime + 1){ //弹幕可误差1秒
                    this.$refs.barrage.shoot({
                        content:this.currentBarrage.content,
                        username:this.currentBarrage.username
                    });
                    this.currentBarrage = this.barrageList.shift();
                    this.loadBarrages();
                }
            },
            //连接聊天室
            connectChatRoom:function(){
                this.chatRoom = new ChatRoom({
                    roomId:this.roomId,
                    onConnect:() =>{
                        this.$Notice.success({
                            title:'聊天室连接成功',
                            desc:''
                        });
                    },
                    onDisconnect:(message) =>{
                        this.$Notice.success({
                            title:'聊天室连接断开',
                            desc:''
                        });
                        console.log(message);
                    },
                    onWillConnect:() =>{

                    },
                    /**
                     * @link https://github.com/Jarvay/48Live/wiki/Chatroom-OnMessage
                     */
                    onMessage:messages =>{
                        messages.forEach(message =>{
                            if(message.type == 'text'){
                                const custom = JSON.parse(message.custom);
                                if(custom.contentType == 1){
                                    this.$refs.barrage.shoot({
                                        content:custom.content,
                                        username:custom.senderName
                                    });
                                }
                            }
                        });
                    },
                    onError:error =>{
                        console.log(error);
                    },
                    onTokenFailed:error =>{
                        this.$Notice.success({
                            title:'聊天室token获取失败',
                            desc:''
                        });
                        console.log(error);
                    },
                    onTokenSuccess:() =>{
                        this.flvPlayer.load();
                        this.spinShow = false;
                        this.play();
                    }
                });
            }
        }
    }
</script>

<style scoped>
    .player-container {
        display: flex;
    }

    .barrage-container {
        flex: 1 0 auto;
        width: 100%;
        height: 640px;
    }

    .video {
        width: 400px;
        height: 640px;
    }
</style>