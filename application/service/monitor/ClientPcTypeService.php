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
use app\monitor\model\ClientPcType;

class ClientPcTypeService extends BaseSerivce
{
    public function search($searchParam)
    {
        return ClientPcType::search(self::PAGINATE,$searchParam);
    }

    public function addClientPcType($data)
    {
        ClientPcType::addClientPcType($data);
        return true;
    }

}
