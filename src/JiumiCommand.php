<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi;

use Hyperf\Command\Command as HyperfCommand;

/**
 * Class JiumiCommand
 * @package System
 */
abstract class JiumiCommand extends HyperfCommand
{
    protected string $module;

    protected CONST CONSOLE_GREEN_BEGIN = "\033[32;5;1m";
    protected CONST CONSOLE_RED_BEGIN = "\033[31;5;1m";
    protected CONST CONSOLE_END = "\033[0m";

    protected function getGreenText($text): string
    {
        return self::CONSOLE_GREEN_BEGIN . $text . self::CONSOLE_END;
    }

    protected function getRedText($text): string
    {
        return self::CONSOLE_RED_BEGIN . $text . self::CONSOLE_END;
    }

    protected function getStub($filename): string
    {
        return BASE_PATH . '/jiumi/Command/Creater/Stubs/' . $filename . '.stub';
    }

    protected function getModulePath(): string
    {
        return BASE_PATH . '/app/' . $this->module . '/Request/';
    }

    protected function getInfo(): string
    {
        return sprintf('
/---------------------------- 九米科技欢迎您 ------------------------------\
|     _____        ______        __    __        __       __        ______  |
|    /     |      /      |      /  |  /  |      /  \     /  |      /      | |
|    $$$$$ |      $$$$$$/       $$ |  $$ |      $$  \   /$$ |      $$$$$$/  |
|       $$ |        $$ |        $$ |  $$ |      $$$  \ /$$$ |        $$ |   |
|  __   $$ |        $$ |        $$ |  $$ |      $$$$  /$$$$ |        $$ |   |
| /  |  $$ |        $$ |        $$ |  $$ |      $$ $$ $$/$$ |        $$ |   |
| $$ \__$$ |       _$$ |_       $$ \__$$ |      $$ |$$$/ $$ |       _$$ |_  |
| $$    $$/       / $$   |      $$    $$/       $$ | $/  $$ |      / $$   | |
|  $$$$$$/        $$$$$$/        $$$$$$/        $$/      $$/       $$$$$$/  |
|                                                                           |
\_______________________  Copyright 九米科技 2021 ~ %s   _________________|
', date('Y'));
    }
}
