<?php
namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminRequest;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminUser\AdminPageOptionIdsGetByAdminUserId;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminRequest;

class AdminRequestController extends AdminController
{
    public function all()
    {
        $model = AdminRequest::query()
            ->select(['id', 'name', 'admin_page_option_id']);
        if (!$this->isAdministrator()) {
            $model->whereIn('admin_page_option_id', (array) AdminPageOptionIdsGetByAdminUserId::init()
                ->setAdminUserId($this->getLoginAdminUserId())
                ->run()
                ->getId());
        }
        $model = $model->orderBy('admin_page_option_id')
            ->get();

        $model->load([
            'adminPageOption' => function (BelongsTo $belongsTo) {
                $belongsTo->withoutGlobalScope('isShow');
            },
            'adminPageOption.adminPage:id,name',
            'adminPageOption.adminPage.adminMenus:id,name,admin_page_id',
        ]);
        $model = $model->toArray();

        $data = [];
        foreach ($model as $value) {
            $name = "";
            if ($adminPageOption = Arr::get($value, 'admin_page_option')) {
                if ($adminPage = Arr::get($adminPageOption, 'admin_page')) {
                    $name = Arr::get($adminPage, 'admin_menus') ? Arr::get($adminPage, 'admin_menus.0.name') . '-' : Arr::get($adminPage, 'name') . '-';
                }
                $name .= Arr::get($adminPageOption, 'name') . '-';
            }
            $name .= Arr::get($value, 'name');
            $data[] = [
                "label" => $name,
                "value" => Arr::get($value, 'id')
            ];
        }
        return $this->response($data);
    }
}
