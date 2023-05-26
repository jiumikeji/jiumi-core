<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi;

use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Jiumi\Traits\ControllerTrait;

/**
 * 后台控制器基类
 * Class JiumiController
 * @package Jiumi
 */
abstract class JiumiController
{
    use ControllerTrait;

    /**
     * @var Jiumi
     */
    #[Inject]
    protected Jiumi $jiumi;
}
