<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-06-21
 * Time: 11:34
 */

namespace app\system\controller;


use app\base\controller\Base;
use app\service\system\SetSerivce;
use think\Request;

class Set extends Base
{
    protected $moduleView = 'system';

    public function set()
    {
        $option = SetSerivce::instance()->optionList();
        $this->assign('option', $option);
        return view($this->viewPath('set/set'));
    }

    public function edit(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            SetSerivce::instance()->editSet($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $option = SetSerivce::instance()->optionList();
        $this->assign('option', $option);
        return view($this->viewPath('set/edit'));
    }

    public function building()
    {
        return view($this->viewPath('set/building'));
    }
}