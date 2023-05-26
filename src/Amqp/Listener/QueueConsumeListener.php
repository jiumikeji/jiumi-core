<?php
/**
 * Description:队列消费监听器
 * User: LBG
 * Date: 2021/9/30
 * Time: 3:13 下午
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */
declare(strict_types=1);
namespace Jiumi\Amqp\Listener;

use App\System\Model\SystemQueueLog;
use App\System\Service\SystemQueueLogService;
use Jiumi\Amqp\Event\AfterConsume;
use Jiumi\Amqp\Event\BeforeConsume;
use Jiumi\Amqp\Event\ConsumeEvent;
use Jiumi\Amqp\Event\FailToConsume;
use Jiumi\Amqp\Event\WaitTimeout;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\Annotation\Listener;

/**
 * 消费队列监听
 * Class QueueConsumeListener
 * @package Jiumi\Amqp\Listener
 */
#[Listener]
class QueueConsumeListener implements ListenerInterface
{
    private SystemQueueLogService $service;

    public function listen(): array
    {
        // 返回一个该监听器要监听的事件数组，可以同时监听多个事件
        return [
            AfterConsume::class,
            BeforeConsume::class,
            ConsumeEvent::class,
            FailToConsume::class,
            WaitTimeout::class,
        ];
    }

    /**
     * @param object $event
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(object $event): void
    {
        $this->service = container()->get(SystemQueueLogService::class);
        if ($event->message) {
            $class = get_class($event);
            $func = lcfirst(trim(strrchr($class, '\\'),'\\'));
            $this->$func($event);
        }
    }

    /**
     * Description:消费前
     * User:mike
     * @param object $event
     */
    public function beforeConsume(object $event)
    {
        $this->service->update(
            (int)$event->data['queue_id'],
            ['consume_status' => SystemQueueLog::CONSUME_STATUS_DOING]
        );
    }

    /**
     * Description:消费中
     * User:mike
     * @param object $event
     */
    public function consumeEvent(object $event)
    {
        // TODO...
    }

    /**
     * Description:消费后
     * User:mike
     * @param object $event
     */
    public function afterConsume(object $event)
    {
        $this->service->update(
            (int)$event->data['queue_id'],
            ['consume_status' => SystemQueueLog::CONSUME_STATUS_SUCCESS]
        );
    }

    /**
     * Description:消费失败
     * User:mike
     * @param object $event
     */
    public function failToConsume(object $event)
    {
        $this->service->update(
            (int)$event->data['queue_id'], [
            'consume_status' => SystemQueueLog::CONSUME_STATUS_REPEAT,
            'log_content' => $event->throwable ?: $event->throwable->getMessage()
        ]);
    }
}
