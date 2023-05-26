<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

namespace Jiumi\Traits;

use Hyperf\Contract\LengthAwarePaginatorInterface;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Jiumi\Annotation\Transaction;
use Jiumi\Exception\NormalStatusException;
use Jiumi\JiumiCollection;
use Jiumi\JiumiModel;
use Hyperf\ModelCache\Manager;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait MapperTrait
{
    /**
     * @var JiumiModel
     */
    public $model;

    /**
     * 获取列表数据
     * @param array|null $params
     * @param bool $isScope
     * @return array
     */
    public function getList(?array $params, bool $isScope = true): array
    {
        return $this->listQuerySetting($params, $isScope)->get()->toArray();
    }

    /**
     * 获取列表数据（带分页）
     * @param array|null $params
     * @param bool $isScope
     * @param string $pageName
     * @return array
     */
    public function getPageList(?array $params, bool $isScope = true, string $pageName = 'page'): array
    {
        $paginate = $this->listQuerySetting($params, $isScope)->paginate(
            $params['pageSize'] ?? $this->model::PAGE_SIZE, ['*'], $pageName, $params[$pageName] ?? 1
        );
        return $this->setPaginate($paginate);
    }

    /**
     * 设置数据库分页
     * @param LengthAwarePaginatorInterface $paginate
     * @return array
     */
    public function setPaginate(LengthAwarePaginatorInterface $paginate): array
    {
        return [
            'items' => method_exists($this, 'handlePageItems') ? $this->handlePageItems($paginate->items()) : $paginate->items(),
            'pageInfo' => [
                'total' => $paginate->total(),
                'currentPage' => $paginate->currentPage(),
                'totalPage' => $paginate->lastPage()
            ]
        ];
    }

    /**
     * 获取树列表
     * @param array|null $params
     * @param bool $isScope
     * @param string $id
     * @param string $parentField
     * @param string $children
     * @return array
     */
    public function getTreeList(
        ?array $params = null,
        bool $isScope = true,
        string $id = 'id',
        string $parentField = 'parent_id',
        string $children='children'
    ): array
    {
        $params['_Jiumiadmin_tree'] = true;
        $params['_Jiumiadmin_tree_pid'] = $parentField;
        $data = $this->listQuerySetting($params, $isScope)->get();
        return $data->toTree([], $data[0]->{$parentField} ?? 0, $id, $parentField, $children);
    }

    /**
     * 返回模型查询构造器
     * @param array|null $params
     * @param bool $isScope
     * @return Builder
     */
    public function listQuerySetting(?array $params, bool $isScope): Builder
    {
        $query = (($params['recycle'] ?? false) === true) ? $this->model::onlyTrashed() : $this->model::query();

        if ($params['select'] ?? false) {
            $query->select($this->filterQueryAttributes($params['select']));
        }

        $query = $this->handleOrder($query, $params);

        $isScope && $query->userDataScope();

        return $this->handleSearch($query, $params);
    }

    /**
     * 排序处理器
     * @param Builder $query
     * @param array|null $params
     * @return Builder
     */
    public function handleOrder(Builder $query, ?array &$params = null): Builder
    {
        // 对树型数据强行加个排序
        if (isset($params['_Jiumiadmin_tree'])) {
            $query->orderBy($params['_Jiumiadmin_tree_pid']);
        }

        if ($params['orderBy'] ?? false) {
            if (is_array($params['orderBy'])) {
                foreach ($params['orderBy'] as $key => $order) {
                    $query->orderBy($order, $params['orderType'][$key] ?? 'asc');
                }
            } else {
                $query->orderBy($params['orderBy'], $params['orderType'] ?? 'asc');
            }
        }

        return $query;
    }

    /**
     * 搜索处理器
     * @param Builder $query
     * @param array $params
     * @return Builder
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query;
    }

    /**
     * 过滤查询字段不存在的属性
     * @param array $fields
     * @param bool $removePk
     * @return array
     */
    protected function filterQueryAttributes(array $fields, bool $removePk = false): array
    {
        $model = new $this->model;
        $attrs = $model->getFillable();
        foreach ($fields as $key => $field) {
            if (!in_array(trim($field), $attrs) && mb_strpos(str_replace('AS', 'as', $field), 'as') === false) {
                unset($fields[$key]);
            } else {
                $fields[$key] = trim($field);
            }
        }
        if ($removePk && in_array($model->getKeyName(), $fields)) {
            unset($fields[array_search($model->getKeyName(), $fields)]);
        }
        $model = null;
        return ( count($fields) < 1 ) ? ['*'] : $fields;
    }

    /**
     * 过滤新增或写入不存在的字段
     * @param array $data
     * @param bool $removePk
     */
    protected function filterExecuteAttributes(array &$data, bool $removePk = false): void
    {
        $model = new $this->model;
        $attrs = $model->getFillable();
        foreach ($data as $name => $val) {
            if (!in_array($name, $attrs)) {
                unset($data[$name]);
            }
        }
        if ($removePk && isset($data[$model->getKeyName()])) {
            unset($data[$model->getKeyName()]);
        }
        $model = null;
    }

    /**
     * 新增数据
     * @param array $data
     * @return int
     */
    public function save(array $data): int
    {
        $this->filterExecuteAttributes($data, $this->getModel()->incrementing);
        $model = $this->model::create($data);
        return $model->{$model->getKeyName()};
    }

    /**
     * 读取一条数据
     * @param int $id
     * @return JiumiModel|null
     */
    public function read(int $id): ?JiumiModel
    {
        return ($model = $this->model::find($id)) ? $model : null;
    }

    /**
     * 按条件读取一行数据
     * @param array $condition
     * @param array $column
     * @return mixed
     */
    public function first(array $condition, array $column = ['*']): ?JiumiModel
    {
        return ($model = $this->model::where($condition)->first($column)) ? $model : null;
    }

    /**
     * 获取单个值
     * @param array $condition
     * @param string $columns
     * @return \Hyperf\Utils\HigherOrderTapProxy|mixed|void|null
     */
    public function value(array $condition, string $columns = 'id')
    {
        return ($model = $this->model::where($condition)->value($columns)) ? $model : null;
    }

    /**
     * 获取单列值
     * @param array $condition
     * @param string $columns
     * @return array
     */
    public function pluck(array $condition, string $columns = 'id'): array
    {
        return $this->model::where($condition)->pluck($columns)->toArray();
    }

    /**
     * 从回收站读取一条数据
     * @param int $id
     * @return JiumiModel|null
     * @noinspection PhpUnused
     */
    public function readByRecycle(int $id): ?JiumiModel
    {
        return ($model = $this->model::withTrashed()->find($id)) ? $model : null;
    }

    /**
     * 单个或批量软删除数据
     * @param array $ids
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function delete(array $ids): bool
    {
        $this->model::destroy($ids);

        $manager = ApplicationContext::getContainer()->get(Manager::class);
        $manager->destroy($ids,$this->model);

        return true;
    }

    /**
     * 更新一条数据
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $this->filterExecuteAttributes($data, true);
        return $this->model::find($id)->update($data) > 0;
    }

    /**
     * 按条件更新数据
     * @param array $condition
     * @param array $data
     * @return bool
     */
    public function updateByCondition(array $condition, array $data): bool
    {
        $this->filterExecuteAttributes($data, true);
        return $this->model::query()->where($condition)->update($data) > 0;
    }

    /**
     * 单个或批量真实删除数据
     * @param array $ids
     * @return bool
     */
    public function realDelete(array $ids): bool
    {
        foreach ($ids as $id) {
            $model = $this->model::withTrashed()->find($id);
            $model && $model->forceDelete();
        }
        return true;
    }

    /**
     * 单个或批量从回收站恢复数据
     * @param array $ids
     * @return bool
     */
    public function recovery(array $ids): bool
    {
        $this->model::withTrashed()->whereIn((new $this->model)->getKeyName(), $ids)->restore();
        return true;
    }

    /**
     * 单个或批量禁用数据
     * @param array $ids
     * @param string $field
     * @return bool
     */
    public function disable(array $ids, string $field = 'status'): bool
    {
        $this->model::query()->whereIn((new $this->model)->getKeyName(), $ids)->update([$field => $this->model::DISABLE]);
        return true;
    }

    /**
     * 单个或批量启用数据
     * @param array $ids
     * @param string $field
     * @return bool
     */
    public function enable(array $ids, string $field = 'status'): bool
    {
        $this->model::query()->whereIn((new $this->model)->getKeyName(), $ids)->update([$field => $this->model::ENABLE]);
        return true;
    }

    /**
     * @return JiumiModel
     */
    public function getModel(): JiumiModel
    {
        return new $this->model;
    }

    /**
     * 数据导入
     * @param string $dto
     * @param \Closure|null $closure
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[Transaction]
    public function import(string $dto, ?\Closure $closure = null): bool
    {
        return (new JiumiCollection())->import($dto, $this->getModel(), $closure);
    }

    /**
     * 闭包通用查询设置
     * @param \Closure|null $closure 传入的闭包查询
     * @return Builder
     */
    public function settingClosure(?\Closure $closure = null): Builder
    {
        return $this->model::where(function($query) use($closure) {
            if ($closure instanceof \Closure) {
                $closure($query);
            }
        });
    }

    /**
     * 闭包通用方式查询一条数据
     * @param \Closure|null $closure
     * @param array|string[] $column
     * @return Builder|Model|null
     */
    public function one(?\Closure $closure = null, array $column = ['*'])
    {
        return $this->settingClosure($closure)->select($column)->first();
    }

    /**
     * 闭包通用方式查询数据集合
     * @param \Closure|null $closure
     * @param array|string[] $column
     * @return array
     */
    public function get(?\Closure $closure = null, array $column = ['*']): array
    {
        return $this->settingClosure($closure)->get($column)->toArray();
    }

    /**
     * 闭包通用方式统计
     * @param \Closure|null $closure
     * @param string $column
     * @return int
     */
    public function count(?\Closure $closure = null, string $column = '*'): int
    {
        return $this->settingClosure($closure)->count($column);
    }

    /**
     * 闭包通用方式查询最大值
     * @param \Closure|null $closure
     * @param string $column
     * @return mixed|string|void
     */
    public function max(?\Closure $closure = null, string $column = '*')
    {
        return $this->settingClosure($closure)->max($column);
    }

    /**
     * 闭包通用方式查询最小值
     * @param \Closure|null $closure
     * @param string $column
     * @return mixed|string|void
     */
    public function min(?\Closure $closure = null, string $column = '*')
    {
        return $this->settingClosure($closure)->min($column);
    }

    /**
     * 数字更新操作
     * @param int $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function numberOperation(int $id, string $field, int $value): bool
    {
        return $this->update($id, [ $field => $value]);
    }

    /**
     * 搜索参数注入
     * @param $params
     * @param array $where
     * @param \Hyperf\Database\Model\Builder|null $query
     * @return \Jiumi\JiumiModel|\Hyperf\Database\Model\Builder
     */
    public function paramsEmptyQuery($params, array $where = [], Builder $query = null): JiumiModel|Builder
    {
        if (!$query) {
            $query = $this->model::query();
        }

        $object = new class($params, $where) {
            public array $paramsWhere = [];

            public function __construct($params, $where)
            {
                foreach ($params as $field => $value) {
                    if (isset($where[$field])) {
                        $this->caseWhere($field, $where[$field], $value);
                    }
                }
            }

            public function caseWhere($field, $operator, $value): void
            {
                if (is_scalar($operator)) {
                    $res = $this->scalarOptionHandle($field, $operator, $value);
                } else if (is_array($operator)) {
                    $res = $this->arrayOptionHandle($field, $operator, $value);
                } else {
                    $res = $this->scalarOptionHandle($field, $operator, $value);
                }
                $this->paramsWhere[ $res[0]] = [$res[1], $res[2] ];
            }

            /**
             * 标量类型获取
             * @param $field
             * @param $operator
             * @param $value
             * @return array
             */
            public function scalarOptionHandle($field, $operator, $value): array
            {
                return [$field, $operator, $value];
            }

            /**
             * 数组类型处理
             * @param $field
             * @param $operator
             * @param $value
             * @return array
             */
            public function arrayOptionHandle($field, $operator, $value): array
            {
                return [$field, $operator, $value];
            }

            public function getParamsWhere(): array
            {
                return $this->paramsWhere;
            }
        };
        return $this->emptyBuildQuery($object->getParamsWhere(), $query);
    }

    /**
     * 非空查询方法
     * 案例
     * [
     *  'field' => 1,
     *  'field' => ['=', 'index']
     * ]
     * @param array $paramsWhere
     * @param $query
     * @return \Jiumi\JiumiModel|\Hyperf\Database\Model\Builder
     */
    public function emptyBuildQuery(array $paramsWhere = [], $query = null): JiumiModel|Builder
    {
        if (!$query) {
            $query = $this->model::query();
        }
        $object = new class($paramsWhere, $query){

            public Builder $query;

            public function __construct($paramsWhere, Builder $query)
            {
                $this->query = $query;
                foreach ($paramsWhere as $field => $value) {
                    if ($value) {
                        if (is_scalar($value)) {
                            $this->scalarWhere($field, '=', $value);
                        } else if (is_array($value)) {
                            $this->arrayWhere($field, $value);
                        }
                    }
                }
            }

            public function scalarWhere($field, $operator, $value): void
            {
                [ $operator, $value ] = $this->optionHandler($field, $operator, $value);
                $this->query->where($field, $operator, $value);
            }

            private function arrayWhere($field, $value): void
            {
                [ $value[0], $value[1] ] = $this->optionHandler($field, $value[0], $value[1]);
                $this->query->where($field, $value[0], $value[1]);
            }

            public function optionHandler($field, $operator, $value): array
            {
                switch ($operator) {
                    case 'like':
                    case 'like%':
                        if (is_scalar($value)) {
                            throw new NormalStatusException("{$field} type error:The expectation is a string");
                        }
                        $likeMap = ['like' => '%#{val}%', 'like%' => '#{val}%'];
                        $value = str_replace("#{val}", $value, $likeMap[$operator]);
                    break;
                }
                return [$operator, $value];
            }

            public function getQuery(): Builder
            {
                return $this->query;
            }
        };
        return $object->getQuery();
    }
}
