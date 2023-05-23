<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use  Qz\Admin\Permission\Cores\AdminPage\AdminPageAdd;
use  Qz\Admin\Permission\Cores\AdminPage\AdminPageIdGet;
use  Qz\Admin\Permission\Cores\AdminPageColumn\AdminPageColumnAdd;
use  Qz\Admin\Permission\Cores\AdminPageOption\AdminPageOptionAdd;
use  Qz\Admin\Permission\Cores\Subsystem\SubsystemIdGet;
use  Qz\Admin\Permission\Facades\Access;
use  Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminMenu;
use Qz\Admin\Permission\Models\AdminPage;
use  Qz\Admin\Permission\Models\AdminPageColumn;
use  Qz\Admin\Permission\Models\AdminUser;
use  Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemPageOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Qz\Admin\Permission\Exceptions\MessageException;

class AccessController extends AdminController
{
    public function login()
    {
        $status = 'error';
        $type = 'mobile';
        $mobile = $this->getParam('mobile');
        $token = '';
        $model = AdminUser::query()
            ->where('mobile', $mobile)
            ->whereHas('adminUserCustomerSubsystems', function (Builder $builder) {
                $builder->where('status', AdminUserCustomerSubsystem::STATUS_NORMAL)
                    ->whereHas('customerSubsystem', function (Builder $builder) {
                        $builder->where('subsystem_id', SubsystemIdGet::init()
                            ->run()
                            ->getId());
                    });
            })
            ->first();
        if (empty($model)) {
            return $this->response(compact('token', 'status', 'type'));
        }
        if ($model instanceof AdminUser) {
            $model->setConnection(config('database.default'))->tokens()->delete();
            $token = $model->setConnection(config('database.default'))->createToken('admin_user')->plainTextToken;
            if ($token) {
                $status = 'ok';
            }
        }
        return $this->response(compact('token', 'status', 'type'));
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function captcha()
    {
        $mobile = $this->getParam('phone');
        if (empty($mobile)) {
            throw new MessageException('手机号不能为空');
        }
        return $this->success();
    }

    public function logout()
    {
        $user = Auth::guard('admin')
            ->user();
        if ($user instanceof AdminUser) {
            $user->setConnection(config('database.default'))->tokens()->delete();
        }
        return $this->success();
    }

    public function user()
    {
        $user = Auth::guard('admin')->user();
        $name = '';
        if ($user instanceof AdminUser) {
            $name = Arr::get($user, 'name', '');
        }
        return $this->success(compact('name'));
    }

    public function addPage()
    {
        $pageId = AdminPageAdd::init()
            ->setName($this->getParam('page_name'))
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        return $this->success(compact('pageId'));
    }

    public function columns()
    {
        $pageId = AdminPageIdGet::init()
            ->setSubsystemId(Access::getSubsystemId())
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        $columns = $this->getParam('columns');
        $pageColumnIds = [];
        foreach ($columns as $column) {
            $dataIndex = Arr::get($column, 'data_index');
            if (is_array($dataIndex)) {
                $dataIndex = implode('.', $dataIndex);
            }
            $pageColumnId = AdminPageColumnAdd::init()
                ->setAdminPageId($pageId)
                ->setCode($dataIndex)
                ->setName(Arr::get($column, 'title'))
                ->run()
                ->getId();
            if ($pageColumnId) {
                $pageColumnIds = Arr::prepend($pageColumnIds, $pageColumnId);
            }
        }
        $model = AdminPageColumn::query()
            ->whereIn('id', $pageColumnIds);
        if (!Access::getAdministrator()) {
            $model->whereHas('AdminUserCustomerSubsystemPageColumns', function (Builder $builder) use ($pageColumnIds) {
                $builder->whereHas('adminUserCustomerSubsystem', function (Builder $builder) {
                    $builder->where('admin_user_id', Auth::guard('admin')->id())
                        ->whereHas('customerSubsystem', function (Builder $builder) {
                            $builder->where('customer_id', Access::getCustomerId())
                                ->where('subsystem_id', Access::getSubsystemId());
                        });
                });
            });
        }
        $dataIndexes = $model
            ->pluck('code');
        return $this->response(compact('dataIndexes'));
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function option()
    {
        $access = false;
        $validator = Validator::make($this->getParam(), [
            'page_code' => [
                'required',
                Rule::exists(AdminPage::class, 'code')
                    ->withoutTrashed()
                    ->where('subsystem_id', Access::getSubsystemId())
            ],
            'option_code' => [
                'required',
            ],
            'option_name' => [
                'required',
            ],
        ], [
            'page_code.required' => '页面标识不能为空',
            'page_code.exists' => '页面标识不存在',
            'option_code.required' => '页面操作标识不能为空',
            'option_name.required' => '页面操作标识不能为空',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $pageId = AdminPageIdGet::init()
            ->setSubsystemId(Access::getSubsystemId())
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        if (empty($pageId)) {
            return $this->response($access);
        }
        $pageOptionId = AdminPageOptionAdd::init()
            ->setAdminPageId($pageId)
            ->setCode($this->getParam('option_code'))
            ->setName($this->getParam('option_name'))
            ->run()
            ->getId();
        if (empty($pageOptionId)) {
            return $this->response($access);
        }
        if (Access::getAdministrator()) {
            $access = true;
            return $this->response($access);
        }
        $access = AdminUserCustomerSubsystemPageOption::query()
            ->whereHas('adminUserCustomerSubsystem', function (Builder $builder) {
                $builder->where('admin_user_id', Auth::guard('admin')->id())
                    ->whereHas('customerSubsystem', function (Builder $builder) {
                        $builder->where('customer_id', Access::getCustomerId())
                            ->where('subsystem_id', Access::getSubsystemId());
                    });
            })
            ->where('admin_page_option_id', $pageOptionId)
            ->exists();
        return $this->response($access);
    }

    public function menu()
    {
        $model = AdminMenu::query()
            ->where('parent_id', 0);
        $administrator = $this->isAdministrator();
        if (empty($administrator)) {
            $model->whereHas('adminUserCustomerSubsystemMenus', function (Builder $builder) {
                $builder->whereHas('adminUserCustomerSubsystem', function (Builder $builder) {
                    $builder->where('admin_user_id', $this->getLoginAdminUserId());
                });
            });
        }
        $model = $model->get();
        $model->load([
            'children'
        ]);
        $menus = [];
        foreach ($model as $value) {
            $menus[] = $this->menuItem($value);
        }
        return $this->response($menus);
    }

    protected function menuItem($value)
    {
        $route = Arr::get($value, 'config');
        $route = Arr::add($route, 'name', Arr::get($value, 'name'));
        $route = Arr::add($route, 'path', Arr::get($value, 'path'));
        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                $routes[] = $this->menuItem($child);
            }
            if (!empty($routes)) {
                Arr::set($route, 'routes', $routes);
            }
        }
        return $route;
    }
}
