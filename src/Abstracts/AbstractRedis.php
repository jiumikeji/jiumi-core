<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare (strict_types = 1);
namespace Jiumi\Abstracts;

use Hyperf\Config\Annotation\Value;

/**
 * Class AbstractRedis
 * @package Jiumi\Abstracts
 */
abstract class AbstractRedis
{
    /**
     * 缓存前缀
     */
    #[Value("cache.default.prefix")]
    protected string $prefix;

    /**
     * key 类型名
     */
    protected string $typeName;

    /**
     * 获取实例
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function getInstance()
    {
        return container()->get(static::class);
    }

    /**
     * 获取redis实例
     * @return \Hyperf\Redis\Redis
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function redis(): \Hyperf\Redis\Redis
    {
        return redis();
    }

    /**
     * 获取key
     * @param string $key
     * @return string|null
     */
    public function getKey(string $key): ?string
    {
        return empty($key) ? null : ($this->prefix . trim($this->typeName, ':') . ':' . $key);
    }

}