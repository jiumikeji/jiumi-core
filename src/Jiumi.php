<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);

namespace Jiumi;

use Hyperf\Utils\Filesystem\Filesystem;

class Jiumi
{
    private static string $jiumiName = 'JiumiAdmin';
    /**
     * @var string
     */
    private static string $version = '1.3.0';

    /**
     * @var string
     */
    private string $appPath = '';

    /**
     * @var array
     */
    private array $moduleInfo = [];

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct()
    {
        $this->setAppPath(BASE_PATH . '/app');
        $this->scanModule();
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function scanModule(): void
    {
        $modules = glob(self::getAppPath() . '*');
        $fs = container()->get(Filesystem::class);
        $infos = [];
        foreach ($modules as &$mod) if (is_dir($mod)) {
            $modInfo = $mod . DIRECTORY_SEPARATOR . 'config.json';
            if (file_exists($modInfo)) {
                $infos[basename($mod)] = json_decode($fs->sharedGet($modInfo), true);
            }
        }
        $sortId = array_column($infos, 'order');
        array_multisort($sortId, SORT_ASC, $infos);
        $this->setModuleInfo($infos);
    }

    /**
     * @return string
     */
    public static function getVersion(): string
    {
        return self::$version;
    }

    /**
     * @return string
     */
    public static function getJiumiName(): string
    {
        return self::$jiumiName;
    }

    /**
     * @return mixed
     */
    public function getAppPath(): string
    {
        return $this->appPath . DIRECTORY_SEPARATOR;
    }

    /**
     * @param mixed $appPath
     */
    public function setAppPath(string $appPath): void
    {
        $this->appPath = $appPath;
    }

    /**
     * @param string|null $name
     * @return mixed
     */
    public function getModuleInfo(string $name = null): array
    {
        if (empty($name)) {
            return $this->moduleInfo;
        }
        return $this->moduleInfo[$name] ?? [];
    }

    /**
     * @param mixed $moduleInfo
     */
    public function setModuleInfo($moduleInfo): void
    {
        $this->moduleInfo = $moduleInfo;
    }

    /**
     * @param String $key
     * @param string $value
     * @param false $save
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setModuleConfigValue(String $key, string $value, bool $save = false): bool
    {
        if (strpos($key, '.') > 0) {
            list($mod, $name) = explode('.', $key);
            if (isset($this->moduleInfo[$mod]) && isset($this->moduleInfo[$mod][$name])) {
                $this->moduleInfo[$mod][$name] = $value;
                $save && $this->saveModuleConfig($mod);
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $mod
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function saveModuleConfig(string $mod): void
    {
        if (!empty($mod)) {
            $fs = container()->get(Filesystem::class);
            $modJson = $this->getAppPath() . $mod . DIRECTORY_SEPARATOR . 'config.json';
            if (! $fs->isWritable($modJson)) {
                $fs->chmod($modJson, 666);
            }
            $fs->put($modJson, \json_encode($this->getModuleInfo($mod), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
    }
}