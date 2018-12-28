<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title></title>
    <style type="text/css">
        body {
            font-family: PingFang-SC-Regular, sans-serif;
            margin: 0;
            line-height: 15.75px
        }
        * {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            outline: 0
        }
        .component-card {
            /* height: 1038px; */
            position: relative;
            padding-top: 60px;
            padding-bottom: 61.5px;
            background: #5B71A9;
            overflow: hidden;
        }
        .component-card .bgimg {
            position: absolute;
            top: 0;
            right: 0;
        }
        .component-card .car-wrapper {
            width: 1029px;
            padding: 73.5px 48px 70px;
            border-radius: 60px;
            margin: 0px auto 0;
            background: #ffffff;
            position: relative;
            z-index: 1;
        }
        .component-card .car-wrapper .product-code {
            width: 235.5px;
            height: 243px;
            margin: 0 auto;
        }
        .component-card .car-wrapper .product-code img {
            width: 100%;
            height: 100%;
        }
        .component-card .car-wrapper .product-name {
            color: #444444;
            font-size:54px;
            line-height: 75px;
            text-align: center;
            margin: 31.5px 0;
            font-family:PingFangSC-Medium;
        }
        .component-card .car-wrapper .content {
            color: #808080;
            font-size: 34.5px;
            line-height: 57px;
        }

        .component-card .text-wrapper {
            width: 100%;
            height: 45px;
            overflow: hidden;
            text-align: center;
            margin-top: 30px;
        }
        .component-card .text-wrapper .text {
            color: #FFFFFF;
            font-size: 36px;
            line-height: 49.5px;
            text-align: center;
        }
        .component-card .text-wrapper .text span {
            padding-left: 60px;
            background: url(https://cdn.inwehub.com/weapp_dianping/finger-p@3x.png) no-repeat;
        }
        .component-card .title-wrapper .logo {
            width: 300px;
            height: 27px;
            margin: 60px auto 0;
            position: relative;
            z-index: 9;
        }
        .component-card .title-wrapper .logo img {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
<div class="component-card">
    <div class="bgimg">
        <img src="https://cdn.inwehub.com/weapp_dianping/productFriends@3x.png" alt="">
    </div>
    <div class="car-wrapper">
        <div class="product-code">
            <img src="{{ $qrcode }}" alt="">
        </div>
        <div class="product-name">{{ $tag->name }}</div>
        <div class="content">
            {!! $tag->summary !!}
        </div>
    </div>
    <div class="text-wrapper">
        <div class="text"><span>长按识别二维码查看详细信息</span></div>
    </div>
    <div class="title-wrapper">
        <div class="logo"><img src="https://cdn.inwehub.com/weapp_dianping/text-logo@3x.png" alt=""></div>
    </div>
</div>
</body>

</html>