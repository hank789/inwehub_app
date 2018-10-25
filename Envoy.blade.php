@servers(['web-test1' => 'web@47.92.24.67','web-pro' => 'web@47.92.64.32','web-read' => 'web@47.92.104.35'])

@task('test',['on' => ['web-test1']])
    cd /home/web/www/intervapp
    git pull origin master
    composer update --no-scripts
    php artisan optimize
    php artisan config:cache
    php artisan route:cache
    php artisan migrate
    php artisan queue:restart
@endtask

@task('test-m',['on' => ['web-test1']])
cd /home/web/www/intervapp
git pull origin master
@endtask

@task('test-dev-m',['on' => ['web-test1']])
cd /home/web/www/intervapp
git pull origin dev
php artisan opcache:clear
php artisan opcache:optimize
@endtask

@task('test-dev-web',['on' => ['web-test1']])
cd /home/web/www
bash update_web_dev.sh
@endtask

@task('pro-web',['on' => ['web-pro']])
cd /home/web/www
bash update_web.sh
@endtask

@task('test-dev',['on' => ['web-test1']])
cd /home/web/www/intervapp
git pull origin dev
php artisan config:cache
php artisan route:cache
php artisan migrate
php artisan opcache:clear
php artisan opcache:optimize
php artisan queue:restart
@endtask

@task('pro',['on' => ['web-pro']])
cd /home/web/www/inwehub_app
git pull origin master
php artisan config:cache
php artisan route:cache
php artisan migrate
php artisan opcache:clear
php artisan opcache:optimize
php artisan queue:restart
@endtask

@task('pro-m',['on' => ['web-pro','web-read']])
cd /home/web/www/inwehub_app
git pull origin master
php artisan opcache:clear
php artisan opcache:optimize
@endtask

