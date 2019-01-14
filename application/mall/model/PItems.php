<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\mall\model;

use think\Model;
use think\Db;

class PItems extends Model
{

    public static function addItems($data)
    {
        if (!empty($data['attribute_id'])) {
            $attribute = implode(',', $data['attribute_id']);
        }
        return self::create([
            'items_name' => $data['name'] ? $data['name'] : '',
            'items_code' => !empty($data['code']) ? $data['code'] : '',
            'status' => !empty($data['status']) ? $data['status'] : '',
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
            'description' => !empty($data['description']) ? $data['description'] : '',
            'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : '',
            'sort' => !empty($data['sort']) ? $data['sort'] : '',
            'image' => !empty($data['image']) ? $data['image'] : '',
            'create_on' => date('Y-m-d H:i:s'),
            'modify_on' => date('Y-m-d H:i:s'),
            'is_deleted' => 0,
            'attribute_id' => !empty($attribute) ? $attribute : '',
        ]);
    }

    public static function itemsEdit($data)
    {
        if (!empty($data['attribute_id'])) {
            $attribute = implode(',', $data['attribute_id']);
        }
        self::where(['id' => $data['id']])->update([
            'items_name' => $data['name'] ? $data['name'] : '',
            'items_code' => !empty($data['code']) ? $data['code'] : '',
            'status' => !empty($data['status']) ? $data['status'] : '',
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
            'description' => !empty($data['description']) ? $data['description'] : '',
            'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : '',
            'sort' => !empty($data['sort']) ? $data['sort'] : '',
            'image' => !empty($data['image']) ? $data['image'] : '',
            'modify_on' => date('Y-m-d H:i:s'),
            'is_deleted' => 0,
            'attribute_id' => !empty($attribute) ? $attribute : '',
        ]);
        return true;
    }

    public static function search($paginate, $param = null)
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
            $searchParam['query']['content'] = $param['content'];
            $list = self::where($where)
                ->where('items_name', 'like', '%' . $param['content'] . '%')
                ->order('parent_id,id', 'desc')
                ->paginate($paginate, false, $searchParam);
        } else {
            $list = self::where($where)
                ->order('parent_id,id', 'desc')
                ->paginate($paginate, false, $searchParam);
        }

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                if ($val['parent_id'] == 0) {
                    $list[$key]['parent_name'] = '顶级类目';
                } else {
                    $parentName = self::where(['id' => $val['parent_id']])->find();
                    $list[$key]['parent_name'] = $parentName['items_name'];
                }
            }
        }
        return $list;
    }
}
