<?php


namespace Qz\Admin\Permission\Cores\AdminRole;


use App\Cores\Core;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminMenu\GetTreeAdminMenusWithCheck;
use Qz\Admin\Permission\Models\AdminMenu;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminRoleMenu;
use Qz\Admin\Permission\Models\AdminRolePageColumn;
use Qz\Admin\Permission\Models\AdminRolePageOption;

class GetMenuByAdminRole extends Core
{
    protected function execute()
    {
        $excludeMenuId = AdminMenu::query()
            ->where('name', '系统设置')
            ->where('parent_id', 0)
            ->value('id');//系统设置不显示
        $adminMenuModel = AdminMenu::query()
            ->where('parent_id', 0)
            ->where(function (Builder $builder) use ($excludeMenuId){
                if ($excludeMenuId){
                    $builder->where('id', '<>', $excludeMenuId);
                }
            });

        $adminMenuModel = $adminMenuModel->get();
        $adminMenuModel->load([
            'children',
            'adminPage',
            'adminPage.adminPageOptions',
            'adminPage.adminPageColumns',
        ]);
        $adminMenuModel = $adminMenuModel->toArray();
        $menus = GetTreeAdminMenusWithCheck::init()
            ->setAdminMenus($adminMenuModel)
            ->setAdminMenuIds($this->getAdminMenuIds())
            ->setAdminPageColumnIds($this->getAdminPageColumnIds())
            ->setAdminPageOptionIds($this->getAdminPageOptionIds())
            ->run()
            ->getTreeAdminMenus();
        $this->setMenus($menus);
    }


    protected $adminRoleIds;

    /**
     * @return mixed
     */
    public function getAdminRoleIds()
    {
        return $this->adminRoleIds;
    }

    /**
     * @param mixed $adminRoleId
     * @return GetMenuByAdminRole
     */
    public function setAdminRoleIds($adminRoleIds)
    {
        $this->adminRoleIds = $adminRoleIds;

        $adminRoleMenuIds = AdminRoleMenu::query()
            ->whereIn('admin_role_id', $adminRoleIds)
            ->pluck('admin_menu_id')
            ->toArray();
        $adminRolePageOptionIds = AdminRolePageOption::query()
                ->whereIn('admin_role_id', $adminRoleIds)
                ->pluck('admin_page_option_id')
                ->toArray();
        $adminRolePageColumnIds = AdminRolePageColumn::query()
            ->whereIn('admin_role_id', $adminRoleIds)
            ->pluck('admin_page_column_id')
            ->toArray();


        $this->setAdminMenuIds($adminRoleMenuIds);
        $this->setAdminPageColumnIds($adminRolePageColumnIds);
        $this->setAdminPageOptionIds($adminRolePageOptionIds);
        return $this;
    }

    protected $adminMenuIds;

    /**
     * @return mixed
     */
    public function getAdminMenuIds()
    {
        return $this->adminMenuIds;
    }

    /**
     * @param mixed $adminMenuIds
     * @return GetMenuByAdminRole
     */
    public function setAdminMenuIds($adminMenuIds)
    {
        $this->adminMenuIds = $adminMenuIds;
        return $this;
    }

    protected $adminPageColumnIds;

    /**
     * @return mixed
     */
    public function getAdminPageColumnIds()
    {
        return $this->adminPageColumnIds;
    }

    /**
     * @param mixed $adminPageColumnIds
     * @return GetMenuByAdminRole
     */
    public function setAdminPageColumnIds($adminPageColumnIds)
    {
        $this->adminPageColumnIds = $adminPageColumnIds;
        return $this;
    }

    protected $adminPageOptionIds;

    /**
     * @return mixed
     */
    public function getAdminPageOptionIds()
    {
        return $this->adminPageOptionIds;
    }

    /**
     * @param mixed $adminPageOptionIds
     * @return GetMenuByAdminRole
     */
    public function setAdminPageOptionIds($adminPageOptionIds)
    {
        $this->adminPageOptionIds = $adminPageOptionIds;
        return $this;
    }

    protected $menus;

    /**
     * @return mixed
     */
    public function getMenus()
    {
        return $this->menus;
    }

    /**
     * @param mixed $menus
     * @return GetMenuByAdminRole
     */
    public function setMenus($menus)
    {
        $this->menus = $menus;
        return $this;
    }




}
