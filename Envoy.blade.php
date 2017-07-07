@servers(['web-test1' => 'web@47.92.24.67'],'web-pro' => 'web@47.92.64.32')

@task('test-f',['on' => ['web-test1']])
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

@task('pro',['on' => ['web-pro']])
cd /home/web/www/intervapp
git pull origin master
composer update --no-scripts
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan migrate
php artisan queue:restart
@endtask

