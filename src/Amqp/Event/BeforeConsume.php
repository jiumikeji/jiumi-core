<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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
