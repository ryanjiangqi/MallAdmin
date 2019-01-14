<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\system\model;

use think\Model;
use think\Db;
use app\system\model\PermissionsGroupMenu;
USE think\helper;

class PermissionsMenu extends Model
{
    public static function search($paginate, $param = null)
    {
        $pageParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['parent_id'])) {
            $where['parent_id'] = $param['parent_id'];
            $pageParam['query']['parent_id'] = $param['parent_id'];
        }
        if (!empty($param['menu_name'])) {
            $pageParam['query']['menu_name'] = $param['menu_name'];
            $list = self::where($where)
                ->where('menu_name', 'like', '%' . $param['menu_name'] . '%')
                ->order('parent_id,id', 'desc')
                ->paginate($paginate, false, $pageParam);
        } else {
            $list = self::where($where)
                ->order('parent_id,id', 'desc')
                ->paginate($paginate, false, $pageParam);
        }


        if (!empty($list)) {
            foreach ($list as $key => $val) {
                if ($val['parent_id'] == 0) {
                    $list[$key]['parent_name'] = '顶级菜单';
                } else {
                    $parentName = self::where(['id' => $val['parent_id']])->find();
                    $list[$key]['parent_name'] = $parentName['menu_name'];
                }
                if ($val['url'] == '') {
                    $list[$key]['url'] = '-';
                }
            }
        }
        return $list;
    }

    public static function allMenuForGroup()
    {
        $data = self::where(['is_deleted' => 0, 'parent_id' => 0])->order('sort', 'desc')->select();
        foreach ($data as $key => $val) {
            $child = PermissionsMenu::where(['is_deleted' => 0, 'parent_id' => $val['id']])->order('sort', 'desc')->select();
            if (!empty($child)) {
                $data[$key]['child'] = $child;
            } else {
                $data[$key]['child'] = '';
            }
        }
        return $data;
    }

    public static function selectMenu($groupId)
    {
        $existMenuNew = [];
        $initMenu = self::allMenuForGroup();
        $existMenu = PermissionsGroupMenu::where(['permissions_group_id' => $groupId])->select()->toArray();
        if (!empty($existMenu)) {
            foreach ($existMenu as $key => $val) {
                $existMenuNew[] = $val['menu_id'];
            }
        }
        if (!empty($initMenu)) {
            foreach ($initMenu as $keyW => $valW) {
                foreach ($valW['child'] as $keyT => $valT) {
                    if (in_array($valT['id'], $existMenuNew)) {
                        $initMenu[$keyW]['child'][$keyT]['checked'] = 'checked';
                    } else {
                        $initMenu[$keyW]['child'][$keyT]['checked'] = '';
                    }
                }
            }
        }
        return $initMenu;
    }
}
