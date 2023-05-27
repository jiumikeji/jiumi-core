<?php

declare(strict_types=1);

/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

namespace Jiumi\Factory;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;

use Hyperf\Logger\LoggerFactory;
use container;
use function class_exists;
use function interface_exists;

class DependProxyFactory
{
    public static function define(string $name, string $definition, bool $isLogger = true): void
    {
        /** @var ContainerInterface $container */
        $container = ApplicationContext::getContainer();
        $config = $container->get(ConfigInterface::class);

        if (interface_exists($definition) || class_exists($definition)) {
            $config->set("dependencies.{$name}", $definition);
            $container->define($name, $definition);
        }
        if (interface_exists($name)) {
            $config->set("jiumiadmin.dependProxy.{$name}", $definition);
        }

         if ($container->has($name)) {
             $isLogger && logger()->info(
                 sprintf('依赖项 [%s] 注入 [%s] 成功.', $definition, $name)
             );
         } else {
             $isLogger && logger()->warning(sprintf('依赖项 [%s] 注入 [%s] 失败.', $definition, $name));
         }
    }
}
