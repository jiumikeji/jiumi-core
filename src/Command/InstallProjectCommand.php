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
    protected ?string $name = 'jiumi:install';

    protected const CONSOLE_GREEN_BEGIN = "\033[32;5;1m";
    protected const CONSOLE_RED_BEGIN = "\033[31;5;1m";
    protected const CONSOLE_END = "\033[0m";

    protected array $database = [];

    protected array $redis = [];


    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php jiumi:install" install JiumiAdmin system');
        $this->setDescription('JiumiAdmin system install command');

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

                $this->line("\n\nReset the \".env\" file. Please restart the service before running \nthe installation command to continue the installation.", "info");
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
            $this->line('Reinstallation is not complete...', 'error');
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
        $answer = $this->confirm('Do you want to test the system environment now?', true);

        if ($answer) {

            $this->line(PHP_EOL . ' Checking environmenting...' . PHP_EOL, 'comment');

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
        $dbAnswer = $this->confirm('Do you need to set Database information?', true);
        // 设置数据库
        if ($dbAnswer) {
            $dbchar = $this->ask('please input database charset, default:', 'utf8mb4');
            $dbname = $this->ask('please input database name, default:', 'jiumiadmin');
            $dbhost = $this->ask('please input database host, default:', '127.0.0.1');
            $dbport = $this->ask('please input database host port, default:', '3306');
            $prefix = $this->ask('please input table prefix, default:', 'jiumi_');
            $dbuser = $this->ask('please input database username, default:', 'root');
            $dbpass = '';

            $i = 3;
            while ($i > 0) {
                if ($i === 3) {
                    $dbpass = $this->ask('Please input database password. Press "enter" 3 number of times, not setting the password');
                } else {
                    $dbpass = $this->ask(sprintf('If you don\'t set the database password, please try again press "enter" %d number of times', $i));
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
            $redisHost = $this->ask('please input redis host, default:', '127.0.0.1');
            $redisPort = $this->ask('please input redis host port, default:', '6379');
            $redisPass = $this->ask('please input redis password, default:', 'Null');
            $redisDb   = $this->ask('please input redis db, default:', '0');

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
                $this->line($this->getGreenText(sprintf('"%s" database created successfully', $this->database['dbname'])));
                file_put_contents(BASE_PATH . '/.env', $envContent);
            } else {
                $this->line($this->getRedText(sprintf('Failed to create database "%s". Please create it manually', $this->database['dbname'])));
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
        /* @var Jiumi $jiumi */
        $this->line("Installation of local modules is about to begin...\n", 'comment');
        $jiumi = make(Jiumi::class);
        $modules = $jiumi->getModuleInfo();
        foreach ($modules as $name => $info) {
            $this->call('jiumi:migrate-run', ['name' => $name, '--force' => 'true']);
            if ($name === 'System') {
                $this->initUserData();
            }
            $this->call('jiumi:seeder-run',  ['name' => $name, '--force' => 'true']);
            $this->line($this->getGreenText(sprintf('"%s" module install successfully', $name)));
        }
    }

    protected function setOthers()
    {
        $this->line(PHP_EOL . ' JiumiAdmin set others items...' . PHP_EOL, 'comment');
        $this->call('jiumi:update');
        $this->call('jiumi:jwt-gen', [ '--jwtSecret' => 'JWT_SECRET' ]);
        $this->call('jiumi:jwt-gen', [ '--jwtSecret' => 'JWT_API_SECRET' ]);

        if (! file_exists(BASE_PATH . '/config/autoload/jiumiadmin.php')) {
            $this->call('vendor:publish', [ 'package' => 'jiumikeji/jiumi' ]);
        }

        //$downloadFrontCode = $this->confirm('Do you downloading the front-end code to "./web" directory?', true);

        // 下载前端代码
		/*
        if ($downloadFrontCode) {
            $this->line(PHP_EOL . ' Now about to start downloading the front-end code' . PHP_EOL, 'comment');
            if (\shell_exec('which git')) {
                \system('git clone https://gitee.com/jiumiadmin/jiumiadmin-vue.git ./web/');
            } else {
                $this->warn('您的服务器没有安装“git”命令，将跳过下载前端项目');
            }
        }*/
    }

    protected function initUserData()
    {
        // 清理数据
        Db::table('system_user')->truncate();
        Db::table('system_role')->truncate();
        Db::table('system_user_role')->truncate();
        if (\Hyperf\Database\Schema\Schema::hasTable('system_user_dept')) {
            Db::table('system_user_dept')->truncate();
        }

        // 创建超级管理员
        Db::table("system_user")->insert([
            'id' => env('SUPER_ADMIN', 1),
            'username' => 'jiumiAdmin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'user_type' => '100',
            'nickname' => '河北九米电子科技有限公司',
            'email' => '10056446@qq.com',
            'phone' => '13785208521',
            'signed' => '成立于2013年，是致力于应用软件、平台系统类软件的定制开发及互联网趋势研究的技术服务型企业',
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
default username: jiumiAdmin
default password: admin123', $this->getInfo(), Jiumi::getVersion()), 'comment');
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
