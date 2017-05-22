<ul class="sidebar-menu" id="root_menu">
    <li class="header">管理菜单</li>
    <li><a href="{{ route('admin.index.index') }}"><i class="fa fa-dashboard"></i> <span>首页</span> </a></li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-wrench"></i> <span>全局</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="global">
            <li><a href="{{ route('admin.setting.website') }}"><i class="fa fa-circle-o"></i> 站点设置</a></li>
            <li><a href="{{ route('admin.setting.email') }}"><i class="fa fa-circle-o"></i> 邮箱设置</a></li>
            <li><a href="{{ route('admin.setting.register') }}"><i class="fa fa-circle-o"></i> 注册设置</a></li>
            <li><a href="{{ route('admin.setting.time') }}"><i class="fa fa-circle-o"></i> 时间设置</a></li>
            <li><a href="{{ route('admin.setting.irrigation') }}"><i class="fa fa-circle-o"></i> 防灌水设置</a></li>
            <li><a href="{{ route('admin.setting.credits') }}"><i class="fa fa-circle-o"></i> 积分设置</a></li>
            <li><a href="{{ route('admin.setting.seo') }}"><i class="fa fa-circle-o"></i> SEO设置</a></li>
            {{--<li><a href="{{ route('admin.setting.variables') }}"><i class="fa fa-circle-o"></i> 变量设置</a></li>--}}
        </ul>
    </li>

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
            <i class="fa fa-users"></i> <span>权限管理</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_user">
            <li><a href="{{ route('admin.permission.index') }}"><i class="fa fa-circle-o"></i> 权限管理</a></li>
            <li><a href="{{ route('admin.role.index') }}"><i class="fa fa-circle-o"></i> 角色管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-comments-o"></i> <span>内容</span>
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
            <i class="fa fa-comments-o"></i> <span>Inwehub.com</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="manage_inwehub">
            <li><a href="{{ route('admin.inwehub.topic.index') }}"><i class="fa fa-circle-o"></i> 话题管理</a></li>
            <li><a href="{{ route('admin.inwehub.news.index') }}"><i class="fa fa-circle-o"></i> 新闻管理</a></li>
            <li><a href="{{ route('admin.inwehub.feeds.index') }}"><i class="fa fa-circle-o"></i> 数据源管理</a></li>
            <li><a href="{{ route('admin.inwehub.wechat.author.index') }}"><i class="fa fa-circle-o"></i> 微信公众号管理</a></li>
            <li><a href="{{ route('admin.inwehub.wechat.article.index') }}"><i class="fa fa-circle-o"></i> 微信公众号文章管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-cutlery"></i> <span>运营</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="operations">
            <li><a href="{{ route('admin.operate.home_data') }}"><i class="fa fa-circle-o"></i> 首页运营数据</a></li>
            <li><a href="{{ route('admin.operate.recommendQa.index') }}"><i class="fa fa-circle-o"></i> 首页问答推荐</a></li>
            <li><a href="{{ route('admin.operate.rgcode.index') }}"><i class="fa fa-circle-o"></i> 邀请码管理</a></li>
            <li><a href="{{ route('admin.appVersion.index') }}"><i class="fa fa-circle-o"></i> APP版本管理</a></li>
            <li><a href="{{ route('admin.notice.index') }}"><i class="fa fa-circle-o"></i> 公告管理</a></li>
        </ul>
    </li>

    <li class="treeview">
        <a href="#">
            <i class="fa fa-database"></i> <span>财务</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu" id="finance">
            <li><a href="{{ route('admin.finance.setting.index') }}"><i class="fa fa-circle-o"></i> 参数设置</a></li>
            <li><a href="{{ route('admin.finance.settlement.index') }}"><i class="fa fa-circle-o"></i> 结算管理</a></li>
            <li><a href="{{ route('admin.finance.withdraw.index') }}"><i class="fa fa-circle-o"></i> 提现管理</a></li>
            <li><a href="{{ route('admin.credit.index') }}"><i class="fa fa-circle-o"></i> 积分管理</a></li>
        </ul>
    </li>


    <li class="header">常用菜单</li>
    <li><a href="{{ route('website.index') }}" target="_blank"><i class="fa fa-circle-o text-success"></i> <span>网站首页</span></a></li>
    <li><a href="{{ route('admin.tool.clearCache') }}"><i class="fa fa-circle-o text-info"></i> <span>清空缓存</span></a></li>
</ul>
