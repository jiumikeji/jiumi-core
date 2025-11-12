<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

use App\System\Vo\QueueMessageVo;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Jiumi\Helper\LoginUser;
use Jiumi\Helper\AppVerify;
use Jiumi\Helper\Id;
use Jiumi\Interfaces\ServiceInterface\QueueLogServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Hyperf\DbConnection\Db;
use App\System\Model\SystemDept;
use App\System\Model\SystemRole;
use App\System\Model\SystemUser;

if (!function_exists('container')) {

    /**
     * 获取容器实例
     * @return \Psr\Container\ContainerInterface
     */
    function container(): \Psr\Container\ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

}

if (!function_exists('redis')) {

    /**
     * 获取Redis实例
     * @return \Hyperf\Redis\Redis
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function redis(): \Hyperf\Redis\Redis
    {
        return container()->get(\Hyperf\Redis\Redis::class);
    }

}

if (!function_exists('console')) {

    /**
     * 获取控制台输出实例
     * @return StdoutLoggerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function console(): StdoutLoggerInterface
    {
        return container()->get(StdoutLoggerInterface::class);
    }

}

if (!function_exists('logger')) {

    /**
     * 获取日志实例
     * @param string $name
     * @return LoggerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function logger(string $name = 'Log'): LoggerInterface
    {
        return container()->get(LoggerFactory::class)->get($name);
    }

}

if (!function_exists('user')) {
    /**
     * 获取当前登录用户实例
     * @param string $scene
     * @return LoginUser
     */
    function user(string $scene = 'default'): LoginUser
    {
        return new LoginUser($scene);
    }
}

if (!function_exists('format_size')) {
    /**
     * 格式化大小
     * @param int $size
     * @return string
     */
    function format_size(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $index = 0;
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
            $index = $i;
        }
        return round($size, 2) . $units[$index];
    }
}

if (!function_exists('lang')) {
    /**
     * 获取当前语言
     * @param string $key
     * @param array $replace
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function lang(): string
    {
        $acceptLanguage = container()->get(\Jiumi\JiumiRequest::class)->getHeaderLine('accept-language');
        return str_replace('-', '_', !empty($acceptLanguage) ? explode(',', $acceptLanguage)[0] : 'zh_CN');
    }
}

if (!function_exists('t')) {
    /**
     * 多语言函数
     * @param string $key
     * @param array $replace
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function t(string $key, array $replace = []): string
    {
        return __($key, $replace, lang());
    }
}

if (!function_exists('jiumi_collect')) {
    /**
     * 创建一个Jiumi的集合类
     * @param null|mixed $value
     * @return \Jiumi\JiumiCollection
     */
    function jiumi_collect($value = null): \Jiumi\JiumiCollection
    {
        return new \Jiumi\JiumiCollection($value);
    }
}

if (!function_exists('context_set')) {
    /**
     * 设置上下文数据
     * @param string $key
     * @param $data
     * @return bool
     */
    function context_set(string $key, $data): bool
    {
        return (bool)\Hyperf\Context\Context::set($key, $data);
    }
}

if (!function_exists('context_get')) {
    /**
     * 获取上下文数据
     * @param string $key
     * @return mixed
     */
    function context_get(string $key)
    {
        return \Hyperf\Context\Context::get($key);
    }
}

if (!function_exists('app_verify')) {
    /**
     * 获取APP应用请求实例
     * @param string $scene
     * @return AppVerify
     */
    function app_verify(string $scene = 'api'): AppVerify
    {
        return new AppVerify($scene);
    }
}

