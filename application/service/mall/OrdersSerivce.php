<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service\mall;

use think\Model;
use think\Db;
use app\service\BaseSerivce;
use app\mall\model\PItems;
use app\mall\model\Product;
use app\mall\model\Orders;
use app\mall\model\OrdersProduct;
use think\helper;

class OrdersSerivce extends BaseSerivce
{
    public function ordersList($searchParam)
    {
        return Orders::search(self::PAGINATE,$searchParam);
    }

    public function updateStatus($data)
    {
        return Orders::where('id', $data['id'])->update(['payment_status' => $data['status']]);
    }

    public function updateOrderStatus($data)
    {
        return Orders::where('id', $data['id'])->update(['orders_status' => $data['status']]);
    }

    public function orderDetail($data)
    {
        $detail = orders::detail($data['id']);
        return $detail;
    }


}
