<template>
    <div class="layout">
        <Layout>
            <player-header :url="'/flvjs/' + liveId"></player-header>
            <Content style="padding: 16px 32px;">
                <div class="player-container">
                    <Spin size="large" fix v-if="spinShow"></Spin>

                    <Card>
                        <p slot="title">{{subTitle}}</p>
                        <p slot="extra">{{title}}</p>

                        <video-player ref="videoPlayer" class="video" :options="playerOptions"></video-player>

                        <div class="button-box">
                            <Icon class="button" @click="play" type="play" v-if="!isPlaying"></Icon>

                            <Icon class="button" @click="pause" type="pause" v-if="isPlaying"></Icon>

                            <div class="volume-box">
                                <Icon style="margin-right:8px;" class="button" type="volume-high" @click="mute"
                                        v-if="!isMuted"></Icon>

                                <Icon style="margin-right:8px;" class="button" type="volume-mute" @click="cancelMute"
                                        v-if="isMuted"></Icon>

                                <Slider class="volume-slider" :disabled="volumeDisabled" v-model="volume"></Slider>
                            </div>
                        </div>
                    </Card>
                </div>
            </Content>
        </Layout>
    </div>
</template>

<script>
    import PlayerHeader from "./PlayerHeader";
    const STATUS_PLAYING = 1;
    const STATUS_PREPARED = 0;

    export default {
        name:'VideoJs',
        components:{PlayerHeader},
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
                title:'',
                subTitle:'',
                status:STATUS_PREPARED,
                volume:80,
                isMuted:false,
                volumeDisabled:false,
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
        methods:{
            getOne:function(){
                axios.get('/api/live/' + this.liveId).then(res =>{
                    if(res.data.errorCode === 0){
                        this.streamPath = res.data.data.streamPath;
                        this.title = res.data.data.title;
                        this.subTitle = res.data.data.subTitle;

                        this.player.src({
                            type:this.getType(this.streamPath),
                            src:this.streamPath
                        });
                    }else{
                        this.$Message.error(res.data.msg);
                    }
                    this.spinShow = false;
                }).catch(error =>{
                    this.spinShow = false;
                    this.$Message.error(error);
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
            cancelMute:function(){
                this.player.volume(this.volume * 0.01);
                this.isMuted = false;
                this.volumeDisabled = false;
            },
            onVolumeChange:function(volume){
                this.volume = volume
            },
            getType:function(url){
                this.$Notice.info({
                    title:'当前视频地址：',
                    desc:url
                });
                if(url.includes('.mp4')){
                    return 'video/mp4';
                }else if(url.includes('.m3u8')){
                    return 'application/x-mpegURL';
                }
            }
        }
    }
</script>

<style scoped>
    .player-container {
        display: flex;
        justify-content: center;
    }

    .button-box {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
    }

    .button {
        cursor: pointer;
        font-size: 32px;
    }

    .volume-box {
        display: flex;
        flex: 1 0 auto;
        justify-content: flex-end;
    }

    .volume-slider {
        width: 30%;
    }

    .video {
        display: flex;
        justify-content: center;
    }
</style>