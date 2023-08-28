<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

namespace Jiumi\Interfaces;

interface JiumiRedisInterface
{
    /**
     * 设置 key 类型名
     * @param string $typeName
     */
    public function setTypeName(string $typeName): void;

    /**
     * 获取key 类型名
     * @return string
     */
    public function getTypeName(): string;
}