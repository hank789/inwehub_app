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
            /* height: 343.5px; */
            position: relative;
            padding: 51px 75px 54px;
            background: #5B71A9;
        }
        .component-card .bgimg {
            position: absolute;
            top: 0;
            right: 0;
        }
        .component-card .card-wrapper {
            width: 1029px;
            margin: 0 auto;
            padding: 75px 90px 90px;
            border-radius:60px;
            background: #ffffff;
            position: relative;
            z-index: 1;
        }
        .component-card .user {
            border-bottom: 1.5px solid #E9E9E9;
        }
        .component-card .card-wrapper .userimg {
            width:232.5px;
            height:240px;
            margin: 0 auto;
        }
        .component-card .card-wrapper .userimg img {
            width: 100%;
            height: 100%;
        }
        .component-card .card-wrapper .username {
            font-size:54px;
            font-family:PingFangSC-Medium;
            color:rgba(68,68,68,1);
            line-height:75px;
            text-align: center;
            margin: 18px 0 19.5px;
        }
        .component-card .card-wrapper .stars {
            text-align: center;
            padding-bottom: 51px;
        }
        .component-card .card-wrapper .stars span {
            color: #FCC816;
            line-height: 45px;
            font-size: 33px;
            text-align: center;
        }
        .component-card .card-wrapper .content {
            padding: 45px 0 0;
            border-bottom: 1.5px solid #E9E9E9;
        }
        .component-card .card-wrapper span {
            font-size:42px;
            font-family:PingFangSC-Regular;
            color:rgba(68,68,68,1);
            line-height:78px;
        }
        .component-card .card-wrapper .conimg {
            width:909px;
            height:489px;
            margin: 78px auto;
        }
        .component-card .card-wrapper .conimg img {
            width: 100%;
            height: 100%;
        }
        .component-card .card-wrapper .product-wrapper {
            margin-top: 45px;
        }
        .component-card .card-wrapper .product-wrapper .product {
            display: flex;
        }
        .component-card .card-wrapper .product-wrapper .product .logo {
            width:132px;
            height:132px;
            background:#ffffff;
            border-radius:12px;
            border:1.5px solid #DCDCDC;
            overflow: hidden;
        }
        .component-card .card-wrapper .product-wrapper .product .logo img {
            width: 100%;
            height: 100%;
        }
        .component-card .card-wrapper .product-wrapper .product .info {
            margin-left: 15px;
        }
        .component-card .card-wrapper .product-wrapper .product .info .name {
            font-size:48px;
            font-family:PingFangSC-Medium;
            font-weight:500;
            color:#444444;
            line-height:67.5px;
        }
        .component-card .card-wrapper .stars-wrapper {
            display: flex;
            margin-top: 15px;
        }
        .component-card .card-wrapper .stars-wrapper .spot {
            width:6px;
            height:6px;
            display: inline-block;
            background: #B4B4B6;
            border-radius: 50%;
            margin: 0px 15px 0;
            position: relative;
            top: -7.5px;
        }
        .component-card .card-wrapper .stars-wrapper span {
            font-size:33px;
            font-family:PingFangSC-Regular;
            font-weight:400;
            color:rgba(180,180,182,1);
            line-height:45px;
        }
        .component-card .card-wrapper .stars-wrapper span:nth-of-type(1) {
            color: #FCC816;
        }

        .component-card .text-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 45px;
        }
        .component-card .text-wrapper .finger {
            width: 45px;
            height: 45px;
            margin-right: 15px;
        }
        .component-card .text-wrapper .finger img {
            width: 100%;
            height: 100%;
        }
        .component-card .text-wrapper .text {
            color: #FFFFFF;
            font-size: 36px;
            line-height: 49.5px;
        }
        .component-card .title-wrapper {
            position: relative;
        }
        .component-card .title-wrapper .logo {
            width:201px;
            height:27px;
            padding: 0 18px;
            margin: 60px auto 0;
            position: relative;
            z-index: 9;
            background: #5B71A9;
        }
        .component-card .title-wrapper .logo img {
            width: 100%;
            height: 100%;
            opacity:0.3;
        }
        .component-card .title-wrapper .line {
            width: 300px;
            height: 3px;
            margin: 12px auto 0;
            position: absolute;
            left: 50%;
            opacity:0.3;
            transform: translateX(-50%);
            background: #ffffff;
        }
        .starImg {
            width: 208.5px;
            height: 31.5px;
            position: relative;
        }
        .starImg .star-star {
            position: absolute;
            top: 0;
            left: 0;
        }
        .starImg .star-star img {
            width: 208.5px;
        }
        .starImg .star-S {
            overflow: hidden;
        }
        .starImg .star-S img {
            /* width: 100%; */
        }
        .stars-wrapper .star-text  {
            margin-top: -6px;
            margin-left: 18px;
        }
        .card-wrapper .user .stars-wrapper {
            justify-content: center;
            padding-bottom: 51px;
        }

    </style>
</head>

<body>
<div class="component-card">
    <div class="bgimg">
        <img src="https://cdn.inwehub.com/weapp_dianping/commentDetail@3x.png" alt="">
    </div>
    <div class="card-wrapper">
        <div class="user">
            <div class="userimg">
                <img src="{{ $qrcode }}" alt="">
            </div>
            <div class="username">{{ $review->owner->name }}</div>
            <div class="stars-wrapper">
                <div class="starImg">
                    <div class="star-S star-star" style="width: {{ 100*$review->rate_star/5 }}%;"><img src="https://cdn.inwhub.com/weapp_dianping/starS@3x.png" alt=""></div>
                    <div class="star-K star-star"><img src="https://cdn.inwehub.com/weapp_dianping/starK@3x.png" alt=""></div>
                </div>
                <div class="star-text">
                    <span>{{ $review->rate_star }}分</span>
                </div>
            </div>
        </div>
        <div class="content">
            <span>{{ $review->title }}</span>
        </div>
        <div class="product-wrapper">
            <div class="product">
                <div class="logo">
                    <img src="{{ $product['logo'] }}" alt="">
                </div>
                <div class="info">
                    <div class="name">{{ $product['name'] }}</div>
                    <div class="stars-wrapper">
                        <div class="starImg">
                            <div class="star-S star-star" style="width: {{ 100*$product['review_average_rate']/5 }}%;"><img src="https://cdn.inwehub.com/weapp_dianping/starS@3x.png" alt=""></div>
                            <div class="star-K star-star"><img src="https://cdn.inwehub.com/weapp_dianping/starK@3x.png" alt=""></div>
                        </div>
                        <div class="star-text">
                            <span>{{ $product['review_average_rate'] }}分</span><i class="spot"></i><span>{{ $product['review_count'] }}条评论</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
</body>

</html>