if (!function_exists('snowflake_id')) {
    /**
     * 生成雪花ID
     * @param int|null $workerId
     * @return String
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function snowflake_id(?int $workerId = null): string
    {
        return container()->get(Id::class)->getId($workerId);
    }
}

if (!function_exists('event')) {
    /**
     * 事件调度快捷方法
     * @param object $dispatch
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function event(object $dispatch): object
    {
        return container()->get(EventDispatcherInterface::class)->dispatch($dispatch);
    }
}

if (!function_exists('push_queue_message')) {
    /**
     * 推送消息到队列
     * @param QueueMessageVo $message
     * @param array $receiveUsers
     * @return bool
     * @throws Throwable
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function push_queue_message(QueueMessageVo $message, array $receiveUsers = []): bool
    {
        return container()
            ->get(QueueLogServiceInterface::class)
            ->pushMessage($message, $receiveUsers);
    }
}

if (!function_exists('add_queue')) {
    /**
     * 添加任务到队列
     * @param \App\System\Vo\AmqpQueueVo $amqpQueueVo
     * @return bool
     * @throws Throwable
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function add_queue(\App\System\Vo\AmqpQueueVo $amqpQueueVo): bool
    {
        return container()
            ->get(QueueLogServiceInterface::class)
            ->addQueue($amqpQueueVo);
    }
}
if (!function_exists('get_user_perm_info')) {
    /**
     * 获取用户的数据权限-用户ID集合
     * @param int $userId 用户ID
     * @param string|array $permIds 权限编码 xx:xx
     * @param string $where 查询条件 OR / AND
     * @param bool|null $scope 是否权限控制
     * @return array
     */
    function get_user_perm_info(int $userId, string|array $permIds, string $where = 'OR', bool|null $scope = null): array
    {
        $result = [
            'admin' => false,
            'deptIds' => [],
            'isHasSelf' => false,
            'query' => null,
        ];
        if (is_null($scope)) {
            $scope = Context::get("sys_perm_scope", true);
        }
        if (!config('jiumiadmin.data_scope_enabled') || !$scope) {
            $result['admin'] = true;
            return $result;
        }

        if (env('SUPER_ADMIN') == $userId) {
            $result['admin'] = true;
            return $result;
        }
        $user = user_info($userId);
        if (!$user) {
            return $result;
        }
        if (in_array('jiumiAdmin', $user['roles'])) {
            $result['admin'] = true;
            return $result;
        }
        $mainDeptId = $user['user']['dept_id'];
        //获取菜单权限对应的角色ID
        if (!is_array($permIds)) {
            $permIdArr = array_map('trim', explode(",", $permIds));
            if (count($permIdArr) > 1) {
                $permIds = $permIdArr;
            }
        }
        $query = Db::table('system_role' . ' as r')
            ->leftJoin('system_user_role as ru', 'r.id', '=', 'ru.role_id')
            ->leftJoin('system_role_menu as rm', 'r.id', '=', 'rm.role_id')
            ->leftJoin('system_menu as m', 'm.id', '=', 'rm.menu_id')
            ->where('ru.user_id', $userId)
            ->whereNull('r.deleted_at')
            ->where('r.status', 1);
        if (!empty($permIds)) {
            if (count($permIds) > 1) {
                if (strtoupper($where) == 'OR') {
                    $query = $query->whereIn('m.code', $permIds);
                } else {
                    foreach ($permIds as $permId) {
                        $query = $query->where('m.code', $permId);
                    }
                }
            } else {
                $query = $query->where('m.code', $permIds[0]);
            }
        }
        $roles = $query->select(['r.id', 'r.data_scope'])->get();
        $roles = json_decode(json_encode($roles), true);
        if (empty($roles)) {
            return $result;
        }
        $isHasSelf = false;
        $deptIds = [];
        $customDeptIds = [];
        $childrenDeptIds = [];
        $roleCache = [];
        foreach ($roles as $role) {
            if (isset($roleCache[$role['id']])) continue;
            $roleCache[$role['id']] = true;
            if ($role['data_scope'] == SystemRole::ALL_SCOPE) {
                //全部权限
                $result['admin'] = true;
                return $result;
            }
            if ($role['data_scope'] == SystemRole::CUSTOM_SCOPE) {
                //自定义
                if (!isset($customDeptIds[$role['id']])) {
                    $customDeptIds[$role['id']] = true;
                    $ids = Db::table('system_role_dept')->where('role_id', $role['id'])
                        ->pluck('dept_id')->toArray();
                    foreach ($ids as $id) {
                        $deptIds[$id] = $id;
                    }
                }
                continue;
            }
            if ($role['data_scope'] == SystemRole::SELF_DEPT_SCOPE) {
                //本部门
                $deptIds[$mainDeptId] = $mainDeptId;
                $ids = $user['dept_ids'];
                foreach ($ids as $id) {
                    $deptIds[$id] = $id;
                }
                continue;
            }
            if ($role['data_scope'] == SystemRole::DEPT_BELOW_SCOPE) {
                //本部门及以下
                $ids = $user['dept_ids'];
                $allIds = [$mainDeptId => $mainDeptId];
                foreach ($ids as $id) {
                    $allIds[$id] = $id;
                }
                $allIds = array_values($allIds);
                $key = implode(',', $allIds);
                if (!isset($childrenDeptIds[$key])) {
                    $childrenDeptIds[$key] = true;
                    //获取当前和子部门所有
                    $ids = Db::table('system_dept')->where('status', 1)->whereNull('deleted_at')
                        ->where(function ($query) use ($allIds) {
                            $query->whereIn('id', $allIds);
                            foreach ($allIds as $id) {
                                $query->orWhereRaw('FIND_IN_SET(' . $id . ',level)');
                            }
                        })->pluck('id')->toArray();
                    foreach ($ids as $id) {
                        $deptIds[$id] = $id;
                    }
                }
                continue;
            }
            if ($role['data_scope'] == SystemRole::SELF_SCOPE) {
                $isHasSelf = true;
            }
        }
        $result['isHasSelf'] = $isHasSelf;
        $deptIds = array_values($deptIds);
        $query = Db::table('system_user as u')
            ->leftJoin('system_user_dept as ud', 'u.id', '=', 'ud.user_id')
            ->select(['u.id']);
        if ($isHasSelf) {
            $query = $query->where('u.id', $userId);
            if (!empty($deptIds)) {
                $query = $query->orWhereIn('ud.dept_id', $deptIds)->orWhereIn('u.dept_id', $deptIds);
            }
        } else if (!empty($deptIds)) {
            $query = $query->whereIn('ud.dept_id', $deptIds)->orWhereIn('u.dept_id', $deptIds);
        }
        $result['deptIds'] = $deptIds;
        $result['query'] = $query;
        return $result;
    }
}
if (!function_exists('get_context_perm_info')) {
    /**
     * 获取上下文数据权限-用户ID集合
     * @param int $userId 用户ID
     * @return array
     */
    function get_context_perm_info($userId): array
    {
        $permWhere = Context::get('sys_perm_where', 'OR');
        $permCodes = Context::get('sys_perm_codes', []);
        $permInfo = Context::get("sys_perm_cache_" . $userId);
        if (!$permInfo) {
            $permInfo = get_user_perm_info($userId, $permCodes, $permWhere);
            Context::set("sys_perm_cache", $permInfo);
        }
        return $permInfo;
    }
}