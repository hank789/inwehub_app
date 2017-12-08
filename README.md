Here is a sample status icon showing the state of the master branch.

[![Build Status](https://travis-ci.com/hank789/intervapp.svg?token=Q3BzvzTb83P2SBUmtLo1&branch=master)](https://travis-ci.com/hank789/intervapp)

##安装
1. cp .env.example .env
2. 创建数据库,修改.env文件
3. 执行`php artisan key:generate`
4. 执行`php artisan migrate`
5. 执行`php artisan db:seed`
6. 执行`php artisan component install intervapp/plus-component-web`

## 线上配置
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

[program:echo-server-worker]
directory=/home/web/www/inwehub
process_name=%(program_name)s_%(process_num)02d
command=/home/web/www/inwehub/node_modules/laravel-echo-server/bin/server.js start
autostart=true
autorestart=true
user=web
numprocs=1
redirect_stderr=true
stdout_logfile=/tmp/echo_server_worker.log
`
## nginx配置
`
server {
    listen 80;
    server_name api.inwehub.com;
    rewrite ^(.*) https://api.inwehub.com$1 permanent;
}
server {
    listen 443;
    server_name api.inwehub.com;
    ssl on;
    root /home/web/www/inwehub_app/public;
    index index.html index.htm;
    ssl_certificate   /etc/nginx/cert/read/214214860610142.pem;
    ssl_certificate_key  /etc/nginx/cert/read/214214860610142.key;
    ssl_session_timeout 5m;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_prefer_server_ciphers on;
    location /socket.io/{
        proxy_pass http://127.0.0.1:6001/socket.io/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $remote_addr;
    }
    location / {
        index  index.php index.html index.htm;
	    try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        root           /home/web/www/inwehub_app/public;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  /home/web/www/inwehub_app/public/$fastcgi_script_name;
        include        fastcgi_params;
    }
}
`

## 启动socket.id
```
node_modules/laravel-echo-server/bin/server.js init
node_modules/laravel-echo-server/bin/server.js client:add APP_ID
node_modules/laravel-echo-server/bin/server.js start
```

## 部署
使用https://laravel.com/docs/5.4/envoy 进行部署
注意事项:
在服务器上创建软链:`ln -s /usr/local/php/bin/php /usr/bin/php`
### 测试环境部署
`envoy run test`
### 正式环境部署
`envoy run pro`