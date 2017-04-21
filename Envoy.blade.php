@servers(['web-test1' => 'web@47.92.24.67'])

@task('deploy-test',['on' => ['web-test1']])
    cd /home/web/www/intervapp
    git pull origin master
    composer update --no-scripts
    php artisan optimize
    php artisan migrate
    php artisan queue:restart
@endtask


@after
@slack('https://hooks.slack.com/services/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', '#general')
@endafter