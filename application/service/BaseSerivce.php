<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service;

use app\system\model\PermissionsGroupMenu;
use app\system\model\PermissionsMenu;
use app\base\model\Base;
use think\helper;
use think\Request;
use think\Db;
use think\Config;
use upload\Uploadimage;

class BaseSerivce
{
    const PAGINATE = 15;

    /**
     * 实例化对象本身
     * @param type $parameter 对象属性
     * @return \app\service\className 实例
     */
    public static function instance($parameter = null)
    {
        $className = get_called_class();
        return new $className($parameter);
    }

    public static function getPermissionsMenu($group)
    {
        $data = [];
        $menu = PermissionsGroupMenu::where(['permissions_group_id' => $group, 'is_deleted' => 0])->order('sort', 'desc')->select()->toArray();
        foreach ($menu as $key => $val) {
            $menuInfo = PermissionsMenu::where(['id' => $val['menu_id']])->find()->toArray();
            if ($menuInfo['parent_id'] != 0) {
                $parentInfo = PermissionsMenu::where(['id' => $menuInfo['parent_id']])->find()->toArray();
                $data[$menuInfo['parent_id']]['parent'] = $parentInfo;
                $data[$menuInfo['parent_id']]['child'][$key] = $menuInfo;
            }
        }
        return $data;
    }

    public static function getAllMenu()
    {
        $menuInfo = PermissionsMenu::where(['parent_id' => 0, 'is_deleted' => 0])->order('sort', 'desc')->select()->toArray();
        if (!empty($menuInfo)) {
            foreach ($menuInfo as $key => $val) {
                $child = PermissionsMenu::where(['parent_id' => $val['id'], 'is_deleted' => 0])->order('sort', 'desc')->select()->toArray();
                $menuInfo[$key]['child'] = $child;
                $menuInfo[$key]['parent'] = $val;
            }
        }
        return $menuInfo;
    }

    public static function menu()
    {
        $data = [];
        $menuInfo = PermissionsMenu::where(['is_deleted' => 0])->select()->toArray();
        if (!empty($menuInfo)) {
            foreach ($menuInfo as $key => $val) {
                if (!empty($val['url'])) {
                    $data[] = $val['url'];
                }
            }
        }
        return $data;
    }

    public static function checkPermissions($groupId)
    {
        //最高权限
        if ($groupId == 0) {
            return true;
        }
        $allMenu = self::menu();
        $noPermissionArray = ['home/index/index'];
        $request = Request::instance();
        $moduleName = $request->module();
        $controllerName = $request->controller();
        $action = $request->action();
        $currentUrl = strtolower($moduleName) . '/' . strtolower($controllerName) . '/' . strtolower($action);
        $menu = Db::table('permissions_group_menu')
            ->alias('a')
            ->join('permissions_menu b', 'a.menu_id = b.id')
            ->where(['a.permissions_group_id' => $groupId])
            ->select();
        $checkPer = false;
        if (!in_array($currentUrl, $allMenu)) {
            return true;
        }
        foreach ($menu as $key => $val) {
            if ($currentUrl == $val['url']) {
                return true;
            }
        }
        return false;
    }

    public static function uploadImage($fileData)
    {
        $uploadArray['savepath'] = Config::get('uploadimage.path');
        $uploadArray['post'] = $fileData;
        $uploadOject = new Uploadimage($uploadArray);
        $result = $uploadOject->uploadImage();
        //如果返回上传的图片的新name ，操作数据库
        if (strpos($result, '.')) {
            $data = ['filename' => $result, 'oldname' => $fileData['file']['name'], 'addtime' => time()];
            Db::table('fileresources')->insert($data);
        }
        return $result;
    }

    public static function getImageList()
    {
        $list = Base::imageList(15);
        return $list;
    }

    /**
     * @desc 每隔一段时间扣去顾客账户金额
     * @param $countFee 每扣除一次金额的时间间隔
     * @param $hourFee 每小时上机金额
     * @return float
     */
    public static function floorFeeBase($countFee, $hourFee)
    {
        $percent = $countFee / 3600;
        return round($percent * $hourFee, 2);
    }

}
