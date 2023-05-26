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
 * 用户角色验证。
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Role extends AbstractAnnotation {

    /**
     * 角色代码标识
     * @var string
     */
    public string $code;

    /**
     * 多个角色代码，过滤条件
     * 为 OR 时，检查有一个通过则全部通过
     * 为 AND 时，检查有一个不通过则全不通过
     * @var string
     */
    public string $where;

    public function __construct($value = null, $where = 'OR')
    {
        parent::__construct($value);
        $this->bindMainProperty('code', [ $value ]);
        $this->bindMainProperty('where', [ $where ]);
    }


}