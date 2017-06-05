<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="企业管理咨询、SAP、Oracle、咨询顾问、consultant、业务流程、企业管理、解决方案、行业、consulting、信息化、数字企业、inwe、InweHub、InweTech">
    <meta name="author" content="">

    <title>InweHub</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('/web/lib/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    {{--<!-- Custom Fonts -->--}}
    {{--<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">--}}
    {{--<link href="https://fonts.googleapis.com/css?family=Catamaran:100,200,300,400,500,600,700,800,900" rel="stylesheet">--}}
    {{--<link href="https://fonts.googleapis.com/css?family=Muli" rel="stylesheet">--}}

    <!-- Plugin CSS -->
    <link rel="stylesheet" href="{{ asset('/web/lib/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/web/lib/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('/web/lib/device-mockups/device-mockups.min.css') }}">
    <link href="{{ asset('/web/css/swiper.min.css') }}" rel="stylesheet" type="text/css"/>

    <!-- Theme CSS -->
    <link href="{{ asset('/web/css/new-age.min.css') }}" rel="stylesheet">

    <style>

        .demo {
            display: block;
            position: absolute;
            top:11.4%;
            left:5%;
            width: 90.5%;
            height:77%;
            margin: 0 auto;
            text-align: center;
            background-repeat: no-repeat;
            background-position: center;
            background-size: 310px 637px;
            overflow: hidden;
        }
        .swiper-container{
            width:100%;
            height:100%;
        }
        .swiper-wrapper img {
            width: 100%;
            height: 100%;
        }


        @media (min-width: 768px){
            .demo{
                left:5%;
            }
        }

    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="{{ asset('/web/js/html5shiv.js') }}"></script>
    <script src="{{ asset('/web/js/respond.min.js') }}"></script>
    <![endif]-->

</head>

<body id="page-top">

<nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand page-scroll" href="#page-top"><i class="myicon myicon-logo"></i> </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a class="page-scroll" href="#download">即刻下载</a>
                </li>
                <li>
                    <a class="page-scroll" href="#features">丰富功能</a>
                </li>
                <li>
                    <a class="page-scroll" href="#contact">联系我们</a>
                </li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container-fluid -->
</nav>

<header>
    <div class="container">
        <div class="row">
            <div class="col-sm-7">
                <div class="header-content">
                    <div class="header-content-inner">
                        <h1>不负期待 专家同行</h1>
                        <h3>为企业和咨询顾问<br/>
                            提供高品质资深专家辅助服务</h3>
                        <br/>
                        <a href="#download" class="btn btn-outline btn-xl page-scroll">内测申请</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-5">
                <div class="device-container">
                    <div class="device-mockup iphone6_plus portrait white">
                        <div class="device">
                            <div class="screen">
                                <!-- Demo image for screen mockup, you can put an image here, some HTML, an animation, video, or anything else! -->
                                <img src="{{ asset('/web/img/demo-screen-1.jpg') }}" class="img-responsive" alt="">
                            </div>
                            <div class="button">
                                <!-- You can hook the "home button" to some JavaScript events or just remove it -->
                            </div>
                        </div>

                        <div class="demo">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="{{ asset('/web/assets/01.png') }}"></div>
                                    <div class="swiper-slide">
                                        <img src="{{ asset('/web/assets/02.png') }}"></div>
                                    <div class="swiper-slide">
                                        <img src="{{ asset('/web/assets/03.png') }}"></div>
                                    <div class="swiper-slide">
                                        <img src="{{ asset('/web/assets/04.png') }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
</header>

<section id="download" class="download bg-primary text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h2 class="section-heading">各大应用市场陆续上线中...</h2>
                <p></p>
                <div class="badges">
                    <a class="badge-link" data-toggle="modal" data-target="#myModal"><img src="{{ asset('/') }}web/img/google-play-badge.svg" alt=""></a>
                    <a class="badge-link" data-toggle="modal" data-target="#myModal"><img src="{{ asset('/') }}web/img/app-store-badge.svg" alt=""></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="features">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="section-heading">
                    <h2>丰富功能，真诚为您打造</h2>
                    <p class="text-muted">任何地方、任何时间、任何问题，轻松化解，顾问必备</p>
                    <hr>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-md-12">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="myicon myicon-item-1"></i>
                                <h3>汇聚资深顾问</h3>
                                <p class="text-muted">为企业便捷高质的提供服务</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="myicon myicon-item-2"></i>
                                <h3>极速高质响应</h3>
                                <p class="text-muted">为顾问快速准确的解答疑惑</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="myicon myicon-item-3"></i>
                                <h3>高效智能协同</h3>
                                <p class="text-muted">高效协作智能管理高质交付</p>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="myicon myicon-item-4"></i>
                                <h3>把握行业脉搏</h3>
                                <p class="text-muted">打造公平与客观的行业环境</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="myicon myicon-item-5"></i>
                                <h3>信息资源对接</h3>
                                <p class="text-muted">为行业人士带来一站式服务</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="myicon myicon-item-6"></i>
                                <h3>行业价值延伸</h3>
                                <p class="text-muted">延伸行业链条实现价值提升</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="contact bg-primary">
    <div class="container">
        <h2>联系我们</h2>
        <ul class="list-inline list-social">
            <li class="social-twitter">
                <a href="#" data-toggle="popover" data-trigger="hover" data-html="true" data-content='<img src="web/img/InweHub.jpg" width="200">'><i class="fa fa-wechat"></i></a>
            </li>
            <li class="social-facebook">
                <a href="#" data-toggle="popover" data-trigger="hover" data-html="true" data-content='<img src="web/img/guwenxiaoha.jpeg" width="200">'><i class="fa myicon myicon-xiaoha"></i></a>
            </li>
            <li class="social-google-plus">
                <a href="mailto:hi@inwehub.com"><i class="fa fa-envelope"></i></a>
            </li>
        </ul>
    </div>
</section>


<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-body">
                您所选择的应用市场尚未上线，如果您已经得到邀请码，请在页面最下方以任何方式联系我们，我们会告诉您应用目前的下载方式。
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">确定</button>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>© 2017 InweHub.com All Rights Reserved.</p>
        <ul class="list-inline">
            <li>
                <a href="#">Privacy</a>
            </li>
            <li>
                <a href="#">Terms</a>
            </li>
            <li>
                <a href="#">FAQ</a>
            </li>
        </ul>
    </div>
</footer>

<!-- jQuery -->
<script src="{{ asset('/web/lib/jquery/jquery.min.js') }}"></script>

<!-- Bootstrap Core JavaScript -->
<script src="{{ asset('/web/lib/bootstrap/js/bootstrap.min.js') }}"></script>

<!-- Plugin JavaScript -->
<script src="{{ asset('/web/js/jquery.easing.min.js') }}"></script>

<!-- Theme JavaScript -->
<script src="{{ asset('/web/js/new-age.min.js') }}"></script>
<script src="{{ asset('/web/js/swiper.min.js') }}" type="text/javascript"></script>
<script>
    var mySwiper = new Swiper('.swiper-container', {
        autoplay: 3000,
        prevButton: '.swiper-button-prev',
        nextButton: '.swiper-button-next',
        slidesPerView: 1,
        effect: 'fade',
        paginationClickable: true,
        loop: true
    });

    $(function () {
        $('[data-toggle="popover"]').popover()

        $('[data-toggle="model"]').modal({});
    })
</script>

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-97476315-2', 'auto');
    ga('send', 'pageview');
</script>
</body>

</html>
