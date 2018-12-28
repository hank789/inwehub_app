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
            position: relative;
            padding: 51px 75px 54px;
            background: #5B71A9;
            overflow: hidden;
        }
        .component-card .bgimg {
            position: absolute;
            top: 0;
            right: 0;
        }
        .component-card .fl {
            float: left;
        }
        .component-card .fl .product-name {
            color: #FFFFFF;
            font-size:54px;
            line-height: 75px;
            margin: 0px 0 18px;
            font-family:PingFangSC-Medium;
        }
        .component-card .text-wrapper {
            overflow: hidden;
        }
        .component-card .text-wrapper .finger {
            width: 45px;
            height: 45px;
            margin-right: 15px;
            float: left;
        }
        .component-card .text-wrapper .finger img {
            width: 100%;
            height: 100%;
            opacity: 0.3;
        }
        .component-card .text-wrapper .text {
            color: #FFFFFF;
            font-size: 36px;
            line-height: 49.5px;
            float: left;
        }
        .component-card .fl .title-wrapper {
            margin-top: 64.5px;
            overflow: hidden;
        }
        .component-card .fl .title-wrapper .line {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #FFFFFF;
            opacity: 0.3;
            margin-top: 10.5px;
            margin-right: 15px;
            float: left;
        }
        .component-card .fl .title-wrapper .logo {
            width:165px;
            height:27px;
            float: left;
        }
        .component-card .fl .title-wrapper .logo img {
            width: 100%;
            height: 100%;
            opacity: 0.3;
        }
        .component-card .fr {
            float: right;
        }
        .component-card .fr .code-wrapper {
            width:238.5px;
            height:238.5px;
            background:rgba(255,255,255,1);
            border-radius:142.5px;
            overflow: hidden;
            position: relative;
        }
        .component-card .fr .code-wrapper .product-code {
            width:208.5px;
            height:214.5px;
            margin: 0 auto;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
        }
        .component-card .fr .code-wrapper .product-code img {
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body>
<div class="component-card">
    <div class="bgimg">
        <img src="https://cdn.inwehub.com/weapp_dianping/productDetail@3x.png" alt="">
    </div>
    <div class="fl">
        <div class="product-name">{{ $tag->name }}</div>
        <div class="text-wrapper">
            <div class="finger">
                <img src="https://cdn.inwehub.com/weapp_dianping/finger@3x.png" alt="">
            </div>
            <div class="text">长按识别二维码查看详细信息</div>
        </div>
        <div class="title-wrapper">
            <div class="line"></div>
            <div class="logo"><img src="https://cdn.inwehub.com/weapp_dianping/text_q@3x.png" alt=""></div>
        </div>
    </div>
    <div class="fr">
        <div class="code-wrapper">
            <div class="product-code">
                <img src="{{ $qrcode }}" alt="">
            </div>
        </div>
    </div>
</div>
</body>

</html>