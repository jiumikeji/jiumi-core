<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */


declare(strict_types=1);

namespace Jiumi\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Jiumi\Exception\NoPermissionException;
use Jiumi\Helper\JiumiCode;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class TokenExceptionHandler
 * @package Jiumi\Exception\Handler
 */
class NoPermissionExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();
        $format = [
            'success' => false,
            'message' => $throwable->getMessage(),
            'code'    => JiumiCode::NO_PERMISSION,
        ];
        return $response->withHeader('Server', 'JiumiAdmin')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods','GET,PUT,POST,DELETE,OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'accept-language,authorization,lang,uid,token,Keep-Alive,User-Agent,Cache-Control,Content-Type')
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus(403)->withBody(new SwooleStream(Json::encode($format)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof NoPermissionException;
    }
}
