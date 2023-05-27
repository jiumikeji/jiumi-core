<?php

declare (strict_types=1);
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */
namespace Jiumi\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Arr;
use Jiumi\Helper\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var StdoutLoggerInterface
     */
    protected StdoutLoggerInterface $console;
    
    public function __construct(StdoutLoggerInterface $console, ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql', 'sql');
        $this->console = $console;
    }
    
    public function listen() : array
    {
        return [QueryExecuted::class];
    }
    /**
     * @param QueryExecuted $event
     */
    public function process(object $event): void
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            $offset = 0;
            if (!Arr::isAssoc($event->bindings)) {
                foreach ($event->bindings as $value) {
                    $value = is_array($value) ? json_encode($value) : "'{$value}'";
                    $sql = Str::replaceFirst('?', "{$value}", $sql, $offset);
                }
            }
            if (env('CONSOLE_SQL')) {
                $this->console->info(sprintf('SQL[%s ms] %s ', $event->time, $sql));
                $this->logger->info(sprintf('[%s] %s', $event->time, $sql));
            }
        }
    }
}