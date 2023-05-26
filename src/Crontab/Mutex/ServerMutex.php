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

interface ServerMutex
{
    /**
     * Attempt to obtain a server mutex for the given crontab.
     * @param JiumiCrontab $crontab
     * @return bool
     */
    public function attempt(JiumiCrontab $crontab): bool;

    /**
     * Get the server mutex for the given crontab.
     * @param JiumiCrontab $crontab
     * @return string
     */
    public function get(JiumiCrontab $crontab): string;
}
