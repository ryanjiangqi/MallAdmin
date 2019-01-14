<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\system\model;

use think\Model;
use think\Db;
USE think\helper;

class SystemSet extends Model
{
    public static function editSet($data)
    {
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                self::where(['option_code' => $key])->update([
                    'option_value' => !empty($val) ? $val : '',
                    'modify_on' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        return true;
    }
}
