<?php

namespace Qz\Admin\Permission\Cores\AdminPageOption;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminPageOption;

class AdminPageOptionIdGet extends Core
{
    protected function execute()
    {
        if (empty($this->getCode()) || empty($this->getAdminPageId())) {
           return;
        }
        $model = AdminPageOption::query()
            ->withoutGlobalScope('isShow')
            ->where('admin_page_id', $this->getAdminPageId())
            ->where('code', $this->getCode())
            ->first();
        if (empty($model)) {
            return;
        }
        $this->setId($model->getKey());
    }

    protected $code;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
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
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    protected $adminPageId;

    /**
     * @return mixed
     */
    public function getAdminPageId()
    {
        return $this->adminPageId;
    }

    /**
     * @param mixed $adminPageId
     * @return $this
     */
    public function setAdminPageId($adminPageId)
    {
        $this->adminPageId = $adminPageId;
        return $this;
    }


}
