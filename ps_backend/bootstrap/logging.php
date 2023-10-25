<?php

$app->configureMonologUsing(function($monolog) use ($app) {
    $monolog->pushHandler(
        (new Monolog\Handler\StreamHandler(
            config('settings.log.storage').'laravel.log',
            $app->make('config')->get('app.log_max_files', 5)
        ))->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true, true))
    );
});