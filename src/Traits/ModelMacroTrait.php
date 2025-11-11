<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */

declare(strict_types=1);

namespace  Jiumi\Traits;

use App\System\Model\SystemDept;
use App\System\Model\SystemRole;
use App\System\Model\SystemUser;
use Hyperf\Context\Context;
use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;
use Jiumi\Exception\JiumiException;

trait ModelMacroTrait
{
    /**
     * 注册自定义方法
     */
    private function registerUserDataScope()
    {
        // 数据权限方法
        $model = $this;
        Builder::macro('userDataScope', function(?int $userid = null) use($model)
        {
            if (! config('jiumiadmin.data_scope_enabled')) {
                return $this;
            }

            $userid = is_null($userid) ? (int) user()->getId() : $userid;

            if (empty($userid)) {
                throw new JiumiException('Data Scope missing user_id');
            }

            /* @var Builder $this */
            if ($userid == env('SUPER_ADMIN')) {
                return $this;
            }

            if (!in_array($model->getDataScopeField(), $model->getFillable())) {
                return $this;
            }

            $dataScope = new class($userid, $this, $model)
            {
                // 用户ID
                protected int $userid;

                // 查询构造器
                protected Builder $builder;

                // 用户查询语句
                protected  $userQuery = null;

                // 外部模型
                protected mixed $model;

                public function __construct(int $userid, Builder $builder, mixed $model)
                {
                    $this->userid  = $userid;
                    $this->builder = $builder;
                    $this->model = $model;
                }

                /**
                 * @return Builder
                 */
                public function execute(): Builder
                {
                    $this->getUserDataScope();
                    return empty($this->userQuery)
                        ? $this->builder
                        : $this->builder->whereIn($this->model->getDataScopeField(), $this->userQuery);
                }

                protected function getUserDataScope(): void
                {
                    $permInfo=get_context_perm_info($this->userid);
                    $this->userQuery=$permInfo['query'];
                }
            };

            return $dataScope->execute();
        });
    }

    /**
     * Description:注册常用自定义方法
     * User:mike
     */
    private function registerBase()
    {
        //添加andFilterWhere()方法
        Builder::macro('andFilterWhere', function ($key, $operator, $value = NULL) {
            if ($value === '' || $value === '%%' || $value === '%') {
                return $this;
            }
            if ($operator === '' || $operator === '%%' || $operator === '%') {
                return $this;
            }
            if($value === NULL){
                return $this->where($key, $operator);
            }else{
                return $this->where($key, $operator, $value);
            }
        });

        //添加orFilterWhere()方法
        Builder::macro('orFilterWhere', function ($key, $operator, $value = NULL) {
            if ($value === '' || $value === '%%' || $value === '%') {
                return $this;
            }
            if ($operator === '' || $operator === '%%' || $operator === '%') {
                return $this;
            }
            if($value === NULL){
                return $this->orWhere($key, $operator);
            }else{
                return $this->orWhere($key, $operator, $value);
            }
        });
    }
}
