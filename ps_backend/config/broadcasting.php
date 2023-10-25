<?php
/**
 * This config doesn't need frontend counterpart yet.
 * If you need to set per frontend, add require() script in the bottom (You can check settings.php config for pattern).
 * And make sure all frontend will have corresponding config file even if its blank only
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    */

    'default' => 'pusher',

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => 'PUHSR_KEY',
            'secret' =>'PUSHER_SECRET',
            'app_id' =>'PUSHER_APP_ID',
            'options' => [
                //
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

    ],

];