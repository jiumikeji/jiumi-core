<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types = 1);
namespace Jiumi\Command\Migrate;

use Hyperf\Database\Migrations\MigrationCreator;

class JiumiMigrationCreator extends MigrationCreator
{

    public function stubPath(): string
    {
        return BASE_PATH . '/vendor/jiumikeji/jiumi-core/src/Migrate/Stubs';
    }
}
