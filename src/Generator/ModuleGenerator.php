<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);

namespace Jiumi\Generator;

use Hyperf\Utils\Filesystem\Filesystem;
use Jiumi\Jiumi;

class ModuleGenerator extends JiumiGenerator
{
    /**
     * @var array
     */
    protected array $moduleInfo;

    /**
     * 设置模块信息
     * @param array $moduleInfo
     * @return $this
     */
    public function setModuleInfo(array $moduleInfo): ModuleGenerator
    {
        $this->moduleInfo = $moduleInfo;
        return $this;
    }

    /**
     * 生成模块基础架构
     */
    public function createModule(): bool
    {
        if (! ($this->moduleInfo['name'] ?? false)) {
            throw new \RuntimeException('模块名称为空');
        }

        $this->moduleInfo['name'] = ucfirst($this->moduleInfo['name']);

        $Jiumi = new Jiumi;
        $Jiumi->scanModule();

        if (! empty($Jiumi->getModuleInfo($this->moduleInfo['name']))) {
            throw new \RuntimeException('同名模块已存在');
        }

        $appPath = BASE_PATH . '/app/';
        $modulePath = $appPath . $this->moduleInfo['name'] . '/';

        /** @var Filesystem $filesystem */
        $filesystem = make(Filesystem::class);
        $filesystem->makeDirectory($appPath . $this->moduleInfo['name']);

        foreach ($this->getGeneratorDirs() as $dir) {
            $filesystem->makeDirectory($modulePath . $dir);
        }

        $this->createConfigJson($filesystem);

        return true;
    }

    /**
     * 创建模块JSON文件
     */
    protected function createConfigJson(Filesystem $filesystem)
    {
        $json = $filesystem->sharedGet($this->getStubDir() . 'config.stub');

        $content = str_replace(
            ['{NAME}','{LABEL}','{DESCRIPTION}', '{VERSION}'],
            [
                $this->moduleInfo['name'],
                $this->moduleInfo['label'],
                $this->moduleInfo['description'],
                $this->moduleInfo['version']
            ],
            $json
        );

        $filesystem->put(BASE_PATH . '/app/' .$this->moduleInfo['name'] . '/config.json', $content);
    }

    /**
     * 生成的目录列表
     */
    protected function getGeneratorDirs(): array
    {
        return [
            'Controller',
            'Model',
            'Listener',
            'Request',
            'Service',
            'Mapper',
            'Database',
            'Middleware',
        ];
    }
}