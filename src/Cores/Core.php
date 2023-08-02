<?php

namespace Qz\Admin\Permission\Cores;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class Core
{
    final public static function init()
    {
        return (new static);
    }

    final public function run()
    {
        try {
            DB::beginTransaction();
            $this->execute();
            DB::commit();;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error(get_class($this), [
                $exception->getMessage(),
                $this->all(),
            ]);
            $this->setExceptionMessage($exception->getMessage());
        }
        return $this;
    }

    final public function afterResponseRun()
    {
        try {
            dispatch(function () {
                try {
                    $this->execute();
                } catch (Exception $exception) {
                    Log::error(get_class($this), [
                        $exception->getMessage(),
                        $this->all(),
                    ]);
                }
            })->afterResponse();
        } catch (Exception $exception) {
            Log::error(get_class($this), [
                $exception->getMessage(),
                $this->all(),
            ]);
        }
    }

    abstract protected function execute();

    final protected function all()
    {
        return get_object_vars($this);
    }

    protected $exceptionMessage;

    /**
     * @return mixed
     */
    final public function getExceptionMessage()
    {
        return $this->exceptionMessage;
    }

    /**
     * @param mixed $exceptionMessage
     * @return Core
     */
    final protected function setExceptionMessage($exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;
        return $this;
    }
}
