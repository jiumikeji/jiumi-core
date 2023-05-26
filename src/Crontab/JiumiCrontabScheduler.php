<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */


declare(strict_types=1);
namespace Jiumi\Crontab;


class JiumiCrontabScheduler
{
    /**
     * JiumiCrontabManage
     */
    protected JiumiCrontabManage $crontabManager;

    /**
     * \SplQueue
     */
    protected \SplQueue $schedules;

    /**
     * JiumiCrontabScheduler constructor.
     * @param JiumiCrontabManage $crontabManager
     */
    public function __construct(JiumiCrontabManage $crontabManager)
    {
        $this->schedules = new \SplQueue();
        $this->crontabManager = $crontabManager;
    }

    public function schedule(): \SplQueue
    {
        foreach ($this->getSchedules() ?? [] as $schedule) {
            $this->schedules->enqueue($schedule);
        }
        return $this->schedules;
    }

    protected function getSchedules(): array
    {
        return $this->crontabManager->getCrontabList();
    }
}
