<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminPage;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminPage\AdminPageAdd;
use Qz\Admin\Permission\Cores\AdminPage\AdminPageDelete;
use Qz\Admin\Permission\Cores\AdminPage\AdminPageUpdate;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminPageController extends AdminController
{
    public function get()
    {
        $model = AdminPage::query()
            ->where('subsystem_id', Access::getSubsystemId());
        $model = $this->filter($model);
        $model = $model->paginate($this->getPageSize());
        foreach ($model as $item) {
            $item->subsystem_ids = $item->subsystems->pluck('id');
        }
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
            'code' => [
                'required',
            ],
        ], [
            'name' => [
                'required' => '页面名不能为空',
            ],
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $id = AdminPageAdd::init()
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
            'name' => [
                'sometimes',
                Rule::unique('admin_pages')->ignore($this->getParam('id'))
            ],
            'admin_user_mobile' => [
                'sometimes',
            ],
        ], [
            'name.unique' => '页面名已重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $id = AdminPageUpdate::init()
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
                AdminPageDelete::init()
                    ->setId($value)
                    ->run()
                    ->getId();
            }
            return $this->success();
        }
        AdminPageDelete::init()
            ->setId($id)
            ->run()
            ->getId();
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminPage::query()
            ->selectRaw($select)
            ->where('subsystem_id', Access::getSubsystemId());
        $model = $this->filter($model);
        $model = $model->get();
        return $this->response($model);
    }
}
