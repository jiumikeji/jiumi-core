<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Jiumi\Annotation\Resubmit;
use Jiumi\Exception\NormalStatusException;
use Jiumi\JiumiRequest;
use Jiumi\Redis\JiumiLockRedis;

/**
 * Class ResubmitAspect
 * @package Jiumi\Aspect
 */
#[Aspect]
class ResubmitAspect extends AbstractAspect
{

    public array $annotations = [
        Resubmit::class
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var $resubmit Resubmit*/
        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Resubmit::class])) {
            $resubmit = $proceedingJoinPoint->getAnnotationMetadata()->method[Resubmit::class];
        }

        $request = container()->get(JiumiRequest::class);

        $key = md5(sprintf('%s-%s-%s', $request->ip(), $request->getPathInfo(), $request->getMethod()));

        $lockRedis = new JiumiLockRedis();
        $lockRedis->setTypeName('resubmit');

        if ($lockRedis->check($key)) {
            $lockRedis = null;
            throw new NormalStatusException($resubmit->message ?: t('jiumiadmin.resubmit'), 500);
        }

        $lockRedis->lock($key, $resubmit->second);
        $lockRedis = null;

        return $proceedingJoinPoint->process();
    }
}