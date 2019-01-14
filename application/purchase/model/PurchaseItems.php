<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\purchase\model;

use think\Model;
use think\Db;

class PurchaseItems extends Model
{


    public static function search($paginate, $param = [])
    {
        $searchParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['parent'])) {
            $where['parent_id'] = $param['parent'];
            $searchParam['query']['parent'] = $param['parent'];
        }
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
            $searchParam['query']['status'] = $param['status'];
        }

        if (!empty($param['content'])) {
            $info = self::where($where)
                ->where('name', 'like', '%' . $param['content'] . '%')
                ->order('parent_id,id', 'desc')
                ->paginate($paginate, false, $searchParam);
        } else {
            $info = self::where($where)
                ->order('parent_id,id', 'desc')
                ->paginate($paginate, false, $searchParam);
        }
        if (!empty($info)) {
            foreach ($info as $key => $value) {
                if ($value['parent_id'] == 0) {
                    $info[$key]['parent_name'] = '顶级分类';
                } else {
                    $parent = self::where(['id' => $value['parent_id']])->find();
                    $info[$key]['parent_name'] = $parent['name'];
                }

            }
        }
        return $info;
    }

    public static function addItems($data)
    {
        return self::create([
            'name' => !empty($data['name']) ? $data['name'] : '',
            'status' => !empty($data['status']) ? $data['status'] : '',
            'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : '',
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
            'sort' => !empty($data['sort']) ? $data['sort'] : '',
            'create_on' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function itemsEdit($data)
    {
        self::where(['id' => $data['id']])->update([
            'name' => !empty($data['name']) ? $data['name'] : '',
            'status' => !empty($data['status']) ? $data['status'] : '',
            'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : '',
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
            'sort' => !empty($data['sort']) ? $data['sort'] : '',
        ]);
    }
}
