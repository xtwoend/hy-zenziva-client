<?php

namespace Xtwoend\ZenzivaClient;


use GuzzleHttp\Client as GuzzleClient;
use Hyperf\Utils\Coroutine;
use GuzzleHttp\HandlerStack;
use Hyperf\Utils\Codec\Json;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;

class Client implements ZenzivaClientInterface
{   
    const HOST_SMS = 'https://console.zenziva.net/reguler/api/sendsms/';
    const HOST_WA = 'https://console.zenziva.net/wareguler/api/sendWA/';
    
    protected $httpClient;
    protected $mode;
    protected $userKey;
    protected $apiKey;

    public function __construct() 
    {
        $this->mode = config('zenziva.mode', 'sms');
        $this->userkey = config('zenziva.user_key');
        $this->apiKey = config('zenziva.api_key');
        $this->init();
    }

    public function send($to, $text)
    {
        $data = [
            'userkey' => $this->userKey,
            'passkey' => $this->apiKey,
            'to' => $to,
            'message' => $text
        ];

        $response  = $this->httpClient->request('POST', '', [
            'form_params' => $data
        ]);

        $body = (string) $response->getBody();

        $json = Json::decode($body);

        return $json;
    }

    public function sendWaFile($to, $message, $file)
    {
        $data = [
            'userkey' => $this->userKey,
            'passkey' => $this->apiKey,
            'to' => $to,
            'link' => $file,
            'caption' => $message
        ];

        $response  = $this->httpClient->request('POST', '', [
            'form_params' => $data
        ]);

        $body = (string) $response->getBody();

        $json = Json::decode($body);

        return $json;
    }

    protected function init()
    {
        $handler = null;

        if (Coroutine::inCoroutine()) {
            $handler = make(PoolHandler::class, [
                'option' => [
                    'max_connections' => config('zenziva.http.max_connection', 50),
                ],
            ]);
        }

        // Default retry Middleware
        $retry = make(RetryMiddleware::class, [
            'retries' => config('zenziva.http.retries', 1),
            'delay' => config('zenziva.http.delay', 5),
        ]);

        $stack = HandlerStack::create($handler);
        $stack->push($retry->getMiddleware(), 'retry');

        $this->httpClient = make(GuzzleClient::class, [
            'base_uri' => ($this->mode == 'sms')? HOST_SMS : HOST_WA,
            'timeout' => config('zenziva.http.timeout', 5),
            'config' => [
                'handler' => $stack,
            ],
        ]);

        return $this->httpClient;
    }
}