<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminMenu;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminMenu\AdminMenuAdd;
use Qz\Admin\Permission\Cores\AdminMenu\AdminMenuDelete;
use Qz\Admin\Permission\Cores\AdminMenu\AdminMenuUpdate;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminMenuController extends AdminController
{
    public function get()
    {
        $model = AdminMenu::query()
            ->where('subsystem_id', Access::getSubsystemId());
        $model = $this->filter($model);
        $model = $model->paginate($this->getPageSize());
        call_user_func_array([$model, 'load'], [
            'parent',
            'adminPage'
        ]);
        return $this->page($model);
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function store()
    {
        $validator = Validator::make($this->getParam(), [
            'name' => [
                'required',
            ],
            'path' => [
                'required',
                Rule::unique(AdminMenu::class)
                    ->withoutTrashed()
                    ->where('subsystem_id', Access::getSubsystemId())
            ],
        ], [
            'name.required' => '菜单名不能为空',
            'path.required' => '菜单路由不能为空',
            'path.unique' => '菜单路由不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $this->addParam('subsystem_id', Access::getSubsystemId());
        $this->addParam('parent_id', 0);
        $this->addParam('sort', 0);
        $this->addParam('admin_page_id', 0);
        $id = AdminMenuAdd::init()
            ->setParam($this->getParam())
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function update()
    {
        $validator = Validator::make($this->getParam(), [
            'path' => [
                Rule::unique(AdminMenu::class)
                    ->withoutTrashed()
                    ->where('subsystem_id', Access::getSubsystemId())
                    ->ignore($this->getParam('id'))
            ],
        ], [
            'path.unique' => '菜单路由不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $this->addParam('parent_id', 0);
        $this->addParam('sort', 0);
        $this->addParam('admin_page_id', 0);
        $id = AdminMenuUpdate::init()
            ->setId($this->getParam('id'))
            ->setParam($this->getParam())
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

    public function destroy()
    {
        $id = $this->getParam('id');
        if (is_array($id)) {
            foreach ($id as $value) {
                AdminMenuDelete::init()
                    ->setId($value)
                    ->run()
                    ->getId();
            }
            return $this->success();
        }
        AdminMenuDelete::init()
            ->setId($id)
            ->run()
            ->getId();
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminMenu::query()
            ->selectRaw($select)
            ->where('subsystem_id', Access::getSubsystemId());
        $model = $this->filter($model);
        $model = $model->get();
        return $this->response($model);
    }
}
