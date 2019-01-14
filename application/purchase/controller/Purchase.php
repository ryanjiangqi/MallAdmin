<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\purchase\controller;

use app\base\controller\Base;
use app\component\MyHelper;
use think\Request;
use app\service\purchase\PurchaseSerivce;

/**
 * Description of Permissions
 *
 * @author Administrator
 */
class Purchase extends Base
{

    protected $moduleView = 'purchase';

    public function itemslist(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['parent', 'status', 'content'], $searchParam);
        $parentItems = PurchaseSerivce::instance()->itemsParent();
        $list = PurchaseSerivce::instance()->itemsList($searchParam);
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        $this->assign('items_list', $parentItems);
        return view($this->viewPath('items/items'));
    }

    public function additems(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            PurchaseSerivce::instance()->addItems($param);
            $this->layMsg('添加成功', 6);
        }
        $parentItems = PurchaseSerivce::instance()->itemsParent();
        $this->assign('items_list', $parentItems);
        return view($this->viewPath('items/add'));
    }

    public function purchaselist(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['purchase_items', 'status', 'content'], $searchParam);
        $list = PurchaseSerivce::instance()->purchaseList($searchParam);
        $itemsList = PurchaseSerivce::instance()->itemsListForALL();
        $this->assign('items_list', $itemsList);
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        return view($this->viewPath('purchase/list'));
    }

    public function purchaseadd(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            PurchaseSerivce::instance()->addPurchase($param);
            $this->layMsg('添加成功', 6);
        }
        $itemsList = PurchaseSerivce::instance()->itemsListForALL();
        $this->assign('items_list', $itemsList);
        return view($this->viewPath('purchase/add'));
    }

    public function deleteditems(Request $request)
    {
        $param = $request->param();
        PurchaseSerivce::instance()->deleteditems($param);
        return json_encode(['code' => 10000, 'msg' => 'success']);
    }

    public function itemsedit(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            PurchaseSerivce::instance()->itemsEdit($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $detail = PurchaseSerivce::instance()->detailItems($param);
        $parentItems = PurchaseSerivce::instance()->selectItemsParent($detail['parent_id']);
        $this->assign('detail', $detail);
        $this->assign('items_list', $parentItems);
        return view($this->viewPath('items/edit'));
    }

    public function deletedpurchase(Request $request)
    {
        $param = $request->param();
        PurchaseSerivce::instance()->deletedPurchase($param);
        return json_encode(['code' => 10000, 'msg' => 'success']);
    }

    public function purchaseedit(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            PurchaseSerivce::instance()->purchaseEdit($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $detail = PurchaseSerivce::instance()->detailPurchase($param);
        $itemsList = PurchaseSerivce::instance()->selectItemsListForALL($detail['purchase_items']);
        $this->assign('items_list', $itemsList);
        $this->assign('detail', $detail);
        return view($this->viewPath('purchase/edit'));
    }
}
