Here is a sample status icon showing the state of the master branch.

[![Build Status](https://travis-ci.com/hank789/intervapp.svg?token=Q3BzvzTb83P2SBUmtLo1&branch=master)](https://travis-ci.com/hank789/intervapp)

##安装
1. cp .env.example .env
2. 创建数据库,修改.env文件
3. 执行`php artisan key:generate`
4. 执行`php artisan migrate`
5. 执行`php artisan db:seed`
6. 查看:https://github.com/tlaverdure/laravel-echo-server,安装socket

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
        add_header Access-Control-Allow-Origin *;
        add_header Access-Control-Allow-Methods 'GET, POST, OPTIONS';
        add_header Access-Control-Allow-Headers 'DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization';
    
        if ($request_method = 'OPTIONS') {
            return 204;
        }
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

## nginx 负载均衡配置

upstream inwehub_api{
    server 127.0.0.1 weight=7;
    server 172.26.195.231 weight=3;
}

server {
	listen 80;
	server_name api.inwehub.com;
	#rewrite ^(.*) https://api.inwehub.com/$1 permanent;
	root /home/web/www/inwehub_app/public;
	index index.html;
	location / {
        add_header Access-Control-Allow-Origin *;
        add_header Access-Control-Allow-Methods 'GET, POST, OPTIONS';
        add_header Access-Control-Allow-Headers 'DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization';

        if ($request_method = 'OPTIONS') {
                return 204;
        }
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
server {
    listen 443;
    server_name api.inwehub.com;
    ssl on;
    root /home/web/www/inwehub_app/public;
    index index.html index.htm;
    ssl_certificate   /etc/nginx/cert/api_inwehub/214701682170142.pem;
    ssl_certificate_key  /etc/nginx/cert/api_inwehub/214701682170142.key;
    ssl_session_timeout 5m;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_prefer_server_ciphers on;
    location /api/weapp/ {
        index  index.php index.html index.htm;
        try_files $uri $uri/ /index.php?$query_string;
    }
    location /api/manage/product/getInfo {
        index  index.php index.html index.htm;
        try_files $uri $uri/ /index.php?$query_string;
    }
    location /api/ {
    	proxy_pass http://inwehub_api;
        proxy_set_header Host $host;
        proxy_redirect http:// $scheme://;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header REMOTE-HOST $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
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

## 启动socket.id
```
node_modules/laravel-echo-server/bin/server.js init
node_modules/laravel-echo-server/bin/server.js client:add APP_ID
node_modules/laravel-echo-server/bin/server.js start
```

## 全文搜索:安装 ElasticSearch

因为我们要使用 ik 插件，在安装这个插件的时候，如果自己想办法安装这个插件会浪费你很多精力。

所以我们直接使用项目： https://github.com/medcl/elasticsearch-rtf

当前的版本是 `Elasticsearch 5.1.1`，ik 插件也是直接自带了。

安装好 ElasticSearch，跑起来服务，测试服务安装是否正确：

```bash
$ curl http://localhost:9200

{
  "name" : "Rkx3vzo",
  "cluster_name" : "elasticsearch",
  "cluster_uuid" : "Ww9KIfqSRA-9qnmj1TcnHQ",
  "version" : {
    "number" : "5.1.1",
    "build_hash" : "5395e21",
    "build_date" : "2016-12-06T12:36:15.409Z",
    "build_snapshot" : false,
    "lucene_version" : "6.3.0"
  },
  "tagline" : "You Know, for Search"
}
```
如果正确的打印以上信息，证明 ElasticSearch 已经安装好了。

接着你需要查看一下 ik 插件是否安装（请在你的 ElasticSearch 文件夹中执行）：

```bash
$ ./bin/elasticsearch-plugin list
analysis-ik
```
如果出现 `analysis-ik`，证明 ik 已经安装。

在es目录运行
```
./bin/elasticsearch
```
如果守护进程运行
```
ES_JAVA_OPTS="-Xms2024m -Xmx2024m"  ./bin/elasticsearch  -d
```

## 初始化和 ElasticSearch 相关的配置，创建 index

```bash
$ php artisan es:init
//创建对应model的索引
$ php artisan scout:import 'App\Models\Submission'
$ php artisan scout:import 'App\Models\Tag'

```
搜索：
```
Feed::search("独立建树")->get()
```

##抓包工具
http://anyproxy.io/cn/

```
anyproxy --intercept
```

##shadowsocks客户端：https://blog.csdn.net/wh211212/article/details/79165415

```bash
pip install shadowsocks
mkdir /etc/shadowsocks
vi /etc/shadowsocks/shadowsocks.json
```

添加配置信息：前提是需要有ss服务器的地址、端口等信息

``
{
    "server":"x.x.x.x",  # Shadowsocks服务器地址
    "server_port":1035,  # Shadowsocks服务器端口
    "local_address": "127.0.0.1", # 本地IP
    "local_port":1080,  # 本地端口
    "password":"password", # Shadowsocks连接密码
    "timeout":300,  # 等待超时时间
    "method":"aes-256-cfb",  # 加密方式
    "fast_open": false,  # true或false。开启fast_open以降低延迟，但要求Linux内核在3.7+
    "workers": 1  #工作线程数 
}
``
启动Shadowsocks服务:
```
sslocal -c /etc/shadowsocks/shadowsocks.json -d start
```
测试是否启动：
```bash
curl --socks5 127.0.0.1:1080 http://httpbin.org/ip
```
Shadowsock客户端服务已正常运行，则结果如下：
```
{
  "origin": "x.x.x.x"       #你的Shadowsock服务器IP
}
```

##代理服务,需要安装python3环境
```
git clone git@github.com:hank789/haipproxy.git
pip install -r requirements.txt
nohup python3 crawler_booter.py --usage crawler common ajax > /tmp/proxy_ip_crawler.log &
nohup python3 crawler_booter.py --usage validator init jianyu360 sogou > /tmp/proxy_ip_validator.log &
nohup python3 scheduler_booter.py --usage crawler common ajax > /tmp/proxy_ip_scheduler_crawler.log &
nohup python3 scheduler_booter.py --usage validator jianyu360 sogou > /tmp/proxy_ip_scheduler_validator.log &
```

## 部署
使用https://laravel.com/docs/5.4/envoy 进行部署
注意事项:
在服务器上创建软链:`ln -s /usr/local/php/bin/php /usr/bin/php`
### 测试环境部署
`envoy run test`
### 正式环境部署
`envoy run pro`