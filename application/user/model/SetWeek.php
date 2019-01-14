<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\model;

use think\Model;
use think\Db;
use think\helper;
use app\user\model\User;

class SetWeek extends Model
{
    public static function setworkList()
    {
        return Db::table('set_week')
            ->field('sw.*,u.real_name,u.mobile')
            ->alias('sw')
            ->join('user u', 'sw.user_id = u.id')
            ->where(['sw.is_deleted' => 0])
            ->select();
    }

    public static function setworkHistoryList($paginate)
    {
        return self::where(['is_deleted' => 1])->paginate($paginate);
    }

    public static function availableEmployees()
    {
        return User::where(['is_deleted' => 0])->select()->toArray();
    }

    public static function manual($data)
    {
        return self::where(['id' => $data['id']])->update([
            'user_id' => !empty($data['user_new_id']) ? $data['user_new_id'] : '',
            'is_update' => '已调整',
            'is_update_by' => !empty($data['is_update_by']) ? $data['is_update_by'] : '',
        ]);
    }
}
