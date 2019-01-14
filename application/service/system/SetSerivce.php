<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service\system;

use app\system\model\SystemSet;
use think\Model;
use think\Db;
use app\service\BaseSerivce;

class SetSerivce extends BaseSerivce
{
    public function optionList()
    {
        $optionCode = ['hour_fee', 'remind_fee', 'count_fee_time'];
        $query = SystemSet::where(['is_deleted' => '0']);
        foreach ($optionCode as $key => $values) {
            if ($key == 0) {
                $query->where(['option_code' => $values]);
            } else {
                $query->whereOr('option_code', '=', $values);
            }
        }
        $info = $query->select()->toArray();
        if (!empty($info)) {
            foreach ($info as $key2 => $value2) {
                $result[$value2['option_code']] = $value2;
            }
        }
        return $result;
    }

    public function editSet($data)
    {
        unset($data['action']);
        return SystemSet::editSet($data);
    }
}
