<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\system\controller;

use app\base\controller\Base;
use app\service\system\PermissionsSerivce;
use think\Request;
use app\component\MyHelper;

/**
 * Description of Permissions
 *
 * @author Administrator
 */
class Permissions extends Base
{

    protected $moduleView = 'system';

    /**
     * @desc 权限组列表
     * @return type
     */
    public function roleGroup(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['built', 'status', 'content'], $searchParam);
        $list = PermissionsSerivce::instance()->permissionsList($searchParam);
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        return view($this->viewPath('role/role-group'));
    }

    public function groupAdd(Request $request)
    {
        $data = $request->param();
        if (!empty($data['action']) && $data['action'] == 'add') {
            PermissionsSerivce::instance()->groupAdd($data);
            $this->layMsg('添加成功', 6);
            exit;
        }
        $menuList = PermissionsSerivce::instance()->allMenuForGroup();
        $this->assign('menu', $menuList);
        return view($this->viewPath('role/role-add'));
    }

    public function menu(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['parent_id', 'menu_name'], $searchParam);
        $list = PermissionsSerivce::instance()->menuList($searchParam);
        $parent = PermissionsSerivce::instance()->parentList();
        $this->assign('list', $list);
        $this->assign('parent', $parent);
        $this->assign('search', $searchParam);
        return view($this->viewPath('role/menu'));
    }

    public function deleted(Request $request)
    {
        $param = $request->param();
        PermissionsSerivce::instance()->deleted($param);
        return json_encode(['code' => 10000, 'msg' => 'success']);
    }

    public function groupedit(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            PermissionsSerivce::instance()->groupEdit($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $menuList = PermissionsSerivce::instance()->selectMenu($param['id']);
        $detail = PermissionsSerivce::instance()->groupDetail($param['id']);
        $this->assign('menu', $menuList);
        $this->assign('detail', $detail);
        return view($this->viewPath('role/role-edit'));
    }
}
