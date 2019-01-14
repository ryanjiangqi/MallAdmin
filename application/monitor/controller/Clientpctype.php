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
use app\service\monitor\ClientPcTypeService;

class Clientpctype extends Base
{
    protected $moduleView = 'monitor';

    public function clientpctypelist(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam([], $searchParam);
        $list = ClientPcTypeService::instance()->search($searchParam);
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        return view($this->viewPath('pctype/list'));
    }

    public function addclientpctype(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            ClientPcTypeService::instance()->addClientPcType($param);
            $this->layMsg('添加成功', 6);
            exit;
        }
        return view($this->viewPath('pctype/addPcType'));
    }
}
