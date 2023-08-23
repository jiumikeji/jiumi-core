<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Aspect;

use Jiumi\Interfaces\ServiceInterface\UserServiceInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Jiumi\Annotation\Permission;
use Jiumi\Exception\NoPermissionException;
use Jiumi\Helper\LoginUser;
use Jiumi\JiumiRequest;

/**
 * Class PermissionAspect
 * @package Jiumi\Aspect
 */
#[Aspect]
class PermissionAspect extends AbstractAspect
{
    public array $annotations = [
        Permission::class
    ];

    /**
     * UserServiceInterface
     */
    protected UserServiceInterface $service;

    /**
     * JiumiRequest
     */
    protected JiumiRequest $request;

    /**
     * LoginUser
     */
    protected LoginUser $loginUser;

    /**
     * PermissionAspect constructor.
     * @param UserServiceInterface $service
     * @param JiumiRequest $request
     * @param LoginUser $loginUser
     */
    public function __construct(
        UserServiceInterface $service,
        JiumiRequest $request,
        LoginUser $loginUser
    )
    {
        $this->service = $service;
        $this->request = $request;
        $this->loginUser = $loginUser;
    }

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->loginUser->isSuperAdmin()) {
            return $proceedingJoinPoint->process();
        }

        /** @var Permission $permission */
        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Permission::class])) {
            $permission = $proceedingJoinPoint->getAnnotationMetadata()->method[Permission::class];
        }

        // 注解权限为空，则放行
        if (empty($permission->code)) {
            return $proceedingJoinPoint->process();
        }

        $this->checkPermission($permission->code, $permission->where);

        return $proceedingJoinPoint->process();
    }

    /**
     * 检查权限
     * @param string $codeString
     * @param string $where
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function checkPermission(string $codeString, string $where): bool
    {
        $codes = $this->service->getInfo()['codes'];

        if ($where === 'OR') {
            foreach (explode(',', $codeString) as $code) {
                if (in_array(trim($code), $codes)) {
                    return true;
                }
            }
            throw new NoPermissionException(
                t('system.no_permission') . ' -> [ ' . $this->request->getPathInfo() . ' ]'
            );
        }

        if ($where === 'AND') {
            foreach (explode(',', $codeString) as $code) {
                $code = trim($code);
                if (! in_array($code, $codes)) {
                    $service = container()->get(\Jiumi\Interfaces\ServiceInterface\MenuServiceInterface::class);
                    throw new NoPermissionException(
                        t('system.no_permission') . ' -> [ ' . $service->findNameByCode($code) . ' ]'
                    );
                }
            }
        }

        return true;
    }
}