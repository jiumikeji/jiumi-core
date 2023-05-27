<?php

declare(strict_types=1);
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */
namespace Jiumi\Amqp\Event;

use Hyperf\Amqp\Message\ConsumerMessageInterface;

class BeforeConsume
{

    /**
     * @var ConsumerMessageInterface
     */
    public $message;
    public $data;

    public function __construct($message, $data)
    {
        $this->message = $message;
        $this->data = $data;
    }
}
