<?php

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
    | Supported: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast service
    |--------------------------------------------------------------------------
    |
    | The service used for broadcasting needs. While Pusher is recommented
    | for development, Voten uses a custom echo service runned on our own
    | server. More info: "github.com/tlaverdure/laravel-echo-server"
    |
    | Supported: "pusher", "echo"
    |
    */

    'service' => env('BROADCAST_SERVICE', 'echo'),

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
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                //
            ],
        ],
        'echo' => [
            'app_address'  => env('ECHO_ADDRESS','http://localhost:6001'),
            'app_origin_address'  => env('ECHO_ORIGIN_ADDRESS','http://localhost:6001'),
            'app_id'       => env('ECHO_APP_ID'),
            'auth_key'     => env('ECHO_AUTH_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
