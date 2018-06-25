<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>口袋48成员直播</title>

    <link rel="stylesheet" href="{{asset('css/app.css')}}">
</head>
<body>
<div id="app">
    <div style="padding: 8px 16px;">
        <router-view></router-view>
    </div>
</div>
<script src="{{asset('js/app.js')}}"></script>
<script>
    const _hmt = _hmt || [];
    (function(){
        const hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?f19ad6836f36d4b6a26617a5b924a50f";
        const s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
</body>
</html>
