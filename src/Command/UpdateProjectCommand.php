<?php

/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);

namespace Jiumi\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Seeders\Seed;
use Hyperf\Database\Migrations\Migrator;
use Jiumi\JiumiCommand;
use Jiumi\Jiumi;

/**
 * Class UpdateProjectCommand
 * @package System\Command
 */
#[Command]
class UpdateProjectCommand extends JiumiCommand
{
    /**
     * 更新项目命令
     * @var string|null
     */
    protected ?string $name = 'jiumi:update';

    protected array $database = [];

    protected Seed $seed;

    protected Migrator $migrator;

    /**
     * UpdateProjectCommand constructor.
     * @param Migrator $migrator
     * @param Seed $seed
     */
    public function __construct(Migrator $migrator, Seed $seed)
    {
        parent::__construct();
        $this->migrator = $migrator;
        $this->seed = $seed;
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php jiumi:update" Update JiumiAdmin system');
        $this->setDescription('JiumiAdmin system update command');
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $modules = make(Jiumi::class)->getModuleInfo();
        $basePath = BASE_PATH . '/app/';
        $this->migrator->setConnection('default');

        foreach ($modules as $name => $module) {
            $seedPath = $basePath . $name . '/Database/Seeders/Update';
            $migratePath = $basePath . $name . '/Database/Migrations/Update';

            if (is_dir($migratePath)) {
                $this->migrator->run([$migratePath]);
            }

            if (is_dir($seedPath)) {
                $this->seed->run([$seedPath]);
            }
        }

        redis()->flushDB();

        $this->line($this->getGreenText('updated successfully...'));
    }
}
