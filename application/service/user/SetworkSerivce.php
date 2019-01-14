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
use app\user\model\SetWeek;
use app\service\BaseSerivce;
use think\Session;

class SetworkSerivce extends BaseSerivce
{
    public function setworkList()
    {
        $list = SetWeek::setworkList();
        return $list;
    }

    public function setworkHistoryList()
    {
        $list = SetWeek::setworkHistoryList(self::PAGINATE);
        foreach ($list as $key => $val) {
            $list[$key]['data'] = json_decode($val['week_data'], true);
        }
        return $list;
    }

    /**
     * @desc 平均排班算法，每周排班一次
     */
    public function schedulingAlg()
    {
        $employees = SetWeek::availableEmployees();
    }

    public function availableEmployees()
    {
        return SetWeek::availableEmployees();
    }

    public function detail($data)
    {
        return SetWeek::where(['id' => $data['id']])->find()->toArray();
    }

    public function manual($data)
    {
        return SetWeek::manual($data);
    }

}
