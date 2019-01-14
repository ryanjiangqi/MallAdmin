<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\home\controller;

use app\base\controller\Base;

class Index extends Base
{

    protected $moduleView = 'home';

    public function index()
    {
        $this->assign('user_info', $this->useInfo);
        $this->assign('menu', $this->menList);
        return view($this->viewPath('index/index'));
    }

    public function welcome()
    {
        return view($this->viewPath('index/welcome'));
    }

}
