<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Aspect;

use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Jiumi\Annotation\Transaction;
use Jiumi\Exception\JiumiException;

/**
 * Class TransactionAspect
 * @package Jiumi\Aspect
 */
#[Aspect]
class TransactionAspect extends AbstractAspect
{
    public array $annotations = [
        Transaction::class
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var Transaction $transaction */
        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Transaction::class])) {
            $transaction = $proceedingJoinPoint->getAnnotationMetadata()->method[Transaction::class];
        }
        try {
            Db::beginTransaction();
            $number = 0;
            $retry  = intval($transaction->retry);
            do {
                $result = $proceedingJoinPoint->process();
                if (! is_null($result)) {
                    break;
                }
                ++$number;
            } while ($number < $retry);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw new JiumiException($e->getMessage(), 500);
        }
        return $result;
    }
}