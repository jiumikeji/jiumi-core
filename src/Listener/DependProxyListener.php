<?php

declare(strict_types=1);

/**
 * JiumiAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using JiumiAdmin.
 *
 * @Author @小小只^v^ <littlezov@qq.com>, X.Mo<root@imoi.cn>
 * @Link   https://gitee.com/jiumikeji/JiumiAdmin
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
                DependProxyFactory::define($target, $definition, true);
            }
        }
    }
}
