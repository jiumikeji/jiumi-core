<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */


declare(strict_types=1);
namespace Jiumi\Helper;

use Xmo\JWTAuth\JWT;
use Psr\SimpleCache\InvalidArgumentException;

class AppVerify
{
    /**
     * @var JWT
     */
    protected JWT $jwt;


    /**
     * AppVerify constructor.
     * @param string $scene 场景，默认为default
     */
    public function __construct(string $scene = 'api')
    {
        /* @var JWT $this->jwt */
        $this->jwt = make(JWT::class)->setScene($scene);
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
     * 获取当前API的信息
     * @return array
     */
    public function getUserInfo(): array
    {
        return $this->jwt->getParserData();
    }

    /**
     * 获取当前ID
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->jwt->getParserData()['id'];
    }

    /**
     * 获取当前APP_ID
     * @return string
     */
    public function getAppId(): string
    {
        return (string) $this->jwt->getParserData()['app_id'];
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
        return $this->jwt->refreshToken();
    }
}