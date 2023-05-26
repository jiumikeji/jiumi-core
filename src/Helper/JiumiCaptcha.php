<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */


declare(strict_types=1);
namespace Jiumi\Helper;

class JiumiCaptcha
{
    /**
     * @return array
     */
    public function getCaptchaInfo(): array
    {
        $conf = new \EasySwoole\VerifyCode\Config();
        $conf->setUseCurve()->setUseNoise();
        $validCode = new \EasySwoole\VerifyCode\VerifyCode($conf);
        $draw = $validCode->DrawCode();
        return ['code' => \Jiumi\Helper\Str::lower($draw->getImageCode()), 'image' => $draw->getImageByte()];
    }
}
