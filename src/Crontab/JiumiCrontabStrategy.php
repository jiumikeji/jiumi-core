<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Crontab;

use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;

use function Hyperf\Coroutine\co;

class JiumiCrontabStrategy
{
    /**
     * JiumiCrontabManage
     */
    #[Inject]
    protected JiumiCrontabManage $jiumiCrontabManage;

    /**
     * JiumiExecutor
     */
    #[Inject]
    protected JiumiExecutor $executor;

    /**
     * @param JiumiCrontab $crontab
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function dispatch(JiumiCrontab $crontab)
    {
        co(function() use($crontab) {
            if ($crontab->getExecuteTime() instanceof Carbon) {
                $wait = $crontab->getExecuteTime()->getTimestamp() - time();
                $wait > 0 && \Swoole\Coroutine::sleep($wait);
                $this->executor->execute($crontab);
            }
        });
    }

    /**
     * 执行一次
     * @param JiumiCrontab $crontab
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function executeOnce(JiumiCrontab $crontab)
    {
        co(function() use($crontab) {
            $this->executor->execute($crontab);
        });
    }
}