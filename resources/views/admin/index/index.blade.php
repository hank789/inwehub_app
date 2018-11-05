@extends('admin.public.layout')

@section('title') 首页 @endsection

@permission('admin.index.all')
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            网站总览(缓存时间半个小时)
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.user.index') }}">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-users" aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">注册用户数</span>
                            <span class="info-box-number">{{ $totalUserNum }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.question.index') }}">
                        <span class="info-box-icon bg-red"><i class="fa fa-question-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">问题总数</span>
                            <span class="info-box-number">{{ $totalQuestionNum }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-edit"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">评价总数</span>
                        <span class="info-box-number">{{ $totalFeedbackNum }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.answer.index') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-feed"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">回答总数</span>
                            <span class="info-box-number">{{ $totalAnswerNum }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.operate.article.index') }}">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-anchor" aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">动态数</span>
                            <span class="info-box-number">{{ $submissionTextCount }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.operate.article.index') }}">
                        <span class="info-box-icon bg-red"><i class="fa fa-archive"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">文章数</span>
                            <span class="info-box-number">{{ $submissionLinkCount }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.task.index') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-tasks"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">总任务数</span>
                            <span class="info-box-number">{{ $totalTasks }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.task.index') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-tasks"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">未清任务</span>
                            <span class="info-box-number">{{ $totalUndoTasks }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->

        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.task.index') }}">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-anchor" aria-hidden="true"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">未清任务人数</span>
                            <span class="info-box-number">{{ $totalUndoTaskUsers }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.activity.coupon') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">签到红包总金额</span>
                            <span class="info-box-number">{{ $signTotalCouponMoney }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.activity.coupon') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">新手红包总金额</span>
                            <span class="info-box-number">{{ $newbieTotalCouponMoney }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.user.index') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">用户账户总余额</span>
                            <span class="info-box-number">{{ $totalBalance }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.finance.settlement.index') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">用户账户待结算金额</span>
                            <span class="info-box-number">{{ $totalSettlement }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.finance.withdraw.index') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">提现总金额</span>
                            <span class="info-box-number">{{ $withDrawMoney }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.finance.withdraw.index') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">付费围观总数</span>
                            <span class="info-box-number">{{ $totalPayForView }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <a href="{{ route('admin.finance.withdraw.index') }}">
                        <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">累计手续费收入</span>
                            <span class="info-box-number">{{ $totalFeeMoney }}</span>
                        </div><!-- /.info-box-content -->
                    </a>
                </div><!-- /.info-box -->
            </div><!-- /.col -->
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-text">简历完成率</span>
                        <span class="info-box-number">{{ $userInfoCompletePercent }}%</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-text">未处理文章数</span>
                        <span class="info-box-number">{{ $articlesTodo }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-text">未处理招标数</span>
                        <span class="info-box-number">{{ $bidTodo }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-text">未处理招聘数</span>
                        <span class="info-box-number">{{ $recruitTodo }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div><!-- /.col -->
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">一周用户数据报告</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p class="text-center">
                                    <strong>一周用户趋势数据</strong>
                                </p>
                                <div class="chart">
                                    <canvas id="user_chart" height="400"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">问答数据报告</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p class="text-center">
                                    <strong>问题、回答、评价统计</strong>
                                </p>
                                <div class="chart">
                                    <canvas id="question_chart" height="400"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">热门标签Top100</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="taglist-inline multi">
                                    @foreach($hotTags as $hotTag)
                                        <li class="tagPopup"><a class="tag" target="_blank" data-toggle="popover"  href="{{ route('ask.tag.index',['id'=>$hotTag['tag_id']]) }}">{{ $hotTag['tag_name'].'('.$hotTag['total_num'].')' }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">热门搜索Top100</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="taglist-inline multi">
                                    @foreach($searchCount as $word=>$count)
                                        <li class="tagPopup"><a class="tag" target="_blank" data-toggle="popover"  href="#">{{ $word.'('.$count.')' }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">用户等级统计</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>等级</th>
                                            <th>人数</th>
                                        </tr>
                                        @foreach($userLevels as $userLevel)
                                            <tr>
                                                <td>{{ $userLevel->user_level }}</td>
                                                <td>{{ $userLevel->total }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">用户余额统计</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>排名</th>
                                            <th>用户ID</th>
                                            <th>用户姓名</th>
                                            <th>手机</th>
                                            <th>余额</th>
                                        </tr>
                                        @foreach($userMoney as $key=>$user)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $user->user_id }}</td>
                                                <td>{{ $user->user->name }}</td>
                                                <td>{{ $user->user->mobile }}</td>
                                                <td>{{ $user->total_money }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">用户提现金额统计</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>排名</th>
                                            <th>用户ID</th>
                                            <th>用户姓名</th>
                                            <th>手机</th>
                                            <th>余额</th>
                                        </tr>
                                        @foreach($userWithdrawMoneyList as $key=>$userWithdrawMoney)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $userWithdrawMoney->user_id }}</td>
                                                <td>{{ $userWithdrawMoney->user->name }}</td>
                                                <td>{{ $userWithdrawMoney->user->mobile }}</td>
                                                <td>{{ $userWithdrawMoney->total_amount }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">邀请用户统计</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>用户ID</th>
                                            <th>用户姓名</th>
                                            <th>手机</th>
                                            <th>邀请用户数</th>
                                        </tr>
                                        @foreach($rcUsers as $user)
                                            @if ($user->rc_uid)
                                                <tr>
                                                    <td>{{ $user->rc_uid }}</td>
                                                    <td>{{ $user->getInviter()->name }}</td>
                                                    <td>{{ $user->getInviter()->mobile }}</td>
                                                    <td>{{ $user->total }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">贡献值排行前50</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>排名</th>
                                            <th>用户ID</th>
                                            <th>用户姓名</th>
                                            <th>手机</th>
                                            <th>贡献值</th>
                                        </tr>
                                        @foreach($coinUsers as $key=>$coinUser)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $coinUser->user_id }}</td>
                                                <td>{{ $coinUser->user->name }}</td>
                                                <td>{{ $coinUser->user->mobile }}</td>
                                                <td>{{ $coinUser->coins }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">成长值排行前50</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>排名</th>
                                            <th>用户ID</th>
                                            <th>用户姓名</th>
                                            <th>手机</th>
                                            <th>成长值</th>
                                        </tr>
                                        @foreach($creditUsers as $key=>$creditUser)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $creditUser->user_id }}</td>
                                                <td>{{ $creditUser->user->name }}</td>
                                                <td>{{ $creditUser->user->mobile }}</td>
                                                <td>{{ $creditUser->credits }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">系统信息</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-striped table-bordered">
                                    <tbody>
                                    <tr>
                                        <td>软件版本：{{ config('inwehub.version') }} Release {{ config('inwehub.release') }} [<a href="http://www.inwehub.com/download.html">查看最新版本</a>]</td>
                                    </tr>
                                    <tr>
                                        <td>服务器域名： {{ $systemInfo['hostName'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>PHP版本： {{ $systemInfo['phpVersion'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>服务器端信息：{{ $systemInfo['runOS'] }}/{{ $systemInfo['serverInfo'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>最大上传限制：{{ $systemInfo['maxUploadSize'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>最大执行时间：{{ $systemInfo['maxExecutionTime'] }} seconds</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">版权申明</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-striped table-bordered">
                                    <tbody>
                                    <tr>
                                        <td>版权所有：www.inwehub.com</td>
                                    </tr>
                                    <tr>
                                        <td>用户协议：<a href="http://www.inwehub.com/license.html" target="_blank">查看用户协议</a></td>
                                    </tr>
                                    <tr>
                                        <td>测试链接：<a href="inwehubtest://web.ywhub.com?__direct_page=http%3a%2f%2fm.weibo.cn%2fu%2f3196963860" target="_blank">启动app</a> </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endsection

@section('script')
<script type="text/javascript" src="{{ asset('/static/js/chartjs/Chart.min.js') }}"></script>
<script type="text/javascript">
    $(function(){
        set_active_menu('root_menu',"{{ route('admin.index.index') }}");
        var userChart = new Chart($("#user_chart"), {
            type: 'line',
            data: {
                labels: [{!! implode(",",$userChart['labels']) !!}],
                datasets: [
                    {
                    label: '注册数',
                        backgroundColor: "rgba(51,102,102,0.8)",
                        borderColor: "rgba(51,102,102,0.8)",
                        fill: false,
                        data: [{{ implode(",",$userChart['registerUsers']) }}]
                    },
                    {
                        fill: false,
                        backgroundColor: "rgba(153,51,51,0.8)",
                        borderColor: "rgba(153,51,51,0.8)",

                        label: '邀请数',
                        data: [{{ implode(",",$userChart['recommendUsers']) }}]
                    },
                    {
                        fill: false,
                        backgroundColor: "rgba(0,166,90,0.9)",
                        borderColor: "rgba(0,166,90,0.9)",

                        label: '行家认证',
                        data: [{{ implode(",",$userChart['authUsers']) }}]
                    },
                    {
                        fill: false,
                        backgroundColor: "rgba(255,100,97,1)",
                        borderColor: "rgba(255,100,97,1)",

                        label: '每日签到',
                        data: [{{ implode(",",$userChart['signUsers']) }}]
                    },
                    {
                        fill: false,
                        backgroundColor: "rgba(238,153,34,1)",
                        borderColor: "rgba(238,153,34,1)",

                        label: '每日登陆',
                        data: [{{ implode(",",$userChart['loginUsers']) }}]
                    },
                ]
            },
            options: {
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
        var questionChart = new Chart($("#question_chart"), {
            type: 'bar',
            data: {
                labels: [{!! implode(",",$questionChart['labels']) !!}],
                datasets: [
                    {
                        label: '提问',
                        backgroundColor: "rgba(204,102,51,0.9)",
                        data: [{{ implode(",",$questionChart['questionRange']) }}]
                    },
                    {
                        label: '回答',
                        backgroundColor: "rgba(51,102,153,0.9)",
                        data: [{{ implode(",",$questionChart['answerRange']) }}]
                    },
                    {
                        label: '评价',
                        backgroundColor: "rgba(0,166,90,0.9)",
                        data: [{{ implode(",",$questionChart['feedbackRange']) }}]
                    },
                    {
                        label: '动态',
                        backgroundColor: "rgba(255,100,97,1)",
                        data: [{{ implode(",",$questionChart['submissionTextRange']) }}]
                    },
                    {
                        label: '文章',
                        backgroundColor: "rgba(238,153,34,1)",
                        data: [{{ implode(",",$questionChart['submissionLinkRange']) }}]
                    },
                    {
                        label: '分享',
                        backgroundColor: "rgba(153,51,51,1)",
                        data: [{{ implode(",",$questionChart['shareRange']) }}]
                    }
                ]
            },
            options: {
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        });


    });
</script>
@endsection
@endpermission