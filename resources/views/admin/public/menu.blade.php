<ul class="sidebar-menu" id="root_menu">
    <li class="header">管理菜单</li>
    <li><a href="{{ route('admin.index.index') }}"><i class="fa fa-dashboard"></i> <span>运营监控</span> </a></li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-users"></i> <span>用户</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_user">
            <li><a href="{{ route('admin.user.index') }}"><i class="fa fa-circle-o"></i> 用户管理</a></li>
            <li><a href="{{ route('admin.authentication.index') }}"><i class="fa fa-circle-o"></i> 专家管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-users"></i> <span>找顾问助手</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_weapp_user">
            <li><a href="{{ route('admin.weapp.user.index') }}"><i class="fa fa-circle-o"></i> 用户管理</a></li>
            <li><a href="{{ route('admin.weapp.demand.index') }}"><i class="fa fa-circle-o"></i> 需求管理</a></li>

        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-building"></i> <span>企业</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_company">
            <li><a href="{{ route('admin.company.index') }}"><i class="fa fa-circle-o"></i> 认证管理</a></li>
            <li><a href="{{ route('admin.company.service.index') }}"><i class="fa fa-circle-o"></i> 企业服务</a></li>
            <li><a href="{{ route('admin.company.data.index') }}"><i class="fa fa-circle-o"></i> 企业信息</a></li>
            <li><a href="{{ route('admin.company.data.people') }}"><i class="fa fa-circle-o"></i> 企业相关人员</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-suitcase"></i> <span>项目</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_project">
            <li><a href="{{ route('admin.project.index') }}"><i class="fa fa-circle-o"></i> 需求管理</a></li>
        </ul>
    </li>


    <li class="treeview">
        <a href="#">
            <i class="fa fa-user-secret"></i> <span>权限</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_role">
            <li><a href="{{ route('admin.permission.index') }}"><i class="fa fa-circle-o"></i> 权限管理</a></li>
            <li><a href="{{ route('admin.role.index') }}"><i class="fa fa-circle-o"></i> 角色管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-comments-o"></i> <span>问答</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_content">
            <li><a href="{{ route('admin.question.index') }}"><i class="fa fa-circle-o"></i> 问题管理</a></li>
            <li><a href="{{ route('admin.answer.index') }}"><i class="fa fa-circle-o"></i> 回答管理</a></li>
            <li><a href="{{ route('admin.comment.index') }}"><i class="fa fa-circle-o"></i> 评论管理</a></li>
            <li><a href="{{ route('admin.tag.index') }}"><i class="fa fa-circle-o"></i> 标签管理</a></li>
            <li><a href="{{ route('admin.category.index') }}"><i class="fa fa-circle-o"></i> 分类管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-clock-o"></i> <span>任务</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_task">
            <li><a href="{{ route('admin.task.index') }}"><i class="fa fa-circle-o"></i> 任务管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-cutlery"></i> <span>运营</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="operations">
            <li><a href="{{ route('admin.operate.recommendRead.index') }}"><i class="fa fa-circle-o"></i> 精选推荐</a></li>
            <li><a href="{{ route('admin.operate.article.index') }}"><i class="fa fa-circle-o"></i> 发现分享</a></li>
            <li><a href="{{ route('admin.operate.pushNotice.index') }}"><i class="fa fa-circle-o"></i> 推送管理</a></li>
            <li><a href="{{ route('admin.operate.recommendExpert.refresh') }}"><i class="fa fa-circle-o"></i> 更新首页专家</a></li>
            <li><a href="{{ route('admin.operate.rgcode.index') }}"><i class="fa fa-circle-o"></i> 邀请码管理</a></li>
            <li><a href="{{ route('admin.appVersion.index') }}"><i class="fa fa-circle-o"></i> APP版本管理</a></li>
            <li><a href="{{ route('admin.operate.bootGuide') }}"><i class="fa fa-circle-o"></i> 启动页管理</a></li>
            <li><a href="{{ route('admin.notice.index') }}"><i class="fa fa-circle-o"></i> 公告管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-coffee"></i> <span>即时通讯</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="im">
            <li><a href="{{ route('admin.im.customer.index') }}"><i class="fa fa-circle-o"></i> 客服小哈</a></li>
            <li><a href="{{ route('admin.im.customer.group') }}"><i class="fa fa-circle-o"></i> 私信群发</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-gift"></i> <span>活动</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="activity">
            <li><a href="{{ route('admin.activity.config') }}"><i class="fa fa-circle-o"></i> 活动配置</a></li>
            <li><a href="{{ route('admin.activity.coupon') }}"><i class="fa fa-circle-o"></i> 红包</a></li>
            <li><a href="{{ route('admin.article.index') }}"><i class="fa fa-circle-o"></i> 活动报名</a></li>
            <li><a href="{{ route('admin.comment.index') }}"><i class="fa fa-circle-o"></i> 评论管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-rmb"></i> <span>财务</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="finance">
            <li><a href="{{ route('admin.finance.setting.index') }}"><i class="fa fa-circle-o"></i> 参数设置</a></li>
            <li><a href="{{ route('admin.finance.settlement.index') }}"><i class="fa fa-circle-o"></i> 结算管理</a></li>
            <li><a href="{{ route('admin.finance.withdraw.index') }}"><i class="fa fa-circle-o"></i> 提现管理</a></li>
            <li><a href="{{ route('admin.finance.order.index') }}"><i class="fa fa-circle-o"></i> 支付订单</a></li>

        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-database"></i> <span>积分</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="credit">
            <li><a href="{{ route('admin.setting.credits') }}"><i class="fa fa-circle-o"></i> 积分设置</a></li>
            <li><a href="{{ route('admin.credit.index') }}"><i class="fa fa-circle-o"></i> 积分管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-wrench"></i> <span>站点配置</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="global">
            <li><a href="{{ route('admin.setting.register') }}"><i class="fa fa-circle-o"></i> 注册设置</a></li>
            <li><a href="{{ route('admin.setting.inviterules') }}"><i class="fa fa-circle-o"></i> 邀请注册设置</a></li>
            <li><a href="{{ route('admin.setting.answer') }}"><i class="fa fa-circle-o"></i> 问答设置</a></li>
            <li><a href="{{ route('admin.setting.aboutus') }}"><i class="fa fa-circle-o"></i> 关于我们</a></li>
            <li><a href="{{ route('admin.setting.help') }}"><i class="fa fa-circle-o"></i> 常见问题</a></li>
            <li><a href="{{ route('admin.setting.qahelp') }}"><i class="fa fa-circle-o"></i> 提问帮助页</a></li>

            {{--<li><a href="{{ route('admin.setting.website') }}"><i class="fa fa-circle-o"></i> 站点设置</a></li>
            <li><a href="{{ route('admin.setting.email') }}"><i class="fa fa-circle-o"></i> 邮箱设置</a></li>
            <li><a href="{{ route('admin.setting.time') }}"><i class="fa fa-circle-o"></i> 时间设置</a></li>
            <li><a href="{{ route('admin.setting.irrigation') }}"><i class="fa fa-circle-o"></i> 防灌水设置</a></li>
            <li><a href="{{ route('admin.setting.seo') }}"><i class="fa fa-circle-o"></i> SEO设置</a></li>
            <li><a href="{{ route('admin.setting.variables') }}"><i class="fa fa-circle-o"></i> 变量设置</a></li>--}}
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-file"></i> <span>日志</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="logger">
            <li><a href="{{ route('admin.logger.login') }}"><i class="fa fa-circle-o"></i> 登陆日志</a></li>
            <li><a href="{{ route('admin.logger.doing') }}"><i class="fa fa-circle-o"></i> 行为日志</a></li>
        </ul>
    </li>


    <li class="header">常用菜单</li>
    <li><a href="{{ route('website.index') }}" target="_blank"><i class="fa fa-circle-o text-success"></i> <span>网站首页</span></a></li>
    <li><a href="{{ route('auth.feed.index') }}" target="_blank"><i class="fa fa-circle-o text-info"></i> <span>动态流</span></a></li>
    <li><a href="{{ config('app.readhub_url').'/backend' }}" target="_blank"><i class="fa fa-circle-o text-success"></i> <span>阅读站</span></a></li>
    <li><a href="{{ route('admin.tool.clearCache') }}"><i class="fa fa-circle-o text-info"></i> <span>清空缓存</span></a></li>
</ul>
