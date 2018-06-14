<template>
    <canvas ref="canvas"></canvas>
</template>

<script>
    const TEXT_SIZE = 28;

    export default {
        name:'Barrage',
        data(){
            return {
                context:null,
                width:-1,
                height:-1,
                barrageList:[],
            };
        },
        created:function(){

        },
        mounted:function(){
            const canvas = this.$refs.canvas;
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;

            this.context = canvas.getContext('2d');
            this.context.fillStyle = '#000000';
            this.context.font ='bold ' + TEXT_SIZE + 'px Microsoft YaHei';

            const react = canvas.getBoundingClientRect();
            this.width = react.right - react.left;
            this.height = react.bottom - react.top;

            this.start();
        },
        methods:{
            shoot:function(content){
                const top = Math.floor(Math.random() * (this.height - TEXT_SIZE)) + TEXT_SIZE;
                let left = this.width;
                this.barrageList.push({
                    top:top,
                    left:left,
                    color:this.randomColor(),
                    content:content
                });
            },
            start:function(){
                setInterval(() =>{
                    this.context.clearRect(0, 0, this.width, this.height);
                    this.barrageList.forEach((barrage, index) =>{
                        this.context.fillStyle = barrage.color || '#000000';
                        this.context.fillText(barrage.content, barrage.left, barrage.top);
                        barrage.left -= 0.9;
                        if(barrage.left + this.context.measureText(barrage.content).width < 0){
                            this.barrageList.splice(index, 1);
                        }
                    });
                }, 8);
            },
            randomColor:function(){
                return '#' + Math.floor(Math.random() * 0xffffff).toString(16);
            }
        }
    }
</script>

<style scoped>

</style>