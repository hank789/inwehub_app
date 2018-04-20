<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="UTF-8" />
    <title>贺新春</title>
    <style type="text/css">
        /*
          dr
          详情的需求描述样式
        */
        page{
            background: #fff;
        }
        .project_detail{
            width:84%;
            overflow:hidden;
            margin:auto;
            margin-top:22rem;
            padding:24rem 34rem;
            box-shadow: 0rem 0rem 8rem 4rem #F4F6F8;
            border-radius: 8rem;
            margin-bottom: 33rem;
            position: relative;
        }
        .project_detail_top{
            border-bottom: 1px solid #DCDCDC;
        }
        .project_detail_title{
            font-size: 32rem;
            color: #444444;
            margin-bottom: 22rem;
            font-weight: 500;
            word-wrap:break-word;
            word-break:break-all;
        }
        .project_detail_tags{
            width: 100%;
            overflow: hidden;
            margin-bottom: 10rem;
            /* display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap； */
        }
        .project_detail_tags .tag{
            float: left;
            border: 1px solid #DCDCDC;
            border-radius: 100rem;
            font-size: 26rem;
            color: #808080;
            padding: 7rem 20rem;
            margin-right: 10rem;
            margin-bottom: 20rem;
        }
        .project_detail_bot{
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            margin-top: 24rem;
        }
        .project_detail_address{
            font-size: 26rem;
            color:#444444;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
        }
        .project_detail_address image{
            width:24rem;
            height:29rem;
        }
        .project_detail_salary{
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            font-size: 38rem;
            color:#66DBF9;
            font-weight: 500;
        }
        .project_detail_salary .gray{
            font-size: 26rem;
            color:#B4B4B6;
        }
        .project_end{
            position:absolute;
            width:220rem;
            height:70rem;
            background:#111111;
            font-size:28rem;
            color:#FFFFFF;
            line-height:65rem;
            text-align:center;
            opacity:0.7;
            right:-55rem;
            bottom:20rem;
            transform:rotate(-45deg);
        }
        .project_describe{
            width:84%;
            overflow:hidden;
            margin:auto;
            padding:0rem 34rem;
        }
        .project_describe_title{
            width: 100%;
            font-size: 32rem;
            color: #444444;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            font-weight: 500;
        }
        .project_describe_title .line{
            width: 45rem;
            height: 3rem;
            background: #DCDCDC;
            margin: 0 10rem;
        }
        .project_content{
            margin-top:18rem;
            font-size: 26rem;
            color: #808080;
        }
        .detail_info{
            width:84%;
            overflow:hidden;
            margin:auto;
            padding:0rem 34rem;
            margin-top: 18rem;
            margin-bottom: 200rem;
        }
        .detail_info_time{
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            font-size: 26rem;
            color: #B4B4B6;
            margin-bottom: 38rem;
        }
        .detail_info_time .blue{
            color: #235280;
            margin-left: 10rem;
        }
        .detail_info_private{
            margin-top: 26rem;
            font-size: 26rem;
            color: #444444;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
        }
        .detail_info_private image{
            width: 52rem;
            height:52rem;
            border-radius: 50%;
            margin-right: 10rem;
        }
        .detail_info_private .interval{
            width: 3rem;
            height: 25rem;
            background: #DCDCDC;
            margin: 0 20rem;
        }
    </style>
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<div class="container">
    <!-- dr  -->
    <div>
        <div class='project_detail'>
            <div class='project_detail_top'>
                <div class='project_detail_title ellipsis-2'>
                    {{$demand->title}}
                </div>
                <div class='project_detail_tags'>
                    <div class='tag'>{{$demand->project_begin_time}}开始</div>
                    <div class='tag'>周期{{$demand->project_cycle['text']}}</div>
                    <div class='tag'>{{$demand->industry['text']}}</div>
                </div>
            </div>
            <div class='project_detail_bot'>
                <div class='project_detail_address'>
                    <image src='../../images/details_address@2x.png'></image>
                    {{$demand->address['selProvince']  === '请选择' ? '' : $demand->address['selProvince'] }}{{$demand->address['selCity'] ? $demand->address['selCity'] : ''}}{{$demand->address['selDistrict'] ? $demand->address['selDistrict'] : ''}}
                </div>
                <div class='project_detail_salary'>{{$demand->salary}}~{{$demand->salary_upper}}<div class='gray'>元/{{$demand->salary_type === 0 ? '天':'月'}}</div></div>
            </div>
            @if ($demand.status>=3)
                <div class='project_end'>已结束</div>
            @endif
        </div>
        <!-- 具体描述 -->
        <div class='project_describe'>
            <div class='project_describe_title'>
                <div class='line'></div>
                具体描述
                <div class='line'></div>
            </div>
            <div class='project_content'>
                <text>{{$demand->description}}</text>
            </div>
        </div>
        <!-- 招募信息有效期  -->

        <!--  -->
        <div class='detail_info'>
            <div class='detail_info_time'>
                招募信息有效期
                <div class='blue'>{{$demand->status >=3 ? '已结束' : $demand->expired_at}}</div>
            </div>
            <div class='project_describe_title'>
                <div class='line'></div>
                发布者信息
                <div class='line'></div>
            </div>
            <div class='detail_info_private'>
                <image src='{{$demand->publisher_avatar}}'></image>
                {{ $demand->publisher_name }}
                <div class='interval'></div>
                {{ $demand->publisher_company }}
                <div class='interval'></div>
                {{ $demand->publisher_title ? $demand->publisher_title : ''}}
            </div>
        </div>
    </div>
</div>

</body>
</html>