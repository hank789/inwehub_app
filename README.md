Here is a sample status icon showing the state of the master branch.

[![Build Status](https://travis-ci.com/hank789/intervapp.svg?token=Q3BzvzTb83P2SBUmtLo1&branch=master)](https://travis-ci.com/hank789/intervapp)

##安装
1. cp .env.example .env
2. 创建数据库,修改.env文件
3. 执行`php artisan key:generate`
4. 执行`php artisan migrate`
5. 执行`php artisan db:seed`
6. 执行`php artisan component install intervapp/plus-component-web`

##线上配置
1. 安装进程管理工具:supervisor
`
[program:queue-default-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/web/www/inwehub/artisan queue:work --queue=default --sleep=3 --tries=1
autostart=true
autorestart=true
user=web
numprocs=2
redirect_stderr=true
stdout_logfile=/tmp/queue_worker.log
[program:queue-withdraw-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/web/www/inwehub/artisan queue:work --queue=withdraw --sleep=3 --tries=1
autostart=true
autorestart=true
user=web
numprocs=1
redirect_stderr=true
stdout_logfile=/tmp/queue_worker.log
`


## 部署
使用https://laravel.com/docs/5.4/envoy 进行部署
注意事项:
在服务器上创建软链:`ln -s /usr/local/php/bin/php /usr/bin/php`
### 测试环境部署
`envoy run test`
### 正式环境部署
`envoy run pro`