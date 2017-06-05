<!DOCTYPE html>
<head>
    <title>index</title>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
    <link href="{{ asset('/web/css/main.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        #main-artboard-container { width: 1440px; display: block }
        #main-artboard-image { width: 1440px; height: 1504px; background: url("{{ asset('/web/images/index.png') }}") no-repeat; }
        #tablet-artboard-container { width: 1100px }
        #tablet-artboard-image { width: 1100px; height: 1504px; background: url("{{ asset('/web/images/index_tablet.png') }}") no-repeat; }
        #mobile-artboard-container { width: 750px }
        #mobile-artboard-image { width: 750px; height: 2214px; background: url("{{ asset('/web/images/index_mobile.png') }}") no-repeat; }
        @media (-webkit-min-device-pixel-ratio: 2), (min--moz-device-pixel-ratio: 2), (-o-min-device-pixel-ratio: 2/1), (min-resolution: 192dpi), (min-resolution: 2dppx) {
            #main-artboard-image { background-image: url("{{ asset('/web/images/index@2x.png') }}"); background-size: 1440px 1504px; }
            #tablet-artboard-image { background-image: url("{{ asset('/web/images/index_tablet@2x.png') }}"); background-size: 1100px 1504px; }
            #mobile-artboard-image { background-image: url("{{ asset('/web/images/index_mobile@2x.png') }}"); background-size: 750px 2214px; }
        }
        @media screen and (max-width: 1439px) {
            #main-artboard-container, #mobile-artboard-container { display: none }
            #tablet-artboard-container { display: block }
        }
        @media screen and (max-width: 1099px) {
            #main-artboard-container, #tablet-artboard-container { display: none }
            #mobile-artboard-container { display: block }
        }
    </style>
    <script src="{{ asset('/web/js/main.js') }}" type="text/javascript"></script>
</head>
<body>
<main>
    <div id="main-artboard-container" class="artboard-container">
        <div id="main-artboard-image" class="artboard-image"></div>
        <a href="http://www.inwehub.com" class="hotspot" style="left:1064px; top:1180px; width:199px; height:20px" target="_blank"></a>
        <a href="http://www.inwehub.com" class="hotspot" style="left:742px; top:605px; width:247px; height:80px" target="_blank"></a>
        <a href="http://www.inwehub.com" class="hotspot" style="left:742px; top:728px; width:250px; height:84px" target="_blank"></a>
        <a href="http://www.inwehub.com" class="hotspot" style="left:865px; top:408px; width:300px; height:75px" target="_blank"></a>
        <a href="http://www.inwehub.com" class="hotspot" style="left:174px; top:18px; width:187px; height:38px" target="_blank"></a>
    </div>
    <div id="tablet-artboard-container" class="artboard-container">
        <div id="tablet-artboard-image" class="artboard-image"></div>
        <a href="http://www.inwehub.com" class="hotspot" style="left:831px; top:1177px; width:199px; height:20px" target="_blank"></a>
        <a href="http://www.inwehub.com" class="hotspot" style="left:510px; top:606px; width:247px; height:80px" target="_blank"></a>
        <a href="http://www.inwehub.com" class="hotspot" style="left:511px; top:729px; width:248px; height:83px" target="_blank"></a>
        <a href="http://www.inwehub.com" class="hotspot" style="left:633px; top:408px; width:300px; height:75px" target="_blank"></a>
        <a href="http://www.inwehub.com" class="hotspot" style="left:85px; top:19px; width:187px; height:38px" target="_blank"></a>
    </div>
    <div id="mobile-artboard-container" class="artboard-container">
        <div id="mobile-artboard-image" class="artboard-image"></div>
    </div>
</main>
</body>
</html>
