<?php
/**
 * Description:消费切面
 * User: LBG
 * Date: 2021/11/19
 * Time: 下午2:14
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */
declare(strict_types=1);
namespace Jiumi\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Jiumi\Amqp\Event\AfterConsume;
use Jiumi\Amqp\Event\BeforeConsume;
use Jiumi\Amqp\Event\FailToConsume;

/**
 * Class ConsumeAspect
 * @package Jiumi\Aspect
 */
#[Aspect]
class ConsumeAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Amqp\Message\ConsumerMessage::consumeMessage'
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $data = $proceedingJoinPoint->getArguments()[0];
        $message = $proceedingJoinPoint->getArguments()[1];
        try{
            event(new BeforeConsume($message, $data));
            $result = $proceedingJoinPoint->process();
            event(new AfterConsume($message, $data, $result));
            return $result;
        } catch (\Throwable $e) {
            event(new FailToConsume($message, $data, $e));
            return null;
        }
    }
}
