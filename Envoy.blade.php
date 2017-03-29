@servers(['web' => 'web@47.92.24.67'])

@task('deploy')
    cd /home/web/www/intervapp
    git pull origin master
@endtask


@after
@slack('https://hooks.slack.com/services/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', '#general')
@endafter