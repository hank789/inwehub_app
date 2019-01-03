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
    <style>.rich_media_content * {max-width: 100%!important;box-sizing: border-box!important;-webkit-box-sizing: border-box!important;word-wrap: break-word!important;}img{height: auto!important;}
        .radius_avatar img{display:block;width:100%;height:100%;border-radius:50%;-moz-border-radius:50%;-webkit-border-radius:50%;background-color:#eee}
        .rich_media_inner{word-wrap:break-word;-webkit-hyphens:auto;-ms-hyphens:auto;hyphens:auto}
        .rich_media_area_primary{padding:20px 16px 12px;background-color:#fafafa}
        html{-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;line-height:1.6}
        body{-webkit-touch-callout:none;font-family:-apple-system-font,BlinkMacSystemFont,"Helvetica Neue","PingFang SC","Hiragino Sans GB","Microsoft YaHei UI","Microsoft YaHei",Arial,sans-serif;color:#333;background-color:#f2f2f2;letter-spacing:.034em}h1,h2,h3,h4,h5,h6{font-weight:400;font-size:16px}*{margin:0;padding:0}a{color:#576b95;text-decoration:none;-webkit-tap-highlight-color:rgba(0,0,0,0)}.rich_media_title{font-size:22px;line-height:1.4;margin-bottom:14px}@supports(-webkit-overflow-scrolling:touch){.rich_media_title{font-weight:700}}.rich_media_meta_list{margin-bottom:22px;line-height:20px;font-size:0;word-wrap:break-word;word-break:break-all}.rich_media_meta_list em{font-style:normal}.rich_media_meta{display:inline-block;vertical-align:middle;margin:0 10px 10px 0;font-size:15px;-webkit-tap-highlight-color:rgba(0,0,0,0)}
        .rich_media_meta.meta_tag_text{margin-right:0}
        .meta_enterprise_tag img{width:30px;height:30px!important;display:block;position:relative;margin-top:-3px;border:0}
        .rich_media_meta_link{color:#576b95}.rich_media_meta_text{color:rgba(0,0,0,0.3)}
        .rich_media_meta_nickname{position:relative}
        .rich_media_content{overflow:hidden;color:#333;font-size:17px;word-wrap:break-word;-webkit-hyphens:auto;-ms-hyphens:auto;hyphens:auto;text-align:justify;position:relative;z-index:0}
        .rich_media_content *{max-width:100%!important;box-sizing:border-box!important;-webkit-box-sizing:border-box!important;word-wrap:break-word!important}
        .rich_media_content p{clear:both;min-height:1em}.rich_media_content em{font-style:italic}.rich_media_content fieldset{min-width:0}
        .rich_media_content blockquote{margin:0;padding-left:10px;border-left:3px solid #dbdbdb}img{height:auto!important}@media only screen and (device-width:375px) and (device-height:812px) and (-webkit-device-pixel-ratio:3) and (orientation:portrait){.rich_media_area_extra{padding-bottom:34px}}@media only screen and (device-width:375px) and (device-height:812px) and (-webkit-device-pixel-ratio:3) and (orientation:landscape){.rich_media_area_primary{padding:20px 60px 15px 60px}  .rich_media_area_extra{padding:0 60px 21px 60px}}@media screen and (min-width:1024px){.rich_media_area_primary_inner,.rich_media_area_extra_inner{max-width:677px;margin-left:auto;margin-right:auto}.rich_media_area_primary{padding-top:32px}}
        .original_tool_area .radius_avatar img{height:100%!important}
    </style>

    <style id="page/appmsg_new/not_in_mm.css">.not_in_mm .rich_media_meta_list{position:relative;z-index:1}.not_in_mm .rich_media_content{position:relative}.not_in_mm .rich_media_inner{position:relative}.not_in_mm .qr_code_pc p{font-size:14px;line-height:20px}</style>
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
                    </div>
                </div>
                <div class="rich_media_content" id="js_content">
                    {!! $article->body !!}
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>