<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types = 1);
namespace Jiumi\Command\Creater;

use Hyperf\DbConnection\Db;
use Jiumi\Jiumi;
use Jiumi\JiumiCommand;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CreateModel
 * @package System\Command\Creater
 */
#[Command]
class CreateModel extends JiumiCommand
{
    protected ?string $name = 'jiumi:model-gen';

    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php jiumi:model-gen <--module | -M <module>> [--table | -T [table]]"');
        $this->setDescription('Generate model to module according to data table');
    }

    public function handle()
    {
        $jiumi = make(Jiumi::class);
        $module = $this->input->getOption('module');
        if ($module) {
            $module = ucfirst(trim($this->input->getOption('module')));
        }

        $table  = $this->input->getOption('table');
        if ($table) {
            $table = trim($this->input->getOption('table'));
        }

        if (empty($module)) {
            $this->line('Missing parameter <--module < module_name>>', 'error');
        }

        $moduleInfos = $jiumi->getModuleInfo();

        if (isset($moduleInfos[$module])) {
            $info = $moduleInfos[$module];
            $path = "app/{$module}/Model";

            $db = env('DB_DATABASE');
            $prefix = env('DB_PREFIX');

            $tables = Db::select('SHOW TABLES');
            $key = "Tables_in_{$db}";

            $tableList = [];
            foreach ($tables as $k) {
                $tmp = $k->$key;
                if (!empty($prefix) && preg_match(sprintf("/%s_%s[_a-zA-Z0-9]+/i", $prefix, $module), $tmp)) {
                    $tableList[] = $tmp;
                }
                if (preg_match(sprintf("/%s[_a-zA-Z0-9]+/i", $module), $tmp)) {
                    $tableList[] = $tmp;
                }
            }

            if (!empty($table)) {
                if (!in_array($table, $tableList)) {
                    $this->confirm("Table \"{$table}\" does not exist or not belong to the \"{$module}\" module. Are you sure to generate the model?", false)
                    &&
                    $this->call('gen:model', ['table' => $table, '--path' => $path]);
                } else {
                    $this->call('gen:model', ['table' => $table, '--path' => $path]);
                }
            } else {
                foreach ($tableList as $table) {
                    $this->call('gen:model', ['table' => $table, '--path' => $path]);
                }
            }
        }
    }

    protected function getOptions(): array
    {
        return [
            ['module', '-M', InputOption::VALUE_REQUIRED, 'Please enter the module to be generated'],
            ['table', '-T', InputOption::VALUE_OPTIONAL, 'Which table you want to associated with the Model.']
        ];
    }
}