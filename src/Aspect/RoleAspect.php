<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Aspect;

use App\System\Service\SystemUserService;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Jiumi\Annotation\Role;
use Jiumi\Exception\NoPermissionException;
use Jiumi\Helper\LoginUser;
use Jiumi\JiumiRequest;

/**
 * Class RoleAspect
 * @package Jiumi\Aspect
 */
#[Aspect]
class RoleAspect extends AbstractAspect
{
    public array $annotations = [
        Role::class
    ];

    /**
     * SystemUserService
     */
    protected SystemUserService $service;

    /**
     * JiumiRequest
     */
    protected JiumiRequest $request;

    /**
     * LoginUser
     */
    protected LoginUser $loginUser;

    /**
     * RoleAspect constructor.
     * @param SystemUserService $service
     * @param JiumiRequest $request
     * @param LoginUser $loginUser
     */
    public function __construct(
        SystemUserService $service,
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
        // 是超管角色放行
        if ($this->loginUser->isAdminRole()) {
            return $proceedingJoinPoint->process();
        }

        /** @var Role $role */
        if (isset($proceedingJoinPoint->getAnnotationMetadata()->method[Role::class])) {
            $role = $proceedingJoinPoint->getAnnotationMetadata()->method[Role::class];
        }

        // 没有使用注解，则放行
        if (empty($role->code)) {
            return $proceedingJoinPoint->process();
        }

        $this->checkRole($role->code, $role->where);

        return $proceedingJoinPoint->process();
    }

    /**
     * 检查角色
     * @param string $codeString
     * @param string $where
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function checkRole(string $codeString, string $where): bool
    {
        $roles = $this->service->getInfo()['roles'];

        if ($where === 'OR') {
            foreach (explode(',', $codeString) as $code) {
                if (in_array(trim($code), $roles)) {
                    return true;
                }
            }
            throw new NoPermissionException(
                t('system.no_role') . ' -> [ ' . $codeString . ' ]'
            );
        }

        if ($where === 'AND') {
            foreach (explode(',', $codeString) as $code) {
                $code = trim($code);
                if (! in_array($code, $roles)) {
                    $service = container()->get(\App\System\Service\SystemRoleService::class);
                    throw new NoPermissionException(
                        t('system.no_role') . ' -> [ ' . $service->findNameByCode($code) . ' ]'
                    );
                }
            }
        }

        return true;
    }
}