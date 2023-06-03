<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);
namespace Jiumi\Aspect;

use App\System\Service\SystemMenuService;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Jiumi\Annotation\OperationLog;
use Jiumi\Annotation\Permission;
use Jiumi\Event\Operation;
use Jiumi\Helper\Ip2region;
use Jiumi\Helper\LoginUser;
use Jiumi\Helper\Str;
use Jiumi\JiumiRequest;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class OperationLogAspect
 * @package Jiumi\Aspect
 */
#[Aspect]
class OperationLogAspect extends AbstractAspect
{
    public array $annotations = [
        OperationLog::class
    ];

    /**
     * 容器
     */
    protected ContainerInterface $container;

    protected Ip2region $ip2region;

    public function __construct()
    {
        $this->container = container();
        $this->ip2region = $this->container->get(Ip2region::class);
    }

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed|void
     * @throws \Hyperf\Di\Exception\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[OperationLog::class];
        /* @var $result ResponseInterface */
        $result = $proceedingJoinPoint->process();
        $isDownload = false;
        if (! empty($annotation->menuName) || ($annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[Permission::class])) {
            if (!empty($result->getHeader('content-description')) && !empty($result->getHeader('content-transfer-encoding'))) {
                $isDownload = true;
            }
            $evDispatcher = $this->container->get(EventDispatcherInterface::class);
            $evDispatcher->dispatch(new Operation($this->getRequestInfo([
                'code' => !empty($annotation->code) ? explode(',', $annotation->code)[0] : '',
                'name' => $annotation->menuName ?? '',
                'response_code' => $result->getStatusCode(),
                'response_data' => $isDownload ? '文件下载' : $result->getBody()->getContents()
            ])));
        }
        return $result;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getRequestInfo(array $data): array
    {
        $request = $this->container->get(JiumiRequest::class);
        $loginUser = $this->container->get(LoginUser::class);

        $operationLog = [
            'time' => date('Y-m-d H:i:s', $request->getServerParams()['request_time']),
            'method' => $request->getServerParams()['request_method'],
            'router' => $request->getServerParams()['path_info'],
            'protocol' => $request->getServerParams()['server_protocol'],
            'ip' => $request->ip(),
            'ip_location' => $this->ip2region->search($request->ip()),
            'service_name' => $data['name'] ?: $this->getOperationMenuName($data['code']),
            'request_data' => $request->all(),
            'response_code' => $data['response_code'],
            'response_data' => $data['response_data'],
        ];
        try {
            $operationLog['username'] = $loginUser->getUsername();
        } catch (\Exception $e) {
            $operationLog['username'] = t('system.no_login_user');
        }

        return $operationLog;
    }

    /**
     * 获取菜单名称
     * @param string $code
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getOperationMenuName(string $code): string
    {
        return $this->container->get(SystemMenuService::class)->findNameByCode($code);
    }
}