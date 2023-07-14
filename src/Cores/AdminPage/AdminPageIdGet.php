<?php

namespace Qz\Admin\Permission\Cores\AdminPage;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminPage;

class AdminPageIdGet extends Core
{
    protected function execute()
    {
        if (!empty($this->getCode())) {
            $model = AdminPage::query()
                ->where('code', $this->getCode())
                ->first();
            if (!empty($model)) {
                $this->setId($model->getKey());
            }
        }
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
}
