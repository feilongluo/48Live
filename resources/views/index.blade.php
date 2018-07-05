<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>口袋48成员直播</title>

    <link rel="stylesheet" href="{{asset('css/app.css')}}?v={{str_random()}}">
</head>
<body>
<div id="app">
    <div style="padding: 8px 16px;">
        <router-view></router-view>
    </div>
</div>
<script src="{{asset('js/app.js')}}?v={{str_random()}}"></script>
</body>
</html>
