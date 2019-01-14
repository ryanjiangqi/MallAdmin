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

class UserAttendance extends Model
{
    public static function userAttendanceExist($userInfo, $date)
    {
        $data = self::where(['is_deleted' => 0])
            ->where(['user_id' => $userInfo['id']])
            ->where('attenddance_date', 'like', '%' . $date . '%')
            ->select()->toArray();
        return $data;
    }

    public static function todayAttendance($userInfo, $date, $attendanceType)
    {
        self::create([
            'user_id' => $userInfo['id'],
            'status' => '打卡成功',
            'attenddance_date' => $date,
            'attendance_type' => $attendanceType,
            'leave_date' => '',
            'create_on' => date('Y-m-d H:i:s'),
            'is_deleted' => '0',
        ]);
    }

    public static function search($paginage, $param)
    {
        $searchParam = [];
        $where['ua.is_deleted'] = 0;
        if (!empty($param['status'])) {
            $where['ua.status'] = $param['status'];
            $searchParam['query']['status'] = $param['status'];
        }
        $query = Db::table('user_attendance');
        $query->field('ua.*,u.user_code,u.username,u.real_name');
        $query->alias('ua');
        $query->join('user u', 'ua.user_id = u.id');
        $query->where($where);
        if (!empty($param['content'])) {
            $searchParam['query']['content'] = $param['content'];
            $query->where('u.user_code|u.username|u.real_name|u.mobile', 'like', '%' . $param['content'] . '%');
        }
        $info = $query->paginate($paginage, false, $searchParam);
        return $info;
    }
}
