<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\monitor\model;

use think\Model;
use think\Db;

class ClientPcType extends Model
{
    public static function search($paginate, $param = null)
    {
        $searchParam = [];
        $where['del_flag'] = 0;
        $list = ClientPcType::where($where)
                ->order('id','desc')
                ->paginate($paginate, false, $searchParam);
        return $list;
    }

    public static function addClientPcType($data)
    {
        return ClientPcType::create([
            'type_number' => 2,
            'type_name' => !empty($data['type_name']) ? $data['type_name'] : "",
            'billing_methods' => !empty($data['billing_methods']) ? $data['billing_methods'] : 1,
            'cost' => !empty($data['cost']) ? $data['cost'] : 0,
            'del_flag' =>0,
            'create_user' =>1,
            'create_time' => date('Y-m-d H:i:s'),
            'update_user' => 1,
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }
}
