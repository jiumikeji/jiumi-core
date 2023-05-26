<?php

declare(strict_types=1);

/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

namespace Jiumi\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Jiumi\Annotation\DependProxyCollector;
use Jiumi\Factory\DependProxyFactory;

#[Listener]
class DependProxyListener implements ListenerInterface
{
    public function listen(): array
    {
        return [ BootApplication::class ];
    }

    public function process(object $event): void
    {
        foreach (DependProxyCollector::list() as $collector) {
            $targets = $collector->values;
            $definition = $collector->provider;
            foreach ($targets as $target) {
                console()->info($target);
                DependProxyFactory::define($target, $definition, true);
            }
        }
    }
}
