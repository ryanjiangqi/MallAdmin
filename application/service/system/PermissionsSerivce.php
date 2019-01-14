<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service\system;

use think\Model;
use think\Db;
use app\system\model\PermissionsGroup;
use app\system\model\PermissionsMenu;
use app\system\model\PermissionsGroupMenu;
use app\service\BaseSerivce;

class PermissionsSerivce extends BaseSerivce
{

    public function permissionsList($searchParam)
    {
        return PermissionsGroup::search(self::PAGINATE, $searchParam);
    }

    public function groupAdd($data)
    {
        $group = PermissionsGroup::addGroup($data);
        return true;
    }

    public function menuList($searchParam)
    {
        return PermissionsMenu::search(self::PAGINATE,$searchParam);
    }

    public function allMenuForGroup()
    {
        return PermissionsMenu::allMenuForGroup();
    }

    public function allSearch()
    {
        return PermissionsGroup::allSearch();
    }

    public function parentList()
    {
        return PermissionsMenu::where(['is_deleted' => 0, 'parent_id' => 0])->order('sort,id', 'desc')->select();
    }

    public function deleted($data)
    {
        PermissionsGroup::destroy(['id' => $data['id']]);
        PermissionsGroupMenu::destroy(['permissions_group_id' => $data['id']]);
        return true;
    }

    public function selectMenu($groupId)
    {
        return PermissionsMenu::selectMenu($groupId);
    }

    public function groupDetail($groupId)
    {
        return PermissionsGroup::where(['id' => $groupId])->find();
    }

    public function groupEdit($data)
    {
        PermissionsGroup::updateGroup($data);
        return true;
    }

}
