<?php

namespace Qz\Admin\Access\Cores\AdminPage;

use Qz\Admin\Access\Cores\Core;
use App\Models\AdminPage;

class AdminPageIdGet extends Core
{
    protected function execute()
    {
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
        if (!empty($code)) {
            $model = AdminPage::query()
                ->where('code', 'admin')
                ->first();
            if (!empty($model)) {
                $this->setId($model->getKey());
            }
        }
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
