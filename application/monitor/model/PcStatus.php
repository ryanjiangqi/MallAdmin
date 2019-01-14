<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\monitor\model;

use think\Model;
use think\Db;
use app\customer\model\Customer;

class PcStatus extends Model
{
    public static function search($paginate, $param = null)
    {
        $searchParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
            $searchParam['query']['status'] = $param['status'];
        }
        if (!empty($param['content'])) {
            $searchParam['query']['content'] = $param['content'];
            $list = PcStatus::where($where)
                ->where('pc_no', 'like', '%' . $param['content'] . '%')
                ->order('status','desc')
                ->paginate($paginate, false, $searchParam);
        } else {
            $list = PcStatus::where($where)
                ->order('status','desc')
                ->paginate($paginate, false, $searchParam);
        }
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                if (!empty($val['customer_id'])) {
                    $customer = Customer::where(['id' => $val['customer_id']])->find();
                    $list[$key]['user_name'] = $customer['user_name'];
                    $data['method'] = 'logoutremote';
                    $data['user_name'] = $customer['user_name'];
                    $data['pc_no'] = $val['pc_no'];
                    $list[$key]['base64_info'] = base64_encode(json_encode($data));
                } else {
                    $list[$key]['user_name'] = '';
                    $list[$key]['base64_info'] = '';
                }
            }
        }
        return $list;
    }

    public static function addPc($data)
    {
        return PcStatus::create([
            'pc_no' => !empty($data['pc_no']) ? $data['pc_no'] : "",
            'pc_marker' => !empty($data['pc_marker']) ? $data['pc_marker'] : "",
            'create_on' => !empty($data['create_on']) ? $data['create_on'] : date('Y-m-d H:i:s'),
            'note' => !empty($data['note']) ? $data['note'] : "",
        ]);
    }
}
