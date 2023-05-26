<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi;

use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Jiumi\Traits\ModelMacroTrait;

/**
 * Class JiumiModel
 * @package Jiumi
 */
class JiumiModel extends Model
{
    use Cacheable, ModelMacroTrait;

    /**
     * 隐藏的字段列表
     * @var string[]
     */
    protected array $hidden = ['deleted_at'];

    /**
     * 状态
     */
    public const ENABLE = 1;
    public const DISABLE = 2;

    /**
     * 默认每页记录数
     */
    public const PAGE_SIZE = 15;

    /**
     * JiumiModel constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        //注册常用方法
        $this->registerBase();
        //注册用户数据权限方法
        $this->registerUserDataScope();
    }

    /**
     * 设置主键的值
     * @param string | int $value
     */
    public function setPrimaryKeyValue($value): void
    {
        $this->{$this->primaryKey} = $value;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyType(): string
    {
        return $this->keyType;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        return parent::save($options);
    }

    /**
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        return parent::update($attributes, $options);
    }

    /**
     * @param array $models
     * @return JiumiCollection
     */
    public function newCollection(array $models = []): JiumiCollection
    {
        return new JiumiCollection($models);
    }
}
