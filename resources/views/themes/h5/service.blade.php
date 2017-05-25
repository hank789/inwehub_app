<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <title>服务条款</title>
    <style>
        html, body {
            height: 100%;
        }
        body {
            margin: 0;
            width: 100%;
            display: table;
            /*font-weight: 100;*/
            font-family: 'Lato';
            color: #333;
        }
        .container {
            margin-top: 20px;
            vertical-align: middle;
        }
        .content {
            padding: 70px 0 170px;
            margin: 0 auto;
            width: 1100px;
            position: relative;
        }
        .content p,h3,h4{
            text-indent: 32px;
            font-weight: 400;
            line-height: 32px;
            font-size: 18px;
            border: none;
            margin: 0;
            padding: 0;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="content">
        {!! Setting()->get('register_license','') !!}
    </div>
</div>
</body>
</html>