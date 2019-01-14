<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\mall\controller;

use app\base\controller\Base;
use think\Request;
use app\service\mall\ProductSerivce;
use app\component\MyHelper;

/**
 * Description of Permissions
 *
 * @author Administrator
 */
class Product extends Base
{

    protected $moduleView = 'mall';

    /* items action  start*/
    public function items(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['parent', 'status', 'content'], $searchParam);
        $parent = ProductSerivce::instance()->parentItems();
        $list = ProductSerivce::instance()->ItemsList($searchParam);
        $this->assign('list', $list);
        $this->assign('parent', $parent);
        $this->assign('search', $searchParam);
        return view($this->viewPath('items/items'));
    }

    public function addItems(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            ProductSerivce::instance()->addItems($param);
            $this->layMsg('添加成功', 6);
            exit;
        }
        $attribute = ProductSerivce::instance()->getAttributeList();
        $parent = ProductSerivce::instance()->parentItems();
        $this->assign('parent_items', $parent);
        $this->assign('attribute', $attribute);
        return view($this->viewPath('items/add'));
    }

    public function deleteditems(Request $request)
    {
        $param = $request->param();
        ProductSerivce::instance()->deleteditems($param);
        return json_encode(['code' => 10000, 'msg' => 'success']);
    }

    public function itemsedit(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            ProductSerivce::instance()->itemsEdit($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $detail = ProductSerivce::instance()->itemsDetail($param);
        $attribute = ProductSerivce::instance()->selectAttributeList($detail['attribute_id']);
        $parent = ProductSerivce::instance()->parentItems();
        $this->assign('parent_items', $parent);
        $this->assign('detail', $detail);
        $this->assign('attribute', $attribute);
        return view($this->viewPath('items/edit'));
    }
    /* items action  end*/


    /* product action start*/
    public function productlist(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['items', 'status', 'is_deal', 'content'], $searchParam);
        $itemsList = ProductSerivce::instance()->itemsListForAll();
        $list = ProductSerivce::instance()->productList($searchParam);
        $this->assign('items_list', $itemsList);
        $this->assign('list', $list);
        $this->assign('search', $searchParam);
        return view($this->viewPath('product/list'));
    }

    public function addproduct(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            ProductSerivce::instance()->addProduct($param);
            $this->layMsg('添加成功', 6);
            exit;
        }
        $itemsList = ProductSerivce::instance()->itemsListForAll();
        if (!empty($param['itemsid'])) {
            $itemsId = $param['itemsid'];
        } else {
            $itemsId = $itemsList[0]['id'];
        }
        $selectAttr = ProductSerivce::instance()->getAttributeFromItems($itemsId);
        $this->assign('attr_list', $selectAttr);
        $this->assign('items_list', $itemsList);
        $this->assign('items_id', $itemsId);
        $this->assign('plu', date('Ymd') . MyHelper::uuid(8));
        return view($this->viewPath('product/add'));
    }

    public function deletedproduct(Request $request)
    {
        $param = $request->param();
        ProductSerivce::instance()->deletedProduct($param);
        return json_encode(['code' => 10000, 'msg' => 'success']);
    }

    public function productedit(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            ProductSerivce::instance()->productEdit($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $detail = ProductSerivce::instance()->detailProduct($param);
        $itemsList = ProductSerivce::instance()->productSelectItems($detail['items_id']);
        $selectAttr = ProductSerivce::instance()->selectAttributeFromItems($detail['items_id'], $param['id']);
        $this->assign('attr_list', $selectAttr);
        $this->assign('items_list', $itemsList);
        $this->assign('detail', $detail);
        return view($this->viewPath('product/edit'));
    }
    /* product action end*/


    /* attribute action start*/
    public function attribute(Request $request)
    {
        $searchParam = $request->param();
        $searchParam = MyHelper::dealSearchParam(['parent', 'status', 'content'], $searchParam);
        $list = ProductSerivce::instance()->attributeSearch($searchParam);
        $parent = ProductSerivce::instance()->attributeParent();
        $this->assign('list', $list);
        $this->assign('parent', $parent);
        $this->assign('search', $searchParam);
        return view($this->viewPath('attribute/list'));
    }

    public function attributeadd(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'add') {
            ProductSerivce::instance()->attributeAdd($param);
            $this->layMsg('添加成功', 6);
            exit;
        }
        $parentAttribute = ProductSerivce::instance()->attributeParent();
        $this->assign('parent_attribute', $parentAttribute);
        return view($this->viewPath('attribute/add'));
    }

    public function deletedattr(Request $request)
    {
        $param = $request->param();
        ProductSerivce::instance()->deletedAttr($param);
        return json_encode(['code' => 10000, 'msg' => 'success']);
    }

    public function attredit(Request $request)
    {
        $param = $request->param();
        if (!empty($param['action']) && $param['action'] == 'edit') {
            ProductSerivce::instance()->attriEdit($param);
            $this->layMsg('修改成功', 6);
            exit;
        }
        $detail = ProductSerivce::instance()->attrDetail($param);
        $parentAttribute = ProductSerivce::instance()->selectParentAttr($detail['attribute_id']);
        $this->assign('parent_attribute', $parentAttribute);
        $this->assign('detail', $detail);
        return view($this->viewPath('attribute/edit'));
    }

    /* attribute action end*/
    public function productAttrHtml(Request $request)
    {
        $param = $request->param();
        $selectAttr = ProductSerivce::instance()->getAttributeFromItems($param['itesms_id']);
        $html = ProductSerivce::instance()->productAttrHtml($selectAttr);
        print_r($html);
        echo $html['price'];
    }
}
