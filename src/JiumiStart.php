<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi;

use App\Setting\Service\ModuleService;
use Hyperf\Framework\Bootstrap\ServerStartCallback;

class JiumiStart extends ServerStartCallback
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function beforeStart()
    {
        $service = container()->get(ModuleService::class);
        $service->setModuleCache();
        $console = console();
        $console->info('JiumiAdmin start success...');
        $console->info($this->welcome());
        $console->info('current booting the user: ' . shell_exec('whoami'));
    }

    protected function welcome(): string
    {
        return sprintf('
/---------------------- welcome to use -----------------------\
|               _                ___       __          _      |
|    ____ ___  (_)___  _____    /   | ____/ /___ ___  (_)___  |
|   / __ `__ \/ / __ \/ ___/   / /| |/ __  / __ `__ \/ / __ \ |
|  / / / / / / / / / / /__/   / ___ / /_/ / / / / / / / / / / |
| /_/ /_/ /_/_/_/ /_/\___/   /_/  |_\__,_/_/ /_/ /_/_/_/ /_/  |
|                                                             |
\_____________  Copyright JiumiAdmin 2021 ~ %s  _____________|
', date('Y'));
    }
}