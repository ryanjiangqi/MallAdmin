<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\mall\controller;

use app\base\controller\Base;
use app\component\MyHelper;
use think\Request;
use app\service\mall\OrdersSerivce;

/**
 * Description of Permissions
 *
 * @author Administrator
 */
class Orders extends Base
{

    protected $moduleView = 'mall';

    public function orderslist(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['is_deal', 'order_status', 'content'], $searchParam);
        $list = OrdersSerivce::instance()->ordersList($searchParam);
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        return view($this->viewPath('orders/list'));
    }

    public function updatestatus(Request $request)
    {
        $param = $request->param();
        OrdersSerivce::instance()->updateStatus($param);
        return json_encode(['code' => '10000', 'msg' => 'success']);
    }

    public function updateorderstatus(Request $request)
    {
        $param = $request->param();
        OrdersSerivce::instance()->updateOrderStatus($param);
        return json_encode(['code' => '10000', 'msg' => 'success']);
    }

    public function orderdetail(Request $request)
    {
        $param = $request->param();
        $detail = OrdersSerivce::instance()->orderDetail($param);
        $this->assign('detail', $detail);
        return view($this->viewPath('orders/detail'));
    }
}
