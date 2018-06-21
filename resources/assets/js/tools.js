Date.prototype.format = function(fmt){
    const o = {
        "M+":this.getMonth() + 1,                 //月份
        "d+":this.getDate(),                    //日
        "h+":this.getHours(),                   //小时
        "m+":this.getMinutes(),                 //分
        "s+":this.getSeconds(),                 //秒
        "q+":Math.floor((this.getMonth() + 3) / 3), //季度
        "S":this.getMilliseconds()             //毫秒
    };
    if(/(y+)/.test(fmt)){
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    for(const k in o){
        if(new RegExp("(" + k + ")").test(fmt)){
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) :
                (("00" + o[k]).substr(("" + o[k]).length)));
        }
    }
    return fmt;
};

class Tools {
    static pictureUrls(picturesStr){
        const pictures = picturesStr.split(',');
        return pictures.map(picture =>{
            if(picture.indexOf('http') >= 0){
                return picture;
            }else{
                return 'https://source.48.cn' + picture;
            }
        });
    }
}

export default Tools;