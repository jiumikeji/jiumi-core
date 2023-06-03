<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types = 1);
namespace Jiumi\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 设置某个万能通用接口状态，true 允许使用，false 禁止使用
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RemoteState extends AbstractAnnotation
{
    /**
     * @param bool $state 状态
     */
    public function __construct(public bool $state = true) {}
}