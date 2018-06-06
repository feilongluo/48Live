<template>
    <div class="layout">
        <Layout>
            <player-header :other-player="'/flvjs/' + liveId" :video-url="streamPath"></player-header>
            <Content style="padding: 16px 32px;">
                <div class="player-container">
                    <Spin size="large" fix v-if="spinShow"></Spin>

                    <Card>
                        <p slot="title">{{subTitle}}</p>
                        <p slot="extra">{{title}}</p>

                        <video-player ref="videoPlayer" class="video" :options="playerOptions"></video-player>

                        <player-controls ref="controls" :is-muted="isMuted" :show-progress="isReview"
                                :is-playing="isPlaying" :volume-disabled="volumeDisabled"
                                @play="play" @pause="pause" @mute="mute" @unmute="unmute" @progress="progressChange"
                                @volume="onVolumeChange"
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
        name:'VideoJs',
        components:{PlayerControls, PlayerHeader},
        data(){
            return {
                playerOptions:{
                    autoplay:false, // 自动播放
                    controls:false, // 是否显示控制栏
                    techOrder:['flash', 'html5'], // 兼容顺序
                    sourceOrder:true, //
                    flash:{hls:{withCredentials:false}},
                    html5:{hls:{withCredentials:false}},
                    sources:[{
                        withCredentials:false,
                        type:'',
                        src:''
                    }],
                },
                spinShow:true,
                liveId:'',
                streamPath:'',
                barrageUrl:'',
                title:'',
                subTitle:'',
                status:STATUS_PREPARED,
                volume:0,
                isMuted:false,
                volumeDisabled:false,
                duration:0,
                currentTime:0,
                isReview:true,      //是否回放
                currentBarrage:{},
                finalBarrageList:[],
                barrageList:[],
            }
        },
        computed:{
            isPlaying:function(){
                return this.status === STATUS_PLAYING;
            },
            player(){
                return this.$refs.videoPlayer.player;
            }
        },
        watch:{
            volume:function(newVolume){
                this.player.volume(newVolume * 0.01);
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
                    if(res.data.errorCode == 0){
                        this.streamPath = res.data.data.streamPath;
                        this.title = res.data.data.title;
                        this.subTitle = res.data.data.subTitle;
                        this.isReview = res.data.data.isReview;
                        this.barrageUrl = 'http://source.48.cn' + res.data.data.lrcPath;

                        this.player.volume(this.$refs.controls.volume * 0.01);

                        this.player.src({
                            type:this.getType(this.streamPath),
                            src:this.streamPath
                        });
                        //时长
                        this.player.on('loadeddata', event =>{
                            this.duration = event.target.player.duration();

                            if(this.isReview){
                                this.getBarrages();
                            }
                        });
                        //当前进度
                        this.player.on('timeupdate', event =>{
                            this.currentTime = event.target.player.currentTime();
                            //弹幕
                            this.loadBarrages();
                        });
                        //播放结束
                        this.player.on('ended', () =>{
                            this.status = STATUS_PREPARED;
                            this.$Notice.info({
                                title:'播放完毕',
                                desc:''
                            });
                        });
                        this.spinShow = false;
                    }else{
                        this.$Message.error(res.data.msg);
                    }
                }).catch(error =>{
                    this.spinShow = false;
                    console.log(error);
                });
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
                    }else{
                        this.$Message.error(res.data.msg);
                    }
                }).catch(error =>{
                    this.$Message.error('弹幕加载失败');
                    console.log(error);
                });
            },
            play:function(){
                this.player.play();
                this.status = STATUS_PLAYING;
            },
            pause:function(){
                this.player.pause();
                this.status = STATUS_PREPARED;
            },
            mute:function(){
                this.player.volume(0);
                this.isMuted = true;
                this.volumeDisabled = true;
            },
            unmute:function(){
                this.player.volume(this.volume * 0.01);
                this.isMuted = false;
                this.volumeDisabled = false;
            },
            onVolumeChange:function(volume){
                this.volume = volume
            },
            getType:function(url){
                if(url.includes('.mp4')){
                    return 'video/mp4';
                }else if(url.includes('.m3u8')){
                    return 'application/x-mpegURL';
                }
            },
            progressChange:function(progress){
                this.player.currentTime(progress);
                //重新加载弹幕
                this.barrageList = [];
                this.finalBarrageList.forEach(item =>{
                    if(this.timeToSecond(item.time) - 2 > progress){
                        this.barrageList.push(item);
                    }
                });
                this.currentBarrage = this.barrageList.shift();
            },
            timeToSecond:function(time){
                const hours = time.split(':')[0];
                const minutes = time.split(':')[1];
                const seconds = time.split(':')[2];
                return Number(hours) * 3600 + Number(minutes) * 60 + Number(seconds);
            },
            loadBarrages:function(){
                const barrageTime = this.timeToSecond(this.currentBarrage.time);
                if(barrageTime > this.currentTime - 1 && barrageTime < this.currentTime + 1){ //弹幕可误差1秒
                    this.send({
                        text:this.currentBarrage.content,
                        speed:3,
                        classname:'style1'
                    });
                    this.currentBarrage = this.barrageList.shift();
                    this.loadBarrages();
                }
            },
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
        display: flex;
        justify-content: center;
    }
</style>