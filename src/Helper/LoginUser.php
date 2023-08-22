<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Helper;

use Jiumi\Interfaces\ServiceInterface\RoleServiceInterface;
use Jiumi\Interfaces\ServiceInterface\UserServiceInterface;
use Jiumi\Exception\TokenException;
use Jiumi\JiumiRequest;
use Jiumikeji\JWTAuth\JWT;
use Psr\SimpleCache\InvalidArgumentException;

class LoginUser
{
    /**
     * @var JWT
     */
    protected JWT $jwt;

    /**
     * @var JiumiRequest
     */
    protected JiumiRequest $request;


    /**
     * LoginUser constructor.
     * @param string $scene 场景，默认为default
     */
    public function __construct(string $scene = 'default')
    {
        /* @var JWT $this->jwt */
        $this->jwt = make(JWT::class)->setScene($scene);
    }

    /**
     * 验证token
     * @param string|null $token
     * @param string $scene
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function check(?string $token = null, string $scene = 'default'): bool
    {
        try {
            if ($this->jwt->checkToken($token, $scene, true, true, true)) {
                return true;
            }
        } catch (InvalidArgumentException $e) {
            throw new TokenException(t('jwt.no_token'));
        } catch (\Throwable $e) {
            throw new TokenException(t('jwt.no_login'));
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
     * 获取当前登录用户信息
     * @param string|null $token
     * @return array
     */
    public function getUserInfo(?string $token = null): array
    {
        return $this->jwt->getParserData($token);
    }

    /**
     * 获取当前登录用户ID
     * @return int
     */
    public function getId(): int
    {
        return $this->jwt->getParserData()['id'];
    }

    /**
     * 获取当前登录用户名
     * @return string
     */
    public function getUsername(): string
    {
        return $this->jwt->getParserData()['username'];
    }

    /**
     * 获取当前登录用户角色
     * @param array $columns
     * @return array
     */
    public function getUserRole(array $columns = ['id', 'name', 'code']): array
    {
        return container()->get(UserServiceInterface::class)->read($this->getId(), ['id'])->roles()->get($columns)->toArray();
    }

    /**
     * 获取当前登录用户岗位
     * @param array $columns
     * @return array
     */
    public function getUserPost(array $columns = ['id', 'name', 'code']): array
    {
        return container()->get(UserServiceInterface::class)->read($this->getId(), ['id'])->posts()->get($columns)->toArray();
    }

    /**
     * 获取当前登录用户部门
     * @param array $columns
     * @return array
     */
    public function getUserDept(array $columns = ['id', 'name']): array
    {
        return container()->get(UserServiceInterface::class)->read($this->getId(), ['id'])->depts()->get($columns)->toArray();
    }

    /**
     * 获取当前token用户类型
     * @return string
     */
    public function getUserType(): string
    {
        return $this->jwt->getParserData()['user_type'];
    }

    /**
     * 是否为超级管理员（创始人），用户禁用对创始人没用
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return env('SUPER_ADMIN') == $this->getId();
    }

    /**
     * 是否为管理员角色
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function isAdminRole(): bool
    {
        return in_array(
            container()->get(RoleServiceInterface::class)->read(env('ADMIN_ROLE'), ['code'])->code,
            container()->get(UserServiceInterface::class)->getInfo()['roles']
        );
    }

    /**
     * 获取Token
     * @param array $user
     * @return string
     * @throws InvalidArgumentException
     */
    public function getToken(array $user): string
    {
        return $this->jwt->getToken($user);
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