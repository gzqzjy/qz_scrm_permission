<?php

namespace Qz\Admin\Access\Cores\AdminPageOption;

use Qz\Admin\Access\Cores\Core;
use App\Models\AdminPageOption;

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
