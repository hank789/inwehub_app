@servers(['web' => 'web@47.92.24.67'])

@task('deploy')
    cd /home/web/www/intervapp
    git pull origin master
@endtask
