<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\service;
use think\helper ;

class BaseOpenApiService {
    public static function instance($parameter = null)
    {
        $className = get_called_class();
        return new $className($parameter);
    }
    //校验用户身份
    protected function authorization(){
        
    }
}