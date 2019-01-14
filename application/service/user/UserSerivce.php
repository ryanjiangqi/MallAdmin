<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service\user;

use app\user\model\UserAttendance;
use think\Model;
use think\Db;
use app\user\model\User;
use app\service\BaseSerivce;
use think\Session;

class UserSerivce extends BaseSerivce
{
    public function userList($searchParam)
    {
        return User::search(self::PAGINATE, $searchParam);
    }

    public function checkLogin($username, $password)
    {
        $checkUsername = User::where(['username' => $username])->find();
        $checkAll = User::where(['username' => $username, 'password' => md5($password)])->find();
        if (empty($checkUsername)) {
            return ['code' => 1001, 'msg' => '用户名不存在'];
        }
        if (empty($checkAll)) {
            return ['code' => 1002, 'msg' => '密码错误'];
        }
        $userInfo['id'] = $checkAll['id'];
        $userInfo['username'] = $checkAll['username'];
        $userInfo['permissions_group_id'] = $checkAll['permissions_group_id'];
        $userInfo['nick_name'] = $checkAll['nick_name'];
        $userInfo['merchant_id'] = $checkAll['merchant_id'];
        Session::set('user_info', $userInfo);
        return ['code' => 1000, 'msg' => '登录成功'];
    }

    public function userAdd($data)
    {
        return User::userAdd($data);
    }

    public function deleteUser($data)
    {
        User::destroy(['id' => $data['id']]);
        return true;
    }

    public function detailUser($data)
    {
        $detail = User::where(['id' => $data['id']])->find()->toArray();
        if (!empty($detail['id_card_image'])) {
            $detail['id_card_image_array'] = explode(',', $detail['id_card_image']);
        } else {
            $detail['id_card_image_array'] = '';
        }
        return $detail;
    }

    public function userEdit($data)
    {
        return User::userEdit($data);
    }

    public function attendanceList($param)
    {
        return UserAttendance::search(self::PAGINATE, $param);
    }

    public function userAttendanceExist()
    {
        $userInfo = Session::get('user_info');
        $nowDate = date('Y-m-d');
        $deital = UserAttendance::userAttendanceExist($userInfo, $nowDate);
        if (empty($deital)) {
            return '';
        } else {
            return $deital;
        }
    }

    public function todayAttendance($param)
    {
        $userInfo = Session::get('user_info');
        $nowDate = date('Y-m-d H:i:s');
        UserAttendance::todayAttendance($userInfo, $nowDate, $param['att_type']);
        return true;
    }

    public function setworkList()
    {
        $list = User::setworkList();
        foreach ($list as $key => $val) {
            $list[$key]['data'] = json_decode($val['week_data'], true);
        }
        return $list;
    }

    public function setworkHistoryList()
    {
        $list = User::setworkHistoryList(self::PAGINATE);
        foreach ($list as $key => $val) {
            $list[$key]['data'] = json_decode($val['week_data'], true);
        }
        return $list;
    }

    public function updateStatus($userId, $status)
    {
        return User::where('id', $userId)->update(['status' => $status]);
    }

}
