<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\mall\model;

use think\Model;
use think\Db;
use app\mall\model\OrdersProduct;

class Orders extends Model
{
    public static function search($paginage, $param = null)
    {
        $searchParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['is_deal'])) {
            $where['is_deal'] = $param['is_deal'];
            $searchParam['query']['is_deal'] = $param['is_deal'];
        }

        if (!empty($param['order_status'])) {
            $where['order_status'] = $param['order_status'];
            $searchParam['query']['order_status'] = $param['order_status'];
        }
        if (!empty($param['content'])) {
            $searchParam['query']['content'] = $param['content'];
            $orderLists = self::where($where)
                ->where('orders_sn|nick_name|mobile', 'like', '%' . $param['content'] . '%')
                ->paginate($paginage, false, $searchParam);
        } else {
            $orderLists = self::where($where)->order('id', 'desc')->paginate($paginage, false, $searchParam);
        }
        if (!empty($orderLists)) {
            foreach ($orderLists as $key => $val) {
                $orderProduct = OrdersProduct::where(['orders_id' => $val['id']])->select()->toArray();
                $orderLists[$key]['orders_product'] = $orderProduct;
            }
        }
        return $orderLists;
    }

    public static function detail($orderId)
    {

        $detail = Db::table('orders')
            ->field('o.*,co.user_name')
            ->alias('o')
            ->join('customer co', 'o.customer_id = co.id')
            ->where(['o.id' => $orderId])
            ->find();
        $product = OrdersProduct::where(['orders_id' => $orderId])->select()->toArray();
        $detail['product'] = $product;
        return $detail;
    }
}
