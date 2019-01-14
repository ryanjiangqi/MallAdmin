<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\base\model;

use think\Model;
use think\Db;


class Base extends Model
{
    public static function imageList($paginate)
    {
        return Db::table('fileresources')->order('id','desc')->paginate($paginate);
    }
}
