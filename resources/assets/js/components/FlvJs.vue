<template>
    <div class="layout">
        <Layout>
            <player-header :url="'/videojs/' + liveId"></player-header>
            <Content style="padding: 16px 32px;">
                <div class="player-container">
                    <Spin size="large" fix v-if="spinShow"></Spin>

                    <Card>
                        <p slot="title">{{subTitle}}</p>
                        <p slot="extra">{{title}}</p>

                        <video class="video" id="liveVideo" ref="video"></video>

                        <player-controls :volume="volume" :is-muted="isMuted" :show-progress="isReview"
                                :is-playing="isPlaying" :volume-disabled="volumeDisabled"
                                @play="play" @pause="pause" @mute="mute" @progress="progressChange"
                                :current-time="currentTime"
                                :duration="duration"></player-controls>
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
                title:'',
                subTitle:'',
                status:STATUS_PREPARED,
                volume:80,
                isMuted:false,
                volumeDisabled:false,
                duration:0,
                currentTime:0,
                isReview:true,      //是否回放
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
        methods:{
            getOne:function(){
                axios.get('/api/live/' + this.liveId).then(res =>{
                    if(res.data.errorCode === 0){
                        this.streamPath = res.data.data.streamPath;
                        this.title = res.data.data.title;
                        this.subTitle = res.data.data.subTitle;

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

                    //时长
                    this.flvPlayer.on(this.$flvjs.Events.MEDIA_INFO, media =>{
                        this.duration = media.duration / 1000;
                    });
                    //当前进度
                    this.$refs.video.addEventListener('timeupdate', event =>{
                        this.currentTime = event.target.currentTime;
                    });
                    //播放结束
                    this.$refs.video.addEventListener('ended', event =>{
                        this.status = STATUS_PREPARED;
                        this.$Notice.info({
                           title:'播放完毕',
                           desc:''
                        });
                    });

                    this.flvPlayer.attachMediaElement(videoElement);
                    this.flvPlayer.load();
                    this.spinShow = false;
                    this.flvPlayer.volume = this.volume * 0.01;

                    this.play();
                }
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
            cancelMute:function(){
                this.flvPlayer.volume = this.volume * 0.01;
                this.isMuted = false;
                this.volumeDisabled = false;
            },
            getType:function(url){
                this.$Notice.info({
                    title:'当前视频地址：',
                    desc:url
                });
                if(url.includes('.mp4')){
                    return 'mp4';
                }else if(url.includes('.flv')){
                    return 'flv';
                }
            },
            progressChange:function(progress){
                this.flvPlayer.currentTime = progress;
            }
        }
    }
</script>

<style scoped>
    .player-container {
        display: flex;
        justify-content: center;
    }

    .video {
        min-height: 400px;
        min-width: 320px;
        max-height: 720px;
    }
</style>