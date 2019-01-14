<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service\customer;

use think\Model;
use think\Db;
use app\service\BaseSerivce;
use think\helper;
use app\customer\model\Customer;

class CustomerSerivce extends BaseSerivce
{


    public function customerList($searchParam)
    {
        return Customer::search(self::PAGINATE,$searchParam);
    }

    public function updateStatus($customerId, $status)
    {
        return Customer::where('id', $customerId)->update(['status' => $status]);
    }

    public function customerDetail($data)
    {
        return Customer::customerDetail($data);
    }

    public function customerConsume($data)
    {
        return Customer::customerConsume($data, self::PAGINATE);
    }
    public function addCustomer($data){
        return Customer::addCustomer($data);
    }
    public function updateIdCard($data){
        return Customer::updateIdCard($data);
    }

}
