<?php
namespace Qz\Admin\Permission\Cores\Subsystem;

use Qz\Admin\Permission\Cores\Core;

class SubsystemIdGet extends Core
{
    public function execute()
    {
        $id = SubsystemAdd::init()
            ->setAppKey(config('app.key'))
            ->run()
            ->getId();
        if (!empty($id)) {
            $this->setId($id);
        }
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
