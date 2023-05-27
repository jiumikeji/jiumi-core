<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Command\Migrate;

use Hyperf\Command\ConfirmableTrait;
use Hyperf\Database\Commands\Migrations\BaseCommand;
use Hyperf\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Hyperf\Command\Annotation\Command;

/**
 * Class JiumiMigrateRollback
 * @package System\Command\Migrate
 */
#[Command]
class JiumiMigrateRollback extends BaseCommand
{
    use ConfirmableTrait;

    protected ?string $name = 'jiumi:migrate-rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Run rollback the database migrations';

    /**
     * The migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    protected $module;

    /**
     * Create a new migration command instance.
     * @param Migrator $migrator
     */
    public function __construct(Migrator $migrator)
    {
        parent::__construct();

        $this->migrator = $migrator;

        $this->setDescription('九米框架模块的运行迁移回滚类');
    }

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->module = trim($this->input->getArgument('name'));

        $this->prepareDatabase();

        // Next, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        $this->migrator->setOutput($this->output)
            ->rollback($this->getMigrationPaths(), [
                'pretend' => $this->input->getOption('pretend'),
                'step' => $this->input->getOption('step'),
            ]);

        // Finally, if the "seed" option has been given, we will re-run the database
        // seed task to re-populate the database, which is convenient when adding
        // a migration and a seed at the same time, as it is only this command.
        if ($this->input->getOption('seed') && ! $this->input->getOption('pretend')) {
            $this->call('db:seed', ['--force' => true]);
        }
    }

    protected function getOptions(): array
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, '要使用的数据库连接'],
            ['force', null, InputOption::VALUE_NONE, '在生产环境中强制运行该操作'],
            ['path', null, InputOption::VALUE_OPTIONAL, '要执行的迁移文件的路径'],
            ['realpath', null, InputOption::VALUE_NONE, '指出任何提供的迁移文件路径都是预先解析的绝对路径'],
            ['pretend', null, InputOption::VALUE_NONE, '转储将要运行的SQL查询'],
            ['seed', null, InputOption::VALUE_NONE, '指示是否应该重新运行任务'],
            ['step', null, InputOption::VALUE_NONE, '强制运行迁移，以便可以单独回滚迁移'],
        ];
    }

    /**
     * Prepare the migration database for running.
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->input->getOption('database') ?? 'default');
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, '请输入要运行的模块'],
        ];
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath(): string
    {
        return BASE_PATH . '/app/' . ucfirst($this->module) . '/Database/Migrations';
    }
}
