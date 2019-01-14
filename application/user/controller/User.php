<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\user\controller;

use app\base\controller\Base;
use app\component\MyHelper;
use app\service\user\UserSerivce;
use app\service\user\SetworkSerivce;
use app\service\system\PermissionsSerivce;
use think\Request;

class User extends Base
{

    protected $moduleView = 'user';

    public function userList(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['status', 'content'], $searchParam);
        $list = UserSerivce::instance()->userList($searchParam);
        $this->assign('search', $searchParam);
        $this->assign('list', $list);
        return view($this->viewPath('user/list'));
    }

    public function changestatus(Request $request)
    {
        $data = $request->param();
        UserSerivce::instance()->updateStatus($data['id'], $data['status']);
        return ['code' => 10000, 'msg' => 'success'];
    }

    public function userAdd(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            UserSerivce::instance()->userAdd($param);
            $this->layMsg('添加成功', 6);
        }
        $this->assign('grouplist', PermissionsSerivce::instance()->allSearch());
        return view($this->viewPath('user/add'));
    }

    public function deleteuser(Request $request)
    {
        $param = $request->param();
        UserSerivce::instance()->deleteUser($param);
        return json_encode(['code' => 10000, 'msg' => 'success']);
    }

    public function useredit(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            UserSerivce::instance()->userEdit($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $detail = UserSerivce::instance()->detailUser($param);
        $this->assign('grouplist', PermissionsSerivce::instance()->allSearch());
        $this->assign('detail', $detail);
        return view($this->viewPath('user/edit'));
    }

    public function attendance(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['status', 'content'], $searchParam);
        $list = UserSerivce::instance()->attendanceList($searchParam);
        $userAttendance = UserSerivce::instance()->userAttendanceExist();
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        $this->assign('attendance_exist', $userAttendance);
        return view($this->viewPath('attendance/list'));
    }

    public function setwork()
    {
        $setList = SetworkSerivce::instance()->setworkList();
        $setworkHistoryList = SetworkSerivce::instance()->setworkHistoryList();
        $this->assign('setlist', $setList);
        $this->assign('history_list', $setworkHistoryList);
        return view($this->viewPath('attendance/weekwork'));
    }

    public function todayattendance(Request $request)
    {
        $param = $request->param();
        if (!empty($param['att_type'])) {
            UserSerivce::instance()->todayAttendance($param);
            return json_encode(['code' => 10000, 'msg' => 'success']);
        }
    }

    public function manual(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            SetworkSerivce::instance()->manual($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $user = SetworkSerivce::instance()->availableEmployees();
        $detail = SetworkSerivce::instance()->detail($param);
        $this->assign('user_id', $param['user_id']);
        $this->assign('user', $user);
        $this->assign('detail', $detail);
        return view($this->viewPath('attendance/manual'));
    }

    public function test()
    {
        SetworkSerivce::instance()->schedulingAlg();
    }
}
