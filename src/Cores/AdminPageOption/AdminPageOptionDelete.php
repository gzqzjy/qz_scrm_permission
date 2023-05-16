<?php

namespace Qz\Admin\Permission\Cores\AdminPageOption;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminPageOption;

class AdminPageOptionDelete extends Core
{
    protected function execute()
    {
        $model = AdminPageOption::withTrashed()
            ->findOrFail($this->getId());
        $model->delete();
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
     * @return AdminPageOptionDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
