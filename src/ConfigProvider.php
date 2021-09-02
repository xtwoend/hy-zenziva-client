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

use Xtwoend\ZenzivaClient\ZenzivaClientInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ZenzivaClientInterface::class => Client::class
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for zenziva.',
                    'source' => __DIR__ . '/../config/zenziva.php',
                    'destination' => BASE_PATH . '/config/autoload/zenziva.php',
                ],
            ],
        ];
    }
}
