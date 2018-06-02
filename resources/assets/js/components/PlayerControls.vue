<template>
    <div>
        <div class="progress-container" v-if="showProgress">
            <Slider :max="duration" class="progress-slider" v-model="progress"
                    @on-change="progressChange" @on-input="progressInput" :tip-format="timeFormat"></Slider>
            <span class="progress-text">{{timeFormat(currentTime)}} / {{timeFormat(duration)}}</span>
        </div>

        <div class="button-box">
            <Icon class="button" @click="play" type="play" v-if="!isPlaying"></Icon>

            <Icon class="button" @click="pause" type="pause" v-if="isPlaying"></Icon>

            <div class="volume-box">
                <Icon style="margin-right:8px;" class="volume-icon" type="volume-high" @click="mute"
                        v-if="!isMuted"></Icon>

                <Icon style="margin-right:8px;" class="volume-icon" type="volume-mute" @click="cancelMute"
                        v-if="isMuted"></Icon>

                <Slider class="volume-slider" :disabled="volumeDisabled" v-model="volume"></Slider>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name:'PlayerControls',
        data(){
            return {
                progress:0,
                dragging:false,
            }
        },
        props:{
            isPlaying:{
                type:Boolean,
                required:true
            },
            isMuted:{
                type:Boolean,
                required:true
            },
            volumeDisabled:{
                type:Boolean,
                required:true
            },
            volume:{
                type:Number,
                required:true
            },
            showProgress:{
                type:Boolean,
                required:true
            },
            duration:{
                type:Number,
                required:true
            },
            currentTime:{
                type:Number,
                required:true
            },
        },
        watch:{
            currentTime:function(currentTime){
                if(!this.dragging){
                    this.progress = currentTime;
                }
            }
        },
        methods:{
            timeFormat:function(seconds){
                seconds = Math.round(seconds);
                let minutes = Math.floor(seconds / 60);
                let hours = Math.floor(minutes / 60);
                minutes = minutes % 60;
                seconds = seconds % 60;
                hours = hours < 10 ? '0' + hours : hours;
                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                return hours + ':' + minutes + ':' + seconds;
            },
            play:function(){
                this.$emit('play')
            },
            pause:function(){
                this.$emit('pause')
            },
            mute:function(){
                this.$emit('mute')
            },
            cancelMute:function(){
                this.$emit('cancelMute')
            },
            progressChange:function(progress){
                this.$emit('progress', progress);
                this.dragging = false;
            },
            progressInput:function(){
                this.dragging = true;
            }
        }
    }
</script>

<style scoped>
    .button-box {
        display: flex;
        justify-content: space-between;
    }

    .button {
        cursor: pointer;
        font-size: 32px;
    }

    .progress-container {
        display: inline-flex;
        align-items: center;
        width: 100%;
    }

    .progress-text {
        font-size: 11px;
    }

    .progress-slider {
        flex: 1 0 auto;
        margin: 0 8px;
    }

    .volume-box {
        display: flex;
        flex: 1 0 auto;
        justify-content: flex-end;
        align-items: center;
    }

    .volume-icon {
        font-size: 24px;
        cursor: pointer;
    }

    .volume-slider {
        width: 72px;
    }
</style>