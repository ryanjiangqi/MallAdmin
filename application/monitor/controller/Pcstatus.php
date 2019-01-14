<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/9
 * Time: 0:23
 */

namespace app\monitor\controller;

use app\base\controller\Base;
use app\component\MyHelper;
use think\Request;
use app\service\monitor\PcSerivce;

class Pcstatus extends Base
{
    protected $moduleView = 'monitor';

    public function pc(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['status', 'content'], $searchParam);
        $list = PcSerivce::instance()->search($searchParam);
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        return view($this->viewPath('pcstatus/list'));
    }

    public function addpc(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            PcSerivce::instance()->addPc($param);
            $this->layMsg('添加成功', 6);
            exit;
        }
        return view($this->viewPath('pcstatus/addpc'));
    }
}
