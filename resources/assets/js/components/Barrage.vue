<template>
    <canvas ref="canvas"></canvas>
</template>

<script>
    const USERNAME_COLOR = '#999999';

    const TEXT_SIZE = 28;

    export default {
        name:'Barrage',
        data(){
            return {
                context:null,
                width:-1,
                height:-1,
                barrageList:[],
                randomCount:0,
            };
        },
        mounted:function(){
            this.init();
        },
        methods:{
            shoot:function(barrage){
                const top = this.randomTop();
                const left = this.width;
                this.barrageList.push({
                    top:top,
                    left:left,
                    color:this.randomColor(),
                    content:barrage.content,
                    username:barrage.username
                });
            },
            start:function(){
                setInterval(() =>{
                    this.context.clearRect(0, 0, this.width, this.height);
                    this.barrageList.forEach(barrage =>{
                        //弹幕发送者
                        this.context.fillStyle = USERNAME_COLOR;
                        this.context.fillText(barrage.username + '：', barrage.left, barrage.top);
                        //弹幕内容
                        this.context.fillStyle = barrage.color || '#000000';
                        this.context.fillText(barrage.content, barrage.left +
                            this.context.measureText(barrage.username + '：').width,
                            barrage.top);
                        barrage.left -= 0.9;
                    });

                    this.barrageList.forEach((barrage, index) =>{
                        if(barrage.left + this.context.measureText(barrage.username + '：' + barrage.content).width < 0){
                            this.barrageList.splice(index, 1);
                        }
                    });
                }, 8);
            },
            randomColor:function(){
                return '#' + Math.floor(Math.random() * 0xffffff).toString(16);
            },
            init:function(){
                const canvas = this.$refs.canvas;
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;

                this.context = canvas.getContext('2d');
                this.context.fillStyle = '#000000';
                this.context.font = 'bold ' + TEXT_SIZE + 'px Microsoft YaHei';

                const react = canvas.getBoundingClientRect();
                this.width = react.right - react.left;
                this.height = react.bottom - react.top;

                this.start();
            },
            randomTop:function(){
                let top = Math.floor(Math.random() * (this.height - TEXT_SIZE)) + TEXT_SIZE;
                const isSuperimposed = this.barrageList.some(barrage =>{
                    return top < barrage.top + TEXT_SIZE && top > barrage.top - TEXT_SIZE;
                });
                //尽量避免重叠
                if(isSuperimposed && this.randomCount < 4){
                    this.randomCount++;
                    return this.randomTop();
                }
                console.log(this.randomCount);
                this.randomCount = 0;
                return top;
            }
        }
    }
</script>

<style scoped>

</style>