<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi;

use Hyperf\HttpServer\Server;

class JiumiServer extends Server
{
    protected ?string $serverName = 'JiumiAdmin';

    protected $routes;

    public function onRequest($request, $response): void
    {
        parent::onRequest($request, $response);
        $this->bootstrap();
    }

    /**
     * JiumiServer bootstrap
     * @return void
     */
    protected function bootstrap(): void
    {
    }
}
