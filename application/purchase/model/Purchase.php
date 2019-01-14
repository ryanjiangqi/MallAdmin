<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\purchase\model;

use think\Model;
use think\Db;
use app\component\Helper;

class Purchase extends Model
{
    public static function search($paginate, $param = null)
    {
        $searchParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['purchase_items'])) {
            $where['purchase_items'] = $param['purchase_items'];
            $searchParam['query']['purchase_items'] = $param['purchase_items'];
        }
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
            $searchParam['query']['status'] = $param['status'];
        }
        if (!empty($param['content'])) {
            $searchParam['query']['content'] = $param['content'];
            $info = self::where($where)
                ->where('name|approve_by', 'like', '%' . $param['content'] . '%')
                ->order('id', 'desc')
                ->paginate($paginate, false, $searchParam);
        } else {
            $info = self::where($where)
                ->order('id', 'desc')
                ->paginate($paginate, false, $searchParam);
        }


        return $info;
    }

    public static function addPurchase($data)
    {
        Purchase::create([
            'purchase_items' => !empty($data['purchase_items']) ? $data['purchase_items'] : '',
            'name' => !empty($data['name']) ? $data['name'] : '',
            'num' => !empty($data['num']) ? $data['num'] : 0,
            'apply_date' => !empty($data['apply_date']) ? $data['apply_date'] : date('Y-m-d H:i:s'),
            'status' => !empty($data['status']) ? $data['status'] : '不发布',
            'apply_status' => !empty($data['apply_status']) ? $data['apply_status'] : '审核中',
            'complete_date' => !empty($data['complete_date']) ? $data['complete_date'] : null,
            'approve_date' => !empty($data['approve_date']) ? $data['approve_date'] : null,
            'approve_by' => !empty($data['approve_by']) ? $data['approve_by'] : '',
            'apply_name' => !empty($data['apply_name']) ? $data['apply_name'] : '',
            'create_on' => !empty($data['create_on']) ? $data['create_on'] : date('Y-m-d H:i:s'),
            'note' => !empty($data['note']) ? $data['note'] : '',
            'image' => !empty($data['image']) ? $data['image'] : '',
            'is_deleted' => !empty($data['is_deleted']) ? $data['is_deleted'] : 0,
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
        ]);
        return true;
    }

    public static function purchaseEdit($data)
    {
        self::where(['id' => $data['id']])->update([
            'purchase_items' => !empty($data['purchase_items']) ? $data['purchase_items'] : '',
            'name' => !empty($data['name']) ? $data['name'] : '',
            'num' => !empty($data['num']) ? $data['num'] : 0,
            'apply_date' => !empty($data['apply_date']) ? $data['apply_date'] : date('Y-m-d H:i:s'),
            'status' => !empty($data['status']) ? $data['status'] : '不发布',
            'apply_status' => !empty($data['apply_status']) ? $data['apply_status'] : '审核中',
            'complete_date' => !empty($data['complete_date']) ? $data['complete_date'] : null,
            'approve_date' => !empty($data['approve_date']) ? $data['approve_date'] : null,
            'approve_by' => !empty($data['approve_by']) ? $data['approve_by'] : '',
            'apply_name' => !empty($data['apply_name']) ? $data['apply_name'] : '',
            'note' => !empty($data['note']) ? $data['note'] : '',
            'image' => !empty($data['image']) ? $data['image'] : '',
            'is_deleted' => !empty($data['is_deleted']) ? $data['is_deleted'] : 0,
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
        ]);
        return true;
    }


}
