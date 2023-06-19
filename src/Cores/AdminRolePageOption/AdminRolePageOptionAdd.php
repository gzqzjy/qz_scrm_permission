<?php
namespace Qz\Admin\Permission\Cores\AdminRolePageOption;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRolePageOption;

class AdminRolePageOptionAdd extends Core
{
    protected function execute()
    {
        $model = AdminRolePageOption::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'admin_role_id' => $this->getAdminRoleId(),
                'admin_page_option_id' => $this->getAdminPageOptionId(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
        $this->setId($model->getKey());
    }
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AdminRolePageOptionAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminRolePageOptionAdd
     */
    public function setParam($param)
    {
        foreach ($param as $key => $value) {
            $setMethod = 'set' . Str::studly($key);
            if (method_exists($this, $setMethod)) {
                call_user_func([$this, $setMethod], $value);
            }
        }
        return $this;
    }

    protected $adminRoleId;

    /**
     * @return mixed
     */
    public function getAdminRoleId()
    {
        return $this->adminRoleId;
    }

    /**
     * @param mixed $adminRoleId
     * @return AdminRolePageOptionAdd
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
        return $this;
    }

    protected $adminPageOptionId;

    /**
     * @return mixed
     */
    public function getAdminPageOptionId()
    {
        return $this->adminPageOptionId;
    }

    /**
     * @param mixed $adminPageOptionId
     * @return AdminRolePageOptionAdd
     */
    public function setAdminPageOptionId($adminPageOptionId)
    {
        $this->adminPageOptionId = $adminPageOptionId;
        return $this;
    }


}
