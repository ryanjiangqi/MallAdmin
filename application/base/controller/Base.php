<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\base\controller;

use think\Controller;
use think\Session;
use think\Request;
use think\Db;
use think\Url;
use app\service\BaseSerivce;
use think\Config;
use app\service\socket\socketService;

/**
 * Description of comm
 *
 * @author RyanJiang
 */
class Base extends Controller
{

    protected $menList;
    protected $useInfo;
    protected $moduleView;
    protected $beforeActionList = [
        'auth',
    ];

    protected function viewPath($views)
    {
        return Config::get('viewpath') . $this->moduleView . '/' . $views;
    }

    private function getMenu($groupId)
    {
        if ($groupId != 0) {
            $menu = BaseSerivce::getPermissionsMenu($groupId);
        } else {
            $menu = BaseSerivce::getAllMenu();
        }
        return $menu;
    }

    private function checkSession()
    {
        $userInfo = Session::get('user_info');
        if (empty($userInfo)) {
            $this->assign('url', Url::build('home/login/login', ''));
            echo $this->fetch(Config::get('viewpath') . 'comm/reload');
        }
        return $userInfo;
    }

    private function checkPermissions($groupId)
    {
        return BaseSerivce::checkPermissions($groupId);
    }

    protected function auth()
    {
        $this->useInfo = Session::get('user_info');
        $userInfo = $this->checkSession();
        //获取权限菜单
        $this->menList = $this->getMenu($userInfo['permissions_group_id']);
        //检测权限
        $bool = $this->checkPermissions($userInfo['permissions_group_id']);
        if (!$bool) {
            echo '无权限';
            exit;
        }
        return true;
    }

    public function layMsg($msg, $icoNum, $showTime = 1000, $reload = true)
    {
        $this->assign('msg', $msg);
        $this->assign('iconum', $icoNum);
        $this->assign('showtime', $showTime);
        $this->assign('reload', $reload);
        echo $this->fetch(Config::get('viewpath') . 'comm/msg');
    }

    public function uploadimage()
    {
        $result = BaseSerivce::uploadImage($_FILES);
        //echo json_encode(array('code' => 200, 'src' => '/static/uploadfile/' . $result));
        echo json_encode(array('code' => 200, 'src' => $result));
    }

    public function uploadimageview(Request $request)
    {
        $param = $request->param();
        $list = BaseSerivce::getImageList();
        $this->assign('list', $list);
        $this->assign('parentid', $param['parentid']);
        $this->assign('num', $param['num']);
        $this->assign('name', urldecode($param['name']));
        return view(Config::get('viewpath') . 'comm/uploadimg');
    }

    public function uploadimageedit()
    {
        $result = BaseSerivce::uploadImage($_FILES);
        //echo json_encode(array('code' => 200, 'src' => '/static/uploadfile/' . $result));
        $data = [
            'code' => '0',
            'msg' => 'success',
            'data' => [
                'src' => 'http://'.$_SERVER['HTTP_HOST'].'/static/uploadfile/' . $result,
                'title' => $result,
            ]
        ];
        echo json_encode($data);
    }
}
