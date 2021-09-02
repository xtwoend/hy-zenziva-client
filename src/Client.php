<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Xtwoend\ZenzivaClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Coroutine;

class Client implements ZenzivaClientInterface
{
    public const HOST_SMS = 'https://console.zenziva.net/reguler/api/sendsms/';

    public const HOST_WA = 'https://console.zenziva.net/wareguler/api/sendWA/';

    public const HOST_WA_FILE = 'https://console.zenziva.net/wareguler/api/sendWAFile/';

    protected $httpClient;

    protected $mode;

    protected $userKey;

    protected $apiKey;

    public function __construct()
    {
        $this->mode = config('zenziva.mode', 'sms');
        $this->userKey = config('zenziva.user_key');
        $this->apiKey = config('zenziva.api_key');
        $this->init();
    }

    public function send($to, $text)
    {
        $url = ($this->mode == 'sms') ? self::HOST_SMS : self::HOST_WA;

        $data = [
            'userkey' => $this->userKey,
            'passkey' => $this->apiKey,
            'to' => $to,
            'message' => $text,
        ];

        $response = $this->httpClient->request('POST', $url, [
            'form_params' => $data,
        ]);

        $body = (string) $response->getBody();

        return Json::decode($body);
    }

    public function sendWaFile($to, $message, $file)
    {
        $data = [
            'userkey' => $this->userKey,
            'passkey' => $this->apiKey,
            'to' => $to,
            'link' => $file,
            'caption' => $message,
        ];

        $response = $this->httpClient->request('POST', self::HOST_WA_FILE, [
            'form_params' => $data,
        ]);

        $body = (string) $response->getBody();

        return Json::decode($body);
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
            'timeout' => config('zenziva.http.timeout', 5),
            'config' => [
                'handler' => $stack,
            ],
        ]);

        return $this->httpClient;
    }
}
