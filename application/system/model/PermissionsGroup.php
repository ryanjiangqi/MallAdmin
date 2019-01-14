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
use think\helper;

class PermissionsGroup extends Model
{
    public static function search($paginate, $param = null)
    {
        $pageParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
            $pageParam['query']['status'] = $param['status'];
        }
        if (!empty($param['built'])) {
            $where['built'] = $param['built'];
            $pageParam['query']['built'] = $param['built'];
        }

        if (!empty($param['content'])) {
            $pageParam['query']['content'] = $param['content'];
            $info = self::where($where)
                ->where('permissions_name', 'like', '%' . $param['content'] . '%')
                ->order('id', 'desc')
                ->paginate($paginate, false, $pageParam);
        } else {
            $info = self::where($where)
                ->order('id', 'desc')
                ->paginate($paginate, false, $pageParam);
        }

        return $info;
    }

    public static function allSearch()
    {
        $info = self::where(['is_deleted' => 0])->order('id', 'desc')->select()->toArray();
        return $info;
    }

    public static function addGroup($data)
    {
        $group = self::create([
            'permissions_code' => 'code',
            'permissions_name' => $data['name'],
            'built' => '非内置权限',
            'status' => !empty($data['status']) ? $data['status'] : '不发布',
            'create_on' => date('Y-m-d H:i:s'),
            'modify_on' => date('Y-m-d H:i:s'),
            'description' => $data['description'],
        ]);
        if (!empty($data['menu_id'])) {
            foreach ($data['menu_id'] as $key => $val) {
                $sortInfo = PermissionsMenu::where(['id' => $val])->find();
                PermissionsGroupMenu::create([
                    'permissions_group_id' => $group->id,
                    'menu_id' => $val,
                    'create_on' => date('Y-m-d H:i:s'),
                    'modify_on' => date('Y-m-d H:i:s'),
                    'sort' => $sortInfo['sort'],
                ]);
            }
        }
        return true;
    }

    public static function updateGroup($data)
    {
        self::where(['id' => $data['id']])->update([
            'permissions_name' => $data['name'],
            'built' => '非内置权限',
            'status' => !empty($data['status']) ? $data['status'] : '不发布',
            'modify_on' => date('Y-m-d H:i:s'),
            'description' => !empty($data['description']) ? $data['description'] : '',
        ]);
        PermissionsGroupMenu::destroy(['permissions_group_id' => $data['id']]);
        if (!empty($data['menu_id'])) {
            foreach ($data['menu_id'] as $key => $val) {
                $sortInfo = PermissionsMenu::where(['id' => $val])->find();
                PermissionsGroupMenu::create([
                    'permissions_group_id' => $data['id'],
                    'menu_id' => $val,
                    'create_on' => date('Y-m-d H:i:s'),
                    'modify_on' => date('Y-m-d H:i:s'),
                    'sort' => $sortInfo['sort'],
                ]);
            }
        }

    }

}
