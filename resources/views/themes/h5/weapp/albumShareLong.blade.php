<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title></title>
    <style type="text/css">
        @font-face {
            font-family: 'PingFangSC-Regular';
            src: url('https://cdn.inwehub.com/system/PingFangSC-Regular.ttf');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'PingFangSC-Medium';
            src: url('https://cdn.inwehub.com/system/PingFangSC-Medium.ttf');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: PingFang-SC-Regular, sans-serif;
            -webkit-tap-highlight-color: transparent;
            -webkit-font-smoothing: antialiased;
            font-size: 0.426rem;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
            /*background: transparent !important;*/
            background: #fff;
            margin: 0;
            padding: 0; }

        @font-face {font-family: "iconfont";
            src: url('iconfont.eot?t=1551326476171'); /* IE9 */
            src: url('iconfont.eot?t=1551326476171#iefix') format('embedded-opentype'), /* IE6-IE8 */
            url('data:application/x-font-woff2;charset=utf-8;base64,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') format('woff2'),
            url('iconfont.woff?t=1551326476171') format('woff'),
            url('iconfont.ttf?t=1551326476171') format('truetype'), /* chrome, firefox, opera, Safari, Android, iOS 4.2+ */
            url('iconfont.svg?t=1551326476171#iconfont') format('svg'); /* iOS 4.1- */
        }

        .iconfont {
            font-family: "iconfont" !important;
            font-size: 16px;
            font-style: normal;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .icon-check-circle:before {
            content: "\e62c";
        }

        .icon-times--:before {
            content: "\e631";
        }

        .icon-times:before {
            content: "\e635";
        }

        .icon-gou1:before {
            content: "\e690";
        }

        .icon-sousuo:before {
            content: "\e688";
        }

        .icon-xiangji1:before {
            content: "\e69e";
        }

        .icon-zan1:before {
            content: "\e6a1";
        }

        .icon-tianjia:before {
            content: "\e6b1";
        }

        .icon-jinru:before {
            content: "\e6db";
        }

        .icon-cai1:before {
            content: "\e6e2";
        }

        .icon-xingxingkongxin:before {
            content: "\e6ed";
        }

        .icon-shixinxingxing:before {
            content: "\e6ee";
        }

        .icon-xingxingyidian:before {
            content: "\e6f1";
        }

        .icon-xingxingweidian:before {
            content: "\e6f2";
        }

        .icon-xiaozhongdianpinglogo:before {
            content: "\e6dc";
        }

        .icon-zan:before {
            content: "\e6dd";
        }

        .icon-xiazaiapp:before {
            content: "\e6de";
        }

        .icon-cai:before {
            content: "\e6df";
        }

        .icon-fenxiang:before {
            content: "\e6e0";
        }

        .icon-youxiang:before {
            content: "\e6e1";
        }

        .icon-zanyidian:before {
            content: "\e6e3";
        }

        .icon-caiyidian:before {
            content: "\e6e4";
        }

        .icon-xiangxiajiantou:before {
            content: "\e6e5";
        }

        .icon-xiangshangjiantou:before {
            content: "\e6e6";
        }

        .icon-guanzhushixin:before {
            content: "\e6eb";
        }

        .icon-jiaobiao-:before {
            content: "\e6ec";
        }

        .icon-dianzan-:before {
            content: "\e6f0";
        }

        .icon-kongzhuangtai-:before {
            content: "\e6f3";
        }

        .icon-diancai-:before {
            content: "\e6f4";
        }

        .icon-xiedianping-:before {
            content: "\e6f6";
        }

        .icon-jiantou-:before {
            content: "\e6f7";
        }

        .icon-bangdan-:before {
            content: "\e6f8";
        }

        .icon-zhuanyeban-:before {
            content: "\e6f9";
        }

        .icon-zhuanti:before {
            content: "\e6fa";
        }

        .icon-zhuanfa-1:before {
            content: "\e6fb";
        }

        .icon-xiayinhao-:before {
            content: "\e6f5";
        }

        .icon-chahao-:before {
            content: "\e6fc";
        }

        .icon-diandiandian-:before {
            content: "\e6fd";
        }

        .icon-sanjiaoxing-:before {
            content: "\e6fe";
        }

        .icon-lianjie-:before {
            content: "\e6ff";
        }

        .icon-shangyinhao-:before {
            content: "\e700";
        }

        .icon-wenhao-:before {
            content: "\e701";
        }



        .container-special {
            position: relative;
            padding-top: 483px;
            margin-bottom: 99px;
            padding-bottom: 36px;
            background: #1C2C42; }
        .container-special .component-card-product {
            margin-top: 81px; }
        .container-special.container-special-share {
            padding-top: 90px;
            padding-bottom: 57px; }
        .container-special.container-special-share .component-earth-bottom {
            height: 639px; }

        .icon {
            width: 1em;
            height: 1em;
            vertical-align: -0.15em;
            fill: currentColor;
            overflow: hidden; }

        .component-card-main {
            margin: 0 48px 156px;
            font-family: PingFangSC-Medium;
            letter-spacing: 3px;
            position: relative;
            background: -webkit-gradient(linear, left top, left bottom, from(#29bcb8), color-stop(52%, #205463), to(#1c2c42));
            background: linear-gradient(180deg, #29bcb8 0%, #205463 52%, #1c2c42 100%);
            border-radius: 12px;
            padding: 90px 75px; }
        .component-card-main:before {
            content: '';
            position: absolute;
            background-image: url(https://cdn.inwehub.com/weapp_dianping/images/zhuanti_main_card_bg@3x.png);
            background-size: contain;
            background: no-repeat;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 1; }
        .component-card-main .title {
            color: #fff;
            font-size: 60px;
            font-weight: 500;
            line-height: 66px;
            letter-spacing: 3px;
            display: -webkit-box;
            overflow: hidden;
            white-space: normal !important;
            text-overflow: ellipsis;
            word-wrap: break-word;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical; }
        .component-card-main .content {
            font-family:PingFangSC-Regular;
            letter-spacing:1px;
            margin-top: 105px;
            font-size: 42px;
            color: #fff;
            opacity: .58;
            line-height: 69px;
            display: -webkit-box;
            overflow: hidden;
            white-space: normal !important;
            text-overflow: ellipsis;
            word-wrap: break-word;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical; }
        .component-card-main .before {
            color: #29BCB8;
            font-size: 45px;
            position: absolute;
            top: 195px;
            left: 75px; }
        .component-card-main .last {
            color: #29BCB8;
            font-size: 45px;
            position: absolute;
            bottom: 15px;
            right: 75px; }
        .component-card-main .topRight {
            position: absolute;
            right: 0;
            top: 0; }
        .component-card-main .topRight img {
            width: 435px;
            height: 333.42px; }

        .component-card-product {
            margin: 0 48px; }
        .component-card-product .first {
            padding: 45px 45px 30px;
            background: -webkit-linear-gradient(315deg, #234c60 0%, #22354e 100%);
            background: linear-gradient(315deg, #234c60 0%, #22354e 100%);
            border-radius: 12px 12px 0 0; }
        .component-card-product .first .productHead {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex; }
        .component-card-product .first .productHead .logo {
            -ms-flex-negative: 0;
            flex-shrink: 0;
            width: 132px;
            height: 132px;
            border-radius: 12px;
            background: white;
            border: 3px solid #ececee;
            overflow: hidden; }
        .component-card-product .first .productHead .logo img {
            width: 100%;
            height: 100%;
            -o-object-fit: contain;
            object-fit: contain; }
        .component-card-product .first .productHead .right {
            padding-left: 24px; }
        .component-card-product .first .productHead .right .title {
            font-size: 48px;
            font-family: PingFangSC-Medium;
            font-weight: 500;
            color: white;
            line-height: 75px;
            display: -webkit-box;
            overflow: hidden;
            white-space: normal !important;
            text-overflow: ellipsis;
            word-wrap: break-word;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical; }
        .component-card-product .first .productHead .right .stars {
            font-size: 33px;
            line-height: 66px;
            font-family: PingFangSC-Regular;
            font-weight: 400;
            color: #29bcb8; }
        .component-card-product .first .productHead .right .stars .iconfont {
            font-size: 33px;
            line-height: 66px;
            font-family: PingFangSC-Regular;
            font-weight: 400;
            color: #29bcb8;
            margin-right: 6px; }
        .component-card-product .first .productHead .right .stars .span {
            margin-left: 9px; }
        .component-card-product .first .content {
            font-size: 42px;
            font-family: PingFangSC-Regular;
            font-weight: 400;
            color: white;
            line-height: 69px;
            letter-spacing: 3px;
            margin-top: 24px;
            opacity: 0.58;
            display: -webkit-box;
            overflow: hidden;
            white-space: normal !important;
            text-overflow: ellipsis;
            word-wrap: break-word;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; }
        .component-card-product .second {
            background: #22354E;
            height: 147px;
            line-height: 147px;
            padding: 0 45px;
            position: relative;
            z-index: 1;
            overflow: hidden; }
        .component-card-product .second .left {
            font-size: 39px;
            font-family: HelveticaNeue-Medium;
            font-weight: 500;
            color: white; }
        .component-card-product .second .left img {
            position: relative;
            top: 6px;
            width: 39px;
            height: 45px;
            margin-right: 9px; }
        .component-card-product .second .right {
            position: absolute;
            right: 45px;
            top: 33px; }
        .component-card-product .second .right .button {
            border-radius: 43.5px;
            padding: 0 48px;
            height: 87px;
            line-height: 87px;
            text-align: center;
            background: #29bcb8;
            color: #fff; }
        .component-card-product .second .right .button .iconfont {
            position: relative;
            top: 3px;
            font-size: 48px;
            margin-right: 9px; }
        .component-card-product .three {
            padding-left: 36px;
            line-height: 102px;
            height: 102px;
            color: #fff;
            font-size: 42px; }
        .component-card-product .three .iconfont {
            font-size: 69px;
            color: #29BCB8;
            position: relative;
            top: -15px;
            margin-right: 9px; }
        .component-card-product .three .span {
            opacity: .58;
            margin-left: 15px; }

        .component-earth-top {
            position: absolute;
            bottom: -69px;
            left: 0;
            height: 126px;
            width: 100%; }
        .component-earth-top img {
            position: absolute;
            top: 56px;
            left: 0;
            width: 100%;
            height: 69px; }

        .component-earth-bottom {
            display: inline-block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 756px;
            z-index: 0; }
        .component-earth-bottom img {
            width: 100%;
            height: 100%;
            -o-object-fit: cover;
            object-fit: cover; }
        .component-earth-bottom img.radian {
            z-index: 1;
            position: absolute;
            left: 0;
            bottom: -1px;
            width: 100%;
            height: 69px; }

        .component-title-big {
            font-size: 60px;
            font-family: PingFangSC-Medium;
            font-weight: 500;
            color: #3c3e44;
            padding: 0 48px;
            margin-top: 9px; }

        .container-card-product-minis {
            padding-left: 30px; }
        .container-card-product-minis .component-card-product-mini {
            margin-bottom: 30px; }

        .component-card-product-mini {
            display: inline-block;
            vertical-align: top;
            width: 234px;
            height: 375px;
            margin: 0 15px;
            border-radius: 12px;
            overflow: hidden;
            background:url(https://cdn.inwehub.com/weapp_dianping/images/minicardbg@3x.png) no-repeat;
            background-size: cover; }
        .component-card-product-mini .logo {
            width: 234px;
            height: 234px;
            background-color:#fff;
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center center;
        }
        .component-card-product-mini .logo img {
            -o-object-fit: contain;
            object-fit: contain;
            width: 100%;
            height: 100%; }
        .component-card-product-mini .desc {
            font-size: 33px;
            font-family: PingFangSC-Regular;
            color: #29bcb8;
            line-height: 48px;
            padding: 24px 30px; }

        .component-card-product-mini .help{
            width:100%;
            height:100%;
            background-image: url(https://cdn.inwehub.com/weapp_dianping/images/wenhao@3x.png);
            background-position: center center;
            background-repeat: no-repeat;
        }

        .component-specialShare-bottom {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
            padding: 30px 48px 15px; }
        .component-specialShare-bottom .left {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-flow: column;
            flex-flow: column;
            padding-top: 27px; }
        .component-specialShare-bottom .left .imgWrapper img {
            width: 222px; }
        .component-specialShare-bottom .left .span {
            font-size: 36px;
            font-family: PingFangSC-Regular;
            font-weight: 400;
            color: #444444;
            line-height: 60px;
            -webkit-transform: scale(0.84);
            transform: scale(0.84);
            position: relative;
            left: -39px;
            opacity: 0.5971; }
        .component-specialShare-bottom .right {
            -webkit-box-pack: end;
            -ms-flex-pack: end;
            justify-content: flex-end;
            position: relative;
            top: -33px;
            left: -21px; }
        .component-specialShare-bottom .right img {
            width: 219px;
            height: 219px; }



    </style>
</head>
<body>
<div class="container-special container-special-share">
    <div class="component-earth-bottom"><img src="{{$category->icon}}"><img class="radian" src="https://cdn.inwehub.com/weapp_dianping/images/hudu2@3x.png"></div>
    <div class="component-card-main">
        <div class="title">{{$category->name}}</div>
        <div class="content">{{$category->summary}}</div>
        <div class="before"><img src="https://cdn.inwehub.com/weapp_dianping/images/shangyinhao@3x.png"></div>
        <div class="last"><img src="https://cdn.inwehub.com/weapp_dianping/images/xiayinhao@3x.png"></div>
        <div class="topRight"><img src="https://cdn.inwehub.com/weapp_dianping/images/zhuanti_main_card_bg@3x.png"></div>
    </div>
    <div class="container-card-product-minis">
        @foreach($tags as $tag)<div class="component-card-product-mini"><div class="logo" style="background-image: url({{ $tag['logo'] }})"></div><div class="desc"><div class="descFirst">评分{{$tag['review_average_rate']}}</div><div class="descSecond">热度{{$tag['support_rate']}}</div></div></div>@endforeach<div class="component-card-product-mini"><div class="help"></div></div>
    </div>
    <div class="component-earth-top"><img class="radian" src="https://cdn.inwehub.com/weapp_dianping/images/hudu@3x.png"></div>
</div>
<div class="component-specialShare-bottom">
    <div class="left"><div class="imgWrapper"><img src="https://cdn.inwehub.com/weapp_dianping/images/qiyefuwudianping_hei@3x.png"></div><div class="span">长按识别小程序码查看专题详情</div></div>
    <div class="right"><img src="{{ $qrcode }}"></div>
</div>
</body>
</html>