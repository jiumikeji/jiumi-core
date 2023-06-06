<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Helper;

use Hyperf\HttpServer\Contract\RequestInterface;
use Jiumikeji\JWTAuth\JWT;
use Psr\SimpleCache\InvalidArgumentException;

class AppVerify
{
    /**
     * @var JWT
     */
    protected JWT $jwt;

    public RequestInterface $request;

    /**
     * AppVerify constructor.
     * @param string $scene 场景，默认为default
     */
    public function __construct(string $scene = 'api')
    {
        /* @var JWT $this->jwt */
        $this->jwt = make(JWT::class)->setScene($scene);
        $this->request = make(RequestInterface::class);
    }

    /**
     * 验证token
     * @param String|null $token
     * @param string $scene
     * @return bool
     * @throws InvalidArgumentException
     */
    public function check(?String $token = null, string $scene = 'api'): bool
    {
        try {
            if ($this->jwt->checkToken($token, $scene, true, true, true)) {
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    /**
     * 获取JWT对象
     * @return Jwt
     */
    public function getJwt(): Jwt
    {
        return $this->jwt;
    }

    /**
     * 获取当前APP的信息
     * @return array
     */
    public function getAppInfo(): array
    {
        $params = $this->request->getQueryParams() ?? null;
        return $this->jwt->getParserData($params['access_token']);
    }

    /**
     * 获取apiID
     * @return string
     */
    public function getApiId(): string
    {
        $accessToken = $this->request->getQueryParams()['access_token'] ?? null;
        return (string) $this->jwt->getParserData($accessToken)['id'];
    }

    /**
     * 获取当前APP_ID
     * @return string
     */
    public function getAppId(): string
    {
        $accessToken = $this->request->getQueryParams()['access_token'] ?? null;
        return (string) $this->jwt->getParserData($accessToken)['app_id'];
    }

    /**
     * 获取Token
     * @param array $apiInfo
     * @return string
     * @throws InvalidArgumentException
     */
    public function getToken(array $apiInfo): string
    {
        return $this->jwt->getToken($apiInfo);
    }

    /**
     * 刷新token
     * @return string
     * @throws InvalidArgumentException
     */
    public function refresh(): string
    {
        $accessToken = $this->request->getQueryParams()['access_token'] ?? null;
        return $this->jwt->refreshToken($accessToken);
    }
}