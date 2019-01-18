<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>InweHub每日精选</title>
    <style>
        * {
            margin: 0;
            font-family:"Open Sans","Helvetica Neue",Helvetica,Arial,STHeiti,"Microsoft Yahei","SimSun",sans-serif;
            box-sizing: border-box;
            font-size: 14px;
        }

        img {
            max-width: 100%;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6em;
        }

        /* -------------------------------------
            BODY & CONTAINER
        ------------------------------------- */
        body {
            background-color: #f6f6f6;
        }

        .body-wrap {
            background-color: #f6f6f6;
            width: 100%;
        }

        .container {
            display: block !important;
            max-width: 600px !important;
            margin: 0 auto !important;
            /* makes it centered */
            clear: both !important;
        }

        .content {
            max-width: 600px;
            margin: 0 auto;
            display: block;
            padding: 20px;
        }

        /* -------------------------------------
            HEADER, FOOTER, MAIN
        ------------------------------------- */
        .main {
            background-color: #fff;
            border: 1px solid #e9e9e9;
            border-radius: 3px;
        }

        .content-wrap {
            padding: 20px;
        }

        /* -------------------------------------
            TYPOGRAPHY
        ------------------------------------- */
        h1, h2, h3 {
            font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            color: #000;
            margin: 40px 0 0;
            line-height: 1.2em;
            font-weight: 400;
        }

        h1 {
            font-size: 32px;
            font-weight: 500;
            /* 1.2em * 32px = 38.4px, use px to get airier line-height also in Thunderbird, and Yahoo!, Outlook.com, AOL webmail clients */
            /*line-height: 38px;*/
        }

        h2 {
            font-size: 24px;
            /* 1.2em * 24px = 28.8px, use px to get airier line-height also in Thunderbird, and Yahoo!, Outlook.com, AOL webmail clients */
            /*line-height: 29px;*/
        }

        h3 {
            font-size: 18px;
            /* 1.2em * 18px = 21.6px, use px to get airier line-height also in Thunderbird, and Yahoo!, Outlook.com, AOL webmail clients */
            /*line-height: 22px;*/
        }

        h4 {
            font-size: 14px;
            font-weight: 600;
        }

        p, ul, ol {
            margin-bottom: 10px;
            font-weight: normal;
        }
        p li, ul li, ol li {
            margin-left: 5px;
            list-style-position: inside;
        }

        /* -------------------------------------
            LINKS & BUTTONS
        ------------------------------------- */
        a {
            color: #348eda;
            text-decoration: underline;
        }
    </style>
</head>

<body itemscope itemtype="{{ Setting()->get('website_url') }}">
<table class="body-wrap">
    <tr>
        <td class="container" width="800">
            <div class="content">
                <table class="main" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="content-wrap">
                            <div id="signature">
                                <div>
                                    <div>

                                        <table width="649" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#fff" style="font-family:'Microsoft Yahei','SimSun'; font-size:14px; border:1px solid #ddd; background-color:#fff">
                                            <tbody>
                                            <tr>
                                                <td align="center" valign="middle" height="58" colspan="2" style="border-bottom:1px solid #ddd">
                                                    <table width="640" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#fff" style="padding-left:10px; background-color:#fff">
                                                        <tbody>
                                                        <tr>
                                                            <td align="left" valign="middle" height="58" width="250"><span style="line-height:28px; padding-left:8px; font-size:18px">InweHub每日精选</span></td>
                                                            <td align="right" valign="middle" height="58"><span style="font-size:18px; padding:0 20px 0 0">{{ $date }}</span></td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>

                                            @foreach($items as $item)

                                                <tr>
                                                    @if ($item['img'])
                                                        <td width="232" align="center">
                                                            <div style="width:190px; padding:20px"><a href="" title="" target="_blank"><img width="190" height="190" title="" border="0" style="display:block" src="{{ $item['img'] }}"></a></div>
                                                        </td>
                                                    @endif
                                                    <td align="left">
                                                        <div style="height:145px; overflow:hidden; padding:20px 20px 0 0">
                                                            <h2 style="font-weight:normal; font-size:18px; margin-top:0; line-height:24px"><a href="{{ $item['url'] }}" title="" target="_blank" style="color:#333; text-decoration:none">{{ $item['title'] }}</a></h2>
                                                            <p style="color:#666; font-family:aril; line-height:24px; font-size:14px">{{ $item['desc'] }}
                                                                <strong></strong><span><a href="{{ $item['url'] }}" target="_blank" style="color:#5183c0; text-decoration:none">阅读全文</a></span>
                                                            </p>
                                                        </div>
                                                        <div style="text-align:right; padding-right:20px; padding-bottom:20px"><span style="display:block; color:#999; font-size:12px">
                                                                <span style="padding:0 5px">热度</span><em style="font-style:normal">{{ $item['rate'] }}</em></span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="1" colspan="2" style="padding:0 20px">
                                                        <div style="height:1px; border-bottom:1px solid #dbdbdb"></div>
                                                    </td>
                                                </tr>
                                                <tr></tr>

                                            @endforeach



                                            <tr>
                                                <td align="center" colspan="2">
                                                    <div style="padding:0px 20px 10px"><span style="display:block; width:100%; font-size:14px; color:#666">收邮件不方便？
                              <em style="color:#f04848; font-style:normal">试试它们吧</em> </span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" colspan="2">
                                                    <span><a href="" target="_blank">
                                                            <img border="0" title="" style="display:inline" src="https://cdn.inwehub.com/system/appstore.png"></a>
                                                    </span>
                                                    <span><img border="0" title="" style="display:inline" src="https://cdn.inwehub.com/system/%E6%9C%8D%E5%8A%A1%E5%8F%B7%E4%BA%8C%E7%BB%B4%E7%A0%81@2x.png"></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" colspan="2"><span style="padding:0 83px 0 0; color:#666">App</span>
                                                    <span style="padding:0 40px 0 10px; color:#666">微信</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="right" style="padding:30px 0 30px 30px"><a href="" target="_blank" title="什么值得买"><img border="0" title="" style="display:block" src="http://eimg.smzdm.com/201410/17/54408d638b537.jpg"></a></td>
                                                <td align="left">
                                                    <div style="float:left; border-left:1px solid #b2b2b2; padding-left:10px; margin-top:18px">
                              <span style="display:block; font-size:14px; color:#666">如果您不想收到此封邮件 点击 <a href="" title="" target="_blank" style="color:#666">
                              退订</a> </span>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <p>&nbsp;</p>
                                        <br>
                                        <br>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
</body>
</html>




