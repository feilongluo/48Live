<template>
    <div class="layout">
        <Layout>
            <player-header :other-player="'/videojs/' + liveId" :video-url="streamPath"></player-header>
            <Content style="padding: 16px 32px;">
                <div class="player-container">
                    <Spin size="large" fix v-if="spinShow"></Spin>

                    <Card>
                        <p slot="title">{{subTitle}}</p>
                        <p slot="extra">{{title}}</p>

                        <video class="video" id="liveVideo" ref="video"></video>


                        <player-controls ref="controls" :is-muted="isMuted" :show-progress="isReview"
                                :is-playing="isPlaying" :volume-disabled="volumeDisabled"
                                @play="play" @pause="pause" @mute="mute" @unmute="unmute" @progress="progressChange"
                                @volume="volumeChange"
                                :current-time="currentTime"
                                :duration="duration"></player-controls>
                    </Card>

                    <Card style="flex: 1 0 auto;margin-left: 16px;">
                        <p slot="title">弹幕</p>
                        <div class="barrage-container" ref="barrage"></div>
                    </Card>
                </div>
            </Content>
        </Layout>
    </div>
</template>

<script>
    import PlayerHeader from "./PlayerHeader";
    import PlayerControls from "./PlayerControls";


    const STATUS_PLAYING = 1;
    const STATUS_PREPARED = 0;

    export default {
        name:'FlvJs',
        components:{PlayerControls, PlayerHeader},
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
            this.liveId = this.$route.params.liveId;
            this.getOne();
        },
        mounted:function(){
            this.send = this.$start(this.$refs.barrage);
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
                        if(this.inRange(this.timeToSecond(this.currentBarrage.time), this.currentTime)){
                            this.currentBarrage = this.barrageList.shift();
                            this.send({
                                text:this.currentBarrage.content,
                                speed:3,
                                classname:'style1'
                            });
                        }
                    });
                    //播放结束
                    this.$refs.video.addEventListener('ended', event =>{
                        this.status = STATUS_PREPARED;
                        this.$Notice.info({
                            title:'播放完毕',
                            desc:''
                        });
                    });
                    this.flvPlayer.load();

                    if(this.isReview){
                        this.getBarrages();
                    }else{
                        this.spinShow = false;
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
                        //时长
                        this.flvPlayer.on(this.$flvjs.Events.MEDIA_INFO, media =>{
                            this.duration = media.duration / 1000;
                        });

                        this.spinShow = false;
                    }else{
                        this.$Message.error(res.data.msg);
                    }
                }).catch(error =>{
                    this.$Message.error(error);
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
                const hours = time.split(':')[0];
                const minutes = time.split(':')[1];
                const seconds = time.split(':')[2];
                return Number(hours) * 3600 + Number(minutes) * 60 + Number(seconds);
            },
            inRange(barrageTime, videoTime){  //弹幕误差2秒
                console.log(barrageTime + ' , ' + videoTime);
                return barrageTime > videoTime - 2 && barrageTime < videoTime + 2;
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
        min-height: 400px;
        min-width: 320px;
        max-height: 640px;
    }
</style>