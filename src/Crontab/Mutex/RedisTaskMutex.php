<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */


declare(strict_types=1);
namespace Jiumi\Crontab\Mutex;

use Hyperf\Redis\RedisFactory;
use Jiumi\Crontab\JiumiCrontab;

class RedisTaskMutex implements TaskMutex
{
    /**
     * @var RedisFactory
     */
    private $redisFactory;

    public function __construct(RedisFactory $redisFactory)
    {
        $this->redisFactory = $redisFactory;
    }

    /**
     * Attempt to obtain a task mutex for the given crontab.
     * @param JiumiCrontab $crontab
     * @return bool
     */
    public function create(JiumiCrontab $crontab): bool
    {
        return (bool) $this->redisFactory->get($crontab->getMutexPool())->set(
            $this->getMutexName($crontab),
            $crontab->getName(),
            ['NX', 'EX' => $crontab->getMutexExpires()]
        );
    }

    /**
     * DeterJiumi if a task mutex exists for the given crontab.
     * @param JiumiCrontab $crontab
     * @return bool
     */
    public function exists(JiumiCrontab $crontab): bool
    {
        return (bool) $this->redisFactory->get($crontab->getMutexPool())->exists(
            $this->getMutexName($crontab)
        );
    }

    /**
     * Clear the task mutex for the given crontab.
     * @param JiumiCrontab $crontab
     */
    public function remove(JiumiCrontab $crontab)
    {
        $this->redisFactory->get($crontab->getMutexPool())->del(
            $this->getMutexName($crontab)
        );
    }

    protected function getMutexName(JiumiCrontab $crontab): string
    {
        return 'framework' . DIRECTORY_SEPARATOR . 'crontab-' . sha1($crontab->getName() . $crontab->getRule());
    }
}
