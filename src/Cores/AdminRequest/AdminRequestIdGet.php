<?php


namespace Qz\Admin\Permission\Cores\AdminRequest;


use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRequest;

class AdminRequestIdGet extends Core
{
    protected function execute()
    {
        if (!empty($this->getCode())) {
            $model = AdminRequest::query()
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
     * @param mixed $code
     * @return AdminRequestIdGet
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
     * @return AdminRequestIdGet
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
