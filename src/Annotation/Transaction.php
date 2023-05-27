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
 * 数据库事务注解。
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Transaction extends AbstractAnnotation
{
    /**
     * @param int $retry 重试次数
     */
    public function __construct(public int $retry = 1) {}
}