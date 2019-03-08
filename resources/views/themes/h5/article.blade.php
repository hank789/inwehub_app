<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $article->title }}</title>
    <meta name="author" content="樱维社区" />
    <meta name="copyright" content="2018 inwehub.com" />
    <link href="https://cdn.inwehub.com/system/article.css" rel="stylesheet" />
    <style>
        .bottomBack {
            position: fixed;
            bottom: 40px;
            right: 32px;
        }
        .bottomBack a {
            width: 127px;
            height: 88px;
            line-height: 88px;
            text-align: center;
            background: #ffffff;
            color: #29BC9E;
            font-size: 32px;
            font-family:PingFangSC-Medium;
            display: inline-block;
            box-shadow:0px 0px 19px 0px rgba(234,240,244,1);
            border-radius:44px;
        }
    </style>
    @if ($miniprogram_back)
        <script
                type="text/javascript"
                src="https://res.wx.qq.com/open/js/jweixin-1.3.2.js"
        ></script>
    @endif

    <!--[if lt IE 9]>
    <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body id="activity-detail" class="zh_CN sougou_body appmsg_skin_default appmsg_style_default rich_media_empty_extra not_in_mm">
<div id="js_article" class="rich_media">
    <div class="rich_media_inner">
        <div id="page-content" class="rich_media_area_primary">
            <div class="rich_media_area_primary_inner">
                <div id="img-content">
                    <h2 class="rich_media_title" id="activity-name">
                        {{ $article->title }}
                    </h2>
                    <div id="meta_content" class="rich_media_meta_list">
                        <span id="copyright_logo" class="rich_media_meta rich_media_meta_text meta_tag_text">原创：</span>
                        <span class="rich_media_meta rich_media_meta_text">
                                                <span id="js_author_name" class="rich_media_meta_link" data-rewardsn="" data-timestamp="" data-canreward="0">{{ $article->author }}</span>
                                            </span>
                        <span class="rich_media_meta rich_media_meta_nickname" id="profileBt">
                      <a href="javascript:void(0);" id="js_name">
                          {{ $article->withAuthor()->name }}                     </a></span>
                        <em id="publish_time" class="rich_media_meta rich_media_meta_text">{{ $showDate }}</em>
                    </div>
                </div>
                <div class="rich_media_content" id="js_content">
                    {!! $article->body !!}
                </div>
            </div>
        </div>
    </div>
    @if ($miniprogram_back)
        <div class="bottomBack">
            <a href="javascript:void(0);" onclick="backToMiniprogam('{{$miniprogram_back}}')">返回</a>
        </div>
    @endif
</div>
<script>
    function backToMiniprogam(path) {
        wx.miniProgram.redirectTo({
            url: path
        })
    }
</script>
</body>
</html>