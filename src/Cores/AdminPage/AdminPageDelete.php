<?php

namespace Qz\Admin\Permission\Cores\AdminPage;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminPage;

class AdminPageDelete extends Core
{
    protected function execute()
    {
        $model = AdminPage::withTrashed()
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
     * @return AdminPageDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
