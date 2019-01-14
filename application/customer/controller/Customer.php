<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\customer\controller;

use app\component\MyHelper;
use think\Request;
use app\base\controller\Base;
use app\service\customer\CustomerSerivce;

class Customer extends Base
{

    protected $moduleView = 'customer';

    public function customerlist(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['status', 'content'], $searchParam);
        $list = CustomerSerivce::instance()->customerList($searchParam);
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        return view($this->viewPath('customer/list'));
    }

    public function addcustomer(Request $request)
    {
        $param = $request->param();
        //初始用户名和密码
        $userName=MyHelper::customerCardId();
        $password=88888888;
        if (!empty($param['action']) && $param['action'] == 'add') {
            CustomerSerivce::instance()->addCustomer($param);
            $this->layMsg('添加成功', 6);
            exit;
        }
        $this->assign('user_name',$userName);
        $this->assign('password',$password);
        return view($this->viewPath('customer/add'));
    }

    public function changestatus(Request $request)
    {
        $data = $request->param();
        CustomerSerivce::instance()->updateStatus($data['id'], $data['status']);
        return ['code' => 10000, 'msg' => 'success'];
    }

    public function detail(Request $request)
    {
        $data = $request->param();
        $detail = CustomerSerivce::instance()->customerDetail($data);
        $detailConsume = CustomerSerivce::instance()->customerConsume($data);
        $this->assign('detail', $detail);
        $this->assign('detail_consume', $detailConsume);
        return view($this->viewPath('customer/detail'));
    }

    public function enteridcard(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'idcard') {
            CustomerSerivce::instance()->updateIdCard($param);
            $this->layMsg('录入成功', 6);
            exit;
        }
        $detail = CustomerSerivce::instance()->customerDetail($param);
        $this->assign('detail', $detail);
        return view($this->viewPath('customer/idcard'));
    }
}
