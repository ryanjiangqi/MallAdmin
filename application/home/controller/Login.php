<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\home\controller;

use think\Request;
use think\Controller;
use think\Session;
use think\Url;
use think\Config;
use app\service\user\UserSerivce;

class Login extends controller
{

    public function loginSuccess()
    {
        $this->assign('url', Url::build('/', ''));
        echo $this->fetch(Config::get('viewpath') . '/comm/reload');
    }

    public function login(Request $request)
    {
        $data = $request->param();
        $userInfo = Session::get('user_info');
        if (!empty($userInfo)) {
            $this->loginSuccess();
        }
        $msg = null;
        if (!empty($data['username']) && !empty($data['password'])) {
            $result = UserSerivce::instance()->checkLogin($data['username'], $data['password']);
            if ($result['code'] == '1000') {
                $this->loginSuccess();
            }
            $msg = $result['msg'];
        }
        $this->assign('msg', $msg);
        return view(Config::get('viewpath') . 'home/index/login');
    }

    public function layout()
    {
        Session::clear();
        $this->assign('url', Url::build('home/login/login', ''));
        echo $this->fetch(Config::get('viewpath') . '/comm/reload');
    }

}
