<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */


declare(strict_types=1);
namespace Jiumi\Crontab\Mutex;

use Jiumi\Crontab\JiumiCrontab;

interface TaskMutex
{
    /**
     * Attempt to obtain a task mutex for the given crontab.
     * @param JiumiCrontab $crontab
     * @return bool
     */
    public function create(JiumiCrontab $crontab): bool;

    /**
     * DeterJiumi if a task mutex exists for the given crontab.
     * @param JiumiCrontab $crontab
     * @return bool
     */
    public function exists(JiumiCrontab $crontab): bool;

    /**
     * Clear the task mutex for the given crontab.
     * @param JiumiCrontab $crontab
     */
    public function remove(JiumiCrontab $crontab);
}
