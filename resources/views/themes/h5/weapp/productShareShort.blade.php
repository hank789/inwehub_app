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
            height: 334.5px;
        }
        .component-card .bgimg {
            position: absolute;
            top: 0;
            right: 0;
        }
        .component-card .fl {
            position: absolute;
            left: 75px;
        }
        .component-card .fl .product-name {
            color: #FFFFFF;
            font-size:54px;
            line-height: 75px;
            margin: 0px 0 18px;
            font-family:PingFangSC-Medium;
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
            display: inline-block;
            position: relative;
            top: -10px;
        }
        .component-card .fl .title-wrapper .logo {
            width:165px;
            height:27px;
        }
        .component-card .fl .title-wrapper .logo img {
            width:165px;
            height:27px;
            opacity: 0.3;
        }
        .component-card .fr {
            position: absolute;
            right: 75px;
        }
        .component-card .fr .code-wrapper {
            width:238.5px;
            height:238.5px;
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
            margin: -107px 0 0 -104.25px;
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
            <div class="text"><span>长按识别二维码查看详细信息</span></div>
        </div>
        <div class="title-wrapper">
            <span class="line"></span>
            <span class="logo"><img src="https://cdn.inwehub.com/weapp_dianping/text_q@3x.png" alt=""></span>
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