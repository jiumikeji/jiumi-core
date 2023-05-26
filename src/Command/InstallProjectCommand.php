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
use Hyperf\DbConnection\Db;
use Jiumi\JiumiCommand;
use Jiumi\Jiumi;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class InstallProjectCommand
 * @package System\Command
 */
#[Command]
class InstallProjectCommand extends JiumiCommand
{
    /**
     * 安装命令
     * @var string|null
     */
    protected ?string $name = 'Jiumi:install';

    protected const CONSOLE_GREEN_BEGIN = "\033[32;5;1m";
    protected const CONSOLE_RED_BEGIN = "\033[31;5;1m";
    protected const CONSOLE_END = "\033[0m";

    protected array $database = [];

    protected array $redis = [];

    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php Jiumi:install" install JiumiAdmin system');
        $this->setDescription('JiumiAdmin系统安装命令');

        $this->addOption('option', '-o', InputOption::VALUE_OPTIONAL, 'input "-o reset" is re install JiumiAdmin');
    }

    public function handle()
    {
        // 获取参数
        $option = $this->input->getOption('option');

        // 全新安装
        if ($option === null) {

            if (!file_exists(BASE_PATH . '/.env')) {
                // 欢迎
                $this->welcome();

                // 检测环境
                $this->checkEnv();

                // 设置数据库
                $this->setDataBaseInformationAndRedis();

                $this->line("\n\nReset the \".env\" file. 请重新启动服务，然后运行\nthe installation命令继续安装.", "info");
            } else if (file_exists(BASE_PATH . '/.env') && $this->confirm('Do you want to continue with the installation program?', true)) {

                // 安装本地模块
                $this->installLocalModule();

                // 其他设置
                $this->setOthers();

                // 安装完成
                $this->finish();
            } else {

                // 欢迎
                $this->welcome();

                // 检测环境
                $this->checkEnv();

                // 设置数据库
                $this->setDataBaseInformationAndRedis();

                // 安装本地模块
                $this->installLocalModule();

                // 其他设置
                $this->setOthers();

                // 安装完成
                $this->finish();
            }
        }

        // 重新安装
        if ($option === 'reset') {
            $this->line('重装未完成...', 'error');
        }
    }

    protected function welcome()
    {
        $this->line('-----------------------------------------------------------', 'comment');
        $this->line('您好，欢迎您使用九米管理系统.', 'comment');
        $this->line('安装就要开始了，只需要几个步骤', 'comment');
        $this->line('-----------------------------------------------------------', 'comment');
    }

    protected function checkEnv()
    {
        $answer = $this->confirm('现在要测试系统环境吗?', true);

        if ($answer) {

            $this->line(PHP_EOL . ' 检查环境...' . PHP_EOL, 'comment');

            if (version_compare(PHP_VERSION, '8.0', '<')) {
                $this->error(sprintf(' php version should >= 8.0 >>> %sNO!%s', self::CONSOLE_RED_BEGIN, self::CONSOLE_END));
                exit;
            }
            $this->line(sprintf(" php version %s >>> %sOK!%s", PHP_VERSION, self::CONSOLE_GREEN_BEGIN, self::CONSOLE_END));

            $extensions = ['swoole', 'mbstring', 'json', 'openssl', 'pdo', 'xml'];

            foreach ($extensions as $ext) {
                $this->checkExtension($ext);
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function setDataBaseInformationAndRedis(): void
    {
        $dbAnswer = $this->confirm('是否需要设置数据库信息?', true);
        // 设置数据库
        if ($dbAnswer) {
            $dbchar = $this->ask('请输入数据库字符集，默认值:', 'utf8mb4');
            $dbname = $this->ask('请输入数据库名称，默认值:', 'Jiumiadmin');
            $dbhost = $this->ask('请输入数据库主机，默认值:', '127.0.0.1');
            $dbport = $this->ask('请输入数据库主机端口，默认值:', '3306');
            $prefix = $this->ask('请输入表前缀，默认值:', 'jiumi_');
            $dbuser = $this->ask('请输入数据库用户名，默认值:', 'root');
            $dbpass = '';

            $i = 3;
            while ($i > 0) {
                if ($i === 3) {
                    $dbpass = $this->ask('请输入数据库密码。按“enter”3次，不设置密码');
                } else {
                    $dbpass = $this->ask(sprintf('如果您没有设置数据库密码，请重试按“enter” %d 次', $i));
                }
                if (!empty($dbpass)) {
                    break;
                } else {
                    $i--;
                }
            }

            $this->database = [
                'charset' => $dbchar,
                'dbname'  => $dbname,
                'dbhost'  => $dbhost,
                'dbport'  => $dbport,
                'prefix'  => $prefix === 'Null' ? '' : $prefix,
                'dbuser'  => $dbuser,
                'dbpass'  => $dbpass ?: '',
            ];
        }

        $redisAnswer = $this->confirm('Do you need to set Redis information?', true);

        // 设置Redis
        if ($redisAnswer) {
            $redisHost = $this->ask('请输入redis主机，默认值:', '127.0.0.1');
            $redisPort = $this->ask('请输入redis主机端口，默认值:', '6379');
            $redisPass = $this->ask('请输入redis密码，默认:', 'Null');
            $redisDb   = $this->ask('请输入redis db，默认值:', '0');

            $this->redis = [
                'host' => $redisHost,
                'port' => $redisPort,
                'auth' => $redisPass === 'Null' ? '(NULL)' : $redisPass,
                'db'   => $redisDb,
            ];
        }

        $dbAnswer && $this->generatorEnvFile();
    }

    /**
     * @throws \Exception
     */
    protected function generatorEnvFile()
    {
        try {
            $env = parse_ini_file(BASE_PATH . '/.env.example', true);
            $env['APP_NAME'] = 'JiumiAdmin';
            $env['APP_ENV'] = 'dev';
            $env['DB_DRIVER'] = 'mysql';
            $env['DB_HOST'] = $this->database['dbhost'];
            $env['DB_PORT'] = $this->database['dbport'];
            $env['DB_DATABASE'] = $this->database['dbname'];
            $env['DB_USERNAME'] = $this->database['dbuser'];
            $env['DB_PASSWORD'] = $this->database['dbpass'];
            $env['DB_CHARSET'] = $this->database['charset'];
            $env['DB_COLLATION'] = sprintf('%s_general_ci', $this->database['charset']);
            $env['DB_PREFIX'] = $this->database['prefix'];
            $env['REDIS_HOST'] = $this->redis['host'];
            $env['REDIS_AUTH'] = $this->redis['auth'];
            $env['REDIS_PORT'] = $this->redis['port'];
            $env['REDIS_DB'] = (string) $this->redis['db'];
            $env['AMQP_HOST'] = '127.0.0.7';
            $env['AMQP_PORT'] = '5672';
            $env['AMQP_USER'] = 'guest';
            $env['AMQP_PASSWORD'] = 'guest';
            $env['AMQP_VHOST'] = '/';
            $env['AMQP_ENABLE'] = 'false';
            $env['SUPER_ADMIN'] = 1;
            $env['ADMIN_ROLE'] = 1;
            $env['CONSOLE_SQL'] = 'true';
            $env['JWT_SECRET'] = base64_encode(random_bytes(64));
            $env['JWT_API_SECRET'] = base64_encode(random_bytes(64));

            $id = null;

            $envContent = '';
            foreach ($env as $key => $e) {
                if (!is_array($e)) {
                    $envContent .= sprintf('%s = %s', $key, $e === '1' ? 'true' : ($e === '' ? '' : $e)) . PHP_EOL . PHP_EOL;
                } else {
                    $envContent .= sprintf('[%s]', $key) . PHP_EOL;
                    foreach ($e as $k => $v) {
                        $envContent .= sprintf('%s = %s', $k, $v === '1' ? 'true' : ($v === '' ? '' : $v)) . PHP_EOL;
                    }
                    $envContent .= PHP_EOL;
                }
            }
            $dsn = sprintf("mysql:host=%s;port=%s", $this->database['dbhost'], $this->database['dbport']);
            $pdo = new \PDO($dsn, $this->database['dbuser'], $this->database['dbpass']);
            $isSuccess = $pdo->query(
                sprintf(
                    'CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARSET %s COLLATE %s_general_ci;',
                    $this->database['dbname'],
                    $this->database['charset'],
                    $this->database['charset']
                )
            );

            $pdo = null;

            if ($isSuccess) {
                $this->line($this->getGreenText(sprintf('"%s" 数据库创建成功', $this->database['dbname'])));
                file_put_contents(BASE_PATH . '/.env', $envContent);
            } else {
                $this->line($this->getRedText(sprintf('创建数据库 "%s". 失败。请手动创建', $this->database['dbname'])));
            }
        } catch (\RuntimeException $e) {
            $this->line($this->getRedText($e->getMessage()));
            exit;
        }
    }

    /**
     * install modules
     */
    protected function installLocalModule()
    {
        /* @var Jiumi $Jiumi */
        $this->line("即将开始安装本地模块...\n", 'comment');
        $Jiumi = make(Jiumi::class);
        $modules = $Jiumi->getModuleInfo();
        foreach ($modules as $name => $info) {
            $this->call('Jiumi:migrate-run', ['name' => $name, '--force' => 'true']);
            if ($name === 'System') {
                $this->initUserData();
            }
            $this->call('Jiumi:seeder-run',  ['name' => $name, '--force' => 'true']);
            $this->line($this->getGreenText(sprintf('"%s" module install successfully', $name)));
        }
    }

    protected function setOthers()
    {
        $this->line(PHP_EOL . ' JiumiAdmin 设置其他项...' . PHP_EOL, 'comment');
        $this->call('Jiumi:update');
    }

    protected function initUserData()
    {
        // 清理数据
        Db::table('system_user')->truncate();
        Db::table('system_role')->truncate();
        Db::table('system_user_role')->truncate();

        // 创建超级管理员
        Db::table("system_user")->insert([
            'id' => env('SUPER_ADMIN', 1),
            'username' => 'jiumiAdmin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'user_type' => '100',
            'nickname' => '创始人',
            'email' => 'admin@adminJiumi.com',
            'phone' => '13785208521',
            'signed' => '九米科技成立于2013年，是致力于应用软件、平台系统类软件的定制开发及互联网趋势研究的技术服务型企业',
            'dashboard' => 'statistics',
            'created_by' => 0,
            'updated_by' => 0,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        // 创建管理员角色
        Db::table('system_role')->insert([
            'id' => env('ADMIN_ROLE', 1),
            'name' => '超级管理员（创始人）',
            'code' => 'jiumiAdmin',
            'data_scope' => 0,
            'sort' => 0,
            'created_by' => env('SUPER_ADMIN', 0),
            'updated_by' => 0,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'remark' => '系统内置角色，不可删除'
        ]);
        Db::table('system_user_role')->insert([
            'user_id' => env('SUPER_ADMIN', 1),
            'role_id' => env('ADMIN_ROLE', 1)
        ]);
    }

    protected function finish(): void
    {
        $i = 5;
        $this->output->write(PHP_EOL . $this->getGreenText('The installation is almost complete'), false);
        while ($i > 0) {
            $this->output->write($this->getGreenText('.'), false);
            $i--;
            sleep(1);
        }
        $this->line(PHP_EOL . sprintf('%s
JiumiAdmin Version: %s
默认用户名: jiumiAdmin
默认密码: admin123', $this->getInfo(), Jiumi::getVersion()), 'comment');
    }

    /**
     * @param $extension
     */
    protected function checkExtension($extension): void
    {
        if (!extension_loaded($extension)) {
            $this->line(sprintf(" %s extension not install >>> %sNO!%s", $extension, self::CONSOLE_RED_BEGIN, self::CONSOLE_END));
            exit;
        }
        $this->line(sprintf(' %s extension is installed >>> %sOK!%s', $extension, self::CONSOLE_GREEN_BEGIN, self::CONSOLE_END));
    }
}
