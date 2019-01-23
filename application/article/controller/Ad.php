<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\article\controller;

use app\base\controller\Base;
use app\component\MyHelper;
use think\Request;
use app\article\model\Ad as AdModel;

/**
 * Description of Permissions
 *
 * @author Administrator
 */
class Ad extends Base
{
    protected $moduleView = 'article';

    public function banner()
    {
        $list = AdModel::where(['status' => '上架', 'type' => '首页banner图'])->select();
        $this->assign('list', $list);
        return view($this->viewPath('ad/list'));

    }

    public function addbanner(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            AdModel::create([
                'type' => $param['type'] ?? '',
                'title' => $param['title'] ?? '',
                'image' => $param['image'] ?? '',
                'url' => $param['url'] ?? '',
                'keyword' => $param['keyword'] ?? '',
                'status' => $param['status'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'sort' => $param['sort'] ?? 0,
            ]);
            $this->layMsg('添加成功', 6);
            exit;
        }
        return view($this->viewPath('ad/add'));
    }

    public function editbanner(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            AdModel::where(['id' => $param['id']])->update([
                'type' => $param['type'] ?? '',
                'title' => $param['title'] ?? '',
                'image' => $param['image'] ?? '',
                'url' => $param['url'] ?? '',
                'keyword' => $param['keyword'] ?? '',
                'status' => $param['status'] ?? '',
                'sort' => $param['sort'] ?? 0,
            ]);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $detail = AdModel::where(['id' => $param['id']])->find()->toArray();
        $this->assign('detail', $detail);
        return view($this->viewPath('ad/edit'));
    }

}
