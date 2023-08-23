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
use Jiumi\Helper\Str;
use Jiumi\JiumiCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class JwtCommand
 * @package System\Command
 */
#[Command]
class JwtCommand extends JiumiCommand
{
    /**
     * 生成JWT密钥命令
     * @var string|null
     */
    protected ?string $name = 'jiumi:jwt-gen';

    public function configure()
    {
        parent::configure();
        $this->setHelp('run "php bin/hyperf.php jiumi:gen-jwt" create the new jwt secret');
        $this->setDescription('JiumiAdmin system gen jwt command');
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $jwtSecret = Str::upper($this->input->getOption('jwtSecret'));

        if (empty($jwtSecret)) {
            $this->line('Missing parameter <--jwtSecret < jwt secret name>>', 'error');
        }

        $envPath = BASE_PATH . '/.env';

        if (! file_exists($envPath)) {
            $this->line('.env file not is exists!', 'error');
        }

        $key = base64_encode(random_bytes(64));

        if (Str::contains(file_get_contents($envPath), $jwtSecret) === false) {
            file_put_contents($envPath, "\n{$jwtSecret}={$key}\n", FILE_APPEND);
        } else {
            file_put_contents($envPath, preg_replace(
                "~{$jwtSecret}\s*=\s*[^\n]*~",
                "{$jwtSecret}=\"{$key}\"",
                file_get_contents($envPath)
            ));
        }

        $this->info('jwt secret generator successfully:' . $key);

    }

    protected function getOptions(): array
    {
        return [
            ['jwtSecret', '', InputOption::VALUE_REQUIRED, 'Please enter the jwtSecret to be generated'],
        ];
    }


}