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
 * excel导入导出元数据。
 * @Annotation
 * @Target("PROPERTY")
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcelProperty extends AbstractAnnotation
{
    /**
     * 列表头名称
     * @var string
     */
    public string $value;

    /**
     * 列顺序
     * @var int
     */
    public int $index;

    /**
     * 宽度
     * @var int
     */
    public int $width;

    /**
     * 对齐方式，默认居左
     * @var string
     */
    public string $align;

    /**
     * 列表头字体颜色
     * @var string
     */
    public string $headColor;

    /**
     * 列表头背景颜色
     * @var string
     */
    public string $headBgColor;

    /**
     * 列表体字体颜色
     * @var string
     */
    public string $color;

    /**
     * 列表体背景颜色
     * @var string
     */
    public string $bgColor;

    /**
     * 字典数据列表
     */
    public ?array $dictData = null;

    /**
     * 字典名称
     * @var string
     */
    public string $dictName;
    /**
     * 数据路径 用法: object.value
     * @var string
     */
    public string $path;
}