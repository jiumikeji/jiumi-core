<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types = 1);
namespace Jiumi\Event;

class UserAdd
{
    public array $userinfo;

    public function __construct(array $userinfo)
    {
        $this->userinfo = $userinfo;
    }
}