<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/6
 * Time: 22:18
 */

namespace app\component;


class MyHelper
{
    public static function uuid($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    public static function emptyHelper($value = null, $defaultVaule = null)
    {
        $result = (isset($value) && !empty($value)) ? $value : $defaultVaule;
        return $result;
    }

    public static function dealSearchParam($initParam, $searchParam)
    {
        if (!empty($initParam)) {
            foreach ($initParam as $key => $value) {
                if (empty($searchParam[$value])) {
                    $searchParam[$value] = '';
                }
            }
        }
        return $searchParam;
    }

    public static function  uniqueRand($min,$max,$num){
        $count = 0;
        $return_arr = array();
        while($count < $num){
            $return_arr[] = mt_rand($min,$max);
            $return_arr = array_flip(array_flip($return_arr));
            $count = count($return_arr);
        }
        shuffle($return_arr);
        return $return_arr;
    }

    public static function customerCardId($length=9){
        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }
}