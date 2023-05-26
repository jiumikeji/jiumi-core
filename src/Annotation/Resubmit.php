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
 * 禁止重复提交
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Resubmit extends AbstractAnnotation
{
    /**
     * second
     * @var int
     */
    public int $second = 3;

    /**
     * 提示信息
     * @var string
     */
    public string $message;

    public function __construct($value, $message = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('second', [ $value ]);
        $this->bindMainProperty('message', [ $message ]);
    }
}