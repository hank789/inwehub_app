<footer class="main-footer text-center">
    <strong>Copyright &copy; 2010-{{ date('Y') }} <a href="http://www.inwehub.com">inwehub.com</a>.</strong> All rights reserved.
</footer>
<div class="post-button">{{ config('app.env') == 'production' ? '正式环境':'测试环境' }}</div>
<style>
    .post-button {
        position: fixed;
        top: 10px;
        right: 50px;
        width:300px;
        line-height:38px;
        text-align:center;
        background:red;
        color:#fff;
        -webkit-border-radius:100px;
        border-radisu:100px;
        left:50%;
        margin-left:-150px;
        z-index:1040;
    }
</style>