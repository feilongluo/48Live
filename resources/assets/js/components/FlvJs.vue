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

                        <video class="video" id="liveVideo"></video>

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
        name:'FlvJs',
        components:{PlayerHeader},
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
                    this.flvPlayer.on(this.$flvjs.Events.ERROR, error =>{
                        console.log(error);
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
</style>