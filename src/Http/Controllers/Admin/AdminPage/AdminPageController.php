<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminPage;

use Qz\Admin\Permission\Cores\AdminPage\AdminPageAdd;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminPageController extends AdminController
{
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

    public function permission()
    {
        $model = AdminMenu::query()
            ->where('parent_id', 0)
            ->orderByDesc('sort')
            ->get();
        $model->load([
            'children',
            'adminPageOptions',
            'adminPageColumns',
        ]);
        return $this->success($model);
    }
}
