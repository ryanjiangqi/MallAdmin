<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service\monitor;

use think\Model;
use think\Db;
use app\service\BaseSerivce;
use think\helper;
use app\monitor\model\PcStatus;

class PcSerivce extends BaseSerivce
{
    public function search($searchParam)
    {
        return PcStatus::search(self::PAGINATE,$searchParam);
    }

    public function addPc($data)
    {
        PcStatus::addPc($data);
        return true;
    }

}
