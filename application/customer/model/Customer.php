<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\customer\model;

use think\Model;
use think\Db;
use app\customer\model\CustomerConsume;
use app\customer\model\CustomerAccount;
use think\helper;

class Customer extends Model
{

    public static function search($paginate, $param = [])
    {
        $searchParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
            $searchParam['query']['status'] = $param['status'];
        }
        if (!empty($param['content'])) {
            $list = self::where($where)
                ->where('user_name|id_card|mobile|real_name', 'like', '%' . $param['content'] . '%')
                ->paginate($paginate, false, $searchParam);
        } else {
            $list = self::where($where)
                ->paginate($paginate, false, $searchParam);
        }
        foreach ($list as $key => $value) {
            $account = CustomerAccount::where(['customer_id' => $value['id']])->find()->toArray();
            $list[$key]['login_status'] = $account['login_status'];
        }
        return $list;
    }

    public static function customerDetail($data)
    {
        $customerInfo = Customer::where(['id' => $data['id']])->find()->toArray();
        $account = CustomerAccount::where(['customer_id' => $data['id']])->find()->toArray();
        $customerInfo['account'] = $account;
        return $customerInfo;
    }

    public static function customerConsume($data, $paginate)
    {
        $consume = CustomerConsume::where(['customer_id' => $data['id']])->order('id', 'desc')->paginate($paginate);
        return $consume;
    }

    public static function addCustomer($data)
    {
        Db::transaction(function () use ($data) {
            $customer = self::create([
                'user_name' => !empty($data['user_name']) ? $data['user_name'] : '',
                'password' => !empty($data['password']) ? md5($data['password']) : '',
                'nick_name' => !empty($data['nick_name']) ? $data['nick_name'] : '',
                'status' => !empty($data['status']) ? $data['status'] : '未激活',
                'head_image' => !empty($data['head_image']) ? $data['head_image'] : '',
                'create_date' => !empty($data['create_date']) ? $data['create_date'] : date('Y-m-d H:i:s'),
                'real_name' => !empty($data['real_name']) ? $data['real_name'] : '',
                'id_card' => !empty($data['id_card']) ? $data['id_card'] : '',
                'mobile' => !empty($data['mobile']) ? $data['mobile'] : '',
                'is_deleted' => !empty($data['is_deleted']) ? $data['is_deleted'] : '0',
                'email' => !empty($data['email']) ? $data['email'] : '',
                'last_login' => !empty($data['last_login']) ? $data['last_login'] : date('Y-m-d H:i:s'),
            ]);
            CustomerAccount::create([
                'customer_id' => $customer->id,
                'amount' => !empty($data['amount']) ? $data['amount'] : '0',
                'create_on' => !empty($data['create_on']) ? $data['create_on'] : date('Y-m-d H:i:s'),
                'is_deleted' => !empty($data['is_deleted']) ? $data['is_deleted'] : '0',
                'modify_on' => !empty($data['modify_on']) ? $data['modify_on'] : date('Y-m-d H:i:s'),
                'login_status' => !empty($data['login_status']) ? $data['login_status'] : '未登录',
            ]);
        });
    }

    public static function updateIdCard($data)
    {
        return self::where(['id' => $data['id']])->update([
            'id_card' => !empty($data['id_card']) ? $data['id_card'] : '',
            'real_name' => !empty($data['real_name']) ? $data['real_name'] : '',
        ]);
    }
}
