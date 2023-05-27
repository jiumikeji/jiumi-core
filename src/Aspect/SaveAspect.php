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
use Jiumi\JiumiModel;
use Jiumi\JiumiRequest;

/**
 * Class SaveAspect
 * @package Jiumi\Aspect
 */
#[Aspect]
class SaveAspect extends AbstractAspect
{
    public array $classes = [
        'Jiumi\JiumiModel::save'
    ];

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $instance = $proceedingJoinPoint->getInstance();

        if (config('jiumiadmin.data_scope_enabled')) {
            try {
                $user = user();
                // 设置创建人
                if ($instance instanceof JiumiModel &&
                    in_array('created_by', $instance->getFillable()) &&
                    is_null($instance->created_by)
                ) {
                    $user->check();
                    $instance->created_by = $user->getId();
                }

                // 设置更新人
                if ($instance instanceof JiumiModel && in_array('updated_by', $instance->getFillable())) {
                    $user->check();
                    $instance->updated_by = $user->getId();
                }

            } catch (\Throwable $e) {}
        }
        // 生成ID
        if ($instance instanceof JiumiModel &&
            !$instance->incrementing &&
            $instance->getPrimaryKeyType() === 'int' &&
            empty($instance->{$instance->getKeyName()})
        ) {
            $instance->setPrimaryKeyValue(snowflake_id());
        }
        return $proceedingJoinPoint->process();
    }
}