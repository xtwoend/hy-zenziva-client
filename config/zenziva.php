<?php


return [
    'user_key' => env('ZENZIVA_USER_KEY'),
    'api_key' => env('ZENZIVA_API_KEY'),
    'mode' => env('ZENZIVA_DEFAULT', 'sms'), // default sender sms or wa
    'http' => [
        'max_connection' => 10,
        'retries' => 1,
        'delay' => 5,
        'timeout' => 5
    ],
];