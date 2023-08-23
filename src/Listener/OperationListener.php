<?php

/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */
declare (strict_types=1);
namespace Jiumi\Listener;

use Jiumi\Interfaces\ServiceInterface\OperLogServiceInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Jiumi\Event\Operation;
use Psr\Container\ContainerInterface;
/**
 * Class OperationListener
 * @package Jiumi\Listener
 */
#[Listener]
class OperationListener implements ListenerInterface
{
    
    protected $container;
    
    protected $ignoreRouter = ['/login', '/getInfo', '/system/captcha'];
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen() : array
    {
        return [Operation::class];
    }
    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     * @param object $event
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(object $event): void
    {
        $requestInfo = $event->getRequestInfo();
        if (!in_array($requestInfo['router'], $this->ignoreRouter)) {
            $service = $this->container->get(OperLogServiceInterface::class);
            $requestInfo['request_data'] = json_encode($requestInfo['request_data'], JSON_UNESCAPED_UNICODE);
            //            $requestInfo['response_data'] = $requestInfo['response_data'];
            $service->save($requestInfo);
        }
    }
}