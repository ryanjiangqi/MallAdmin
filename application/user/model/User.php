<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\model;

use think\Model;
use think\Db;
use app\component\MyHelper;
use app\user\model\UserAttendance;
use app\user\model\SetWeek;

class User extends Model
{
    public static function search($paginate, $param = null)
    {
        $searchParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
            $searchParam['query']['status'] = $param['status'];
        }
        if (!empty($param['content'])) {
            $searchParam['query']['content'] = $param['content'];
            $list = self::where($where)
                ->where('username|real_name|mobile|user_code', 'like', '%' . $param['content'] . '%')
                ->order('id', 'desc')
                ->paginate($paginate, false, $searchParam);
        } else {
            $list = self::where($where)
                ->order('id', 'desc')
                ->paginate($paginate, false, $searchParam);
        }
        return $list;
    }

    public static function userAdd($data)
    {
        if (!empty($data['idcardimage'])) {
            $idCardImages = implode(',', $data['idcardimage']);
        }
        self::create([
            'user_code' => date('Ymd') . MyHelper::uuid(8),
            'username' => !empty($data['username']) ? $data['username'] : '',
            'nick_name' => !empty($data['nick_name']) ? $data['nick_name'] : '',
            'password' => !empty($data['password']) ? md5($data['password']) : md5('123456'),
            'permissions_group_id' => !empty($data['permissions_group_id']) ? $data['permissions_group_id'] : '',
            'email' => !empty($data['email']) ? $data['email'] : '',
            'mobile' => !empty($data['mobile']) ? $data['mobile'] : '',
            'id_card' => !empty($data['id_card']) ? $data['id_card'] : '',
            'id_card_image' => !empty($idCardImages) ? $idCardImages : '',
            'head_image' => !empty($data['head_image']) ? $data['head_image'] : '',
            'real_name' => !empty($data['real_name']) ? $data['real_name'] : '',
            'province' => !empty($data['province']) ? $data['province'] : '',
            'city' => !empty($data['city']) ? $data['city'] : '',
            'district' => !empty($data['district']) ? $data['district'] : '',
            'address' => !empty($data['address']) ? $data['address'] : '',
            'create_on' => !empty($data['create_on']) ? $data['create_on'] : date('Y-m-d H:i:s'),
            'modify_on' => !empty($data['modify_on']) ? $data['modify_on'] : date('Y-m-d H:i:s'),
            'is_deleted' => !empty($data['is_deleted']) ? $data['is_deleted'] : 0,
            'pay' => !empty($data['pay']) ? $data['pay'] : '',
            'note' => !empty($data['note']) ? $data['note'] : 0,
            'status' => !empty($data['status']) ? $data['status'] : '未激活',
        ]);
        return true;
    }

    public static function userEdit($data)
    {
        if (!empty($data['idcardimage'])) {
            $idCardImages = implode(',', $data['idcardimage']);
        }
        self::where(['id' => $data['id']])->update([
            'username' => !empty($data['username']) ? $data['username'] : '',
            'nick_name' => !empty($data['nick_name']) ? $data['nick_name'] : '',
            'permissions_group_id' => !empty($data['permissions_group_id']) ? $data['permissions_group_id'] : '',
            'email' => !empty($data['email']) ? $data['email'] : '',
            'mobile' => !empty($data['mobile']) ? $data['mobile'] : '',
            'id_card' => !empty($data['id_card']) ? $data['id_card'] : '',
            'id_card_image' => !empty($idCardImages) ? $idCardImages : '',
            'head_image' => !empty($data['head_image']) ? $data['head_image'] : '',
            'real_name' => !empty($data['real_name']) ? $data['real_name'] : '',
            'province' => !empty($data['province']) ? $data['province'] : '',
            'city' => !empty($data['city']) ? $data['city'] : '',
            'district' => !empty($data['district']) ? $data['district'] : '',
            'address' => !empty($data['address']) ? $data['address'] : '',
            'modify_on' => !empty($data['modify_on']) ? $data['modify_on'] : date('Y-m-d H:i:s'),
            'is_deleted' => !empty($data['is_deleted']) ? $data['is_deleted'] : 0,
            'pay' => !empty($data['pay']) ? $data['pay'] : '',
            'note' => !empty($data['note']) ? $data['note'] : 0,
            'status' => !empty($data['status']) ? $data['status'] : '未激活',
        ]);

    }

    public static function searchAttendance($paginate, $param = null)
    {
        return Db::table('user_attendance')
            ->field('ua.*,u.user_code,u.username,u.real_name,w.name as work_name')
            ->alias('ua')
            ->join('user u', 'ua.user_id = u.id')
            ->join('work_type w', 'w.id = ua.attendance_type')
            ->paginate($paginate);
    }
}
