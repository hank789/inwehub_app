<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title></title>
    <link href="https://cdn.inwehub.com/weapp_dianping/css/common.css" rel="stylesheet">
    <link href="https://cdn.inwehub.com/weapp_dianping/css/iconfont.css" rel="stylesheet">
    <link href="https://cdn.inwehub.com/weapp_dianping/css/special.min.css" rel="stylesheet">
</head>
<body>
<div class="container-special container-special-share">
    <div class="component-earth-bottom"><img src="{{$category->icon}}"><img class="radian" src="https://cdn.inwehub.com/weapp_dianping/images/hudu2@3x.png"></div>
    <div class="component-card-main">
        <div class="title">{{$category->name}}</div>
        <div class="content">{{$category->summary}}</div>
        <div class="before"><span class="iconfont icon-shangyinhao-"></span></div>
        <div class="last"><span class="iconfont icon-shangyinhao-"></span></div>
        <div class="topRight"><img src="https://cdn.inwehub.com/weapp_dianping/images/zhuanti_main_card_bg@3x.png"></div>
    </div>
    <div class="container-card-product-minis">
        @foreach($tags as $tag)
            <div class="component-card-product-mini">
                <div class="logo"><img src="{{ $tag['logo'] }}"></div>
                <div class="desc">
                    <div class="descFirst">评分{{$tag['review_average_rate']}}</div>
                    <div class="descSecond">热度{{$tag['support_rate']}}</div>
                </div>
            </div>
        @endforeach
        <div class="component-card-product-mini">
            <div class="help"><span class="iconfont icon-wenhao-"></span></div>
        </div>
    </div>
    <div class="component-earth-top"><img class="radian" src="https://cdn.inwehub.com/weapp_dianping/images/hudu@3x.png"></div>
</div>
<div class="component-specialShare-bottom">
    <div class="left"><img src="https://cdn.inwehub.com/weapp_dianping/images/qiyefuwudianping_hei@3x.png"><span>长按识别二维码查看专题详情</span></div>
    <div class="right"><img src="{{ $qrcode }}"></div>
</div>
</body>
</html>