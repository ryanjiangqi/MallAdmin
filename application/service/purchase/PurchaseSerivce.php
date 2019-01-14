<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service\purchase;

use think\Model;
use think\Db;
use app\service\BaseSerivce;
use app\purchase\model\PurchaseItems;
use app\purchase\model\Purchase;
use think\helper;

class PurchaseSerivce extends BaseSerivce
{
    public function itemsList($searchParam)
    {
        return PurchaseItems::search(self::PAGINATE, $searchParam);
    }

    public function itemsParent()
    {
        return PurchaseItems::where(['is_deleted' => 0, 'parent_id' => 0])->order('id', 'desc')->select()->toArray();
    }

    public function selectItemsParent($itemsId)
    {
        $parentList = PurchaseItems::where(['is_deleted' => 0, 'parent_id' => 0])->order('id', 'desc')->select()->toArray();
        if (!empty($parentList)) {
            foreach ($parentList as $key => $val) {
                if ($val['id'] == $itemsId) {
                    $parentList[$key]['selected'] = 'selected';
                } else {
                    $parentList[$key]['selected'] = '';
                }
            }
        }
        return $parentList;
    }

    public function addItems($data)
    {
        return PurchaseItems::addItems($data);
    }

    public function detailItems($data)
    {
        return PurchaseItems::where(['id' => $data['id']])->find()->toArray();
    }

    public function deleteditems($data)
    {
        PurchaseItems::destroy(['id' => $data['id']]);
        return true;
    }

    public function itemsEdit($data)
    {
        PurchaseItems::itemsEdit($data);
        return true;
    }

    public function purchaseList($searchParam)
    {
        return Purchase::search(self::PAGINATE, $searchParam);
    }

    public function itemsListForALL()
    {
        $parent = $this->itemsParent();
        if (!empty($parent)) {
            foreach ($parent as $key => $val) {
                $chlid = PurchaseItems::where(['is_deleted' => 0, 'parent_id' => $val['id']])->order('id', 'desc')->select()->toArray();
                $parent[$key]['child'] = $chlid;
            }
        }
        return $parent;
    }

    public function addPurchase($data)
    {
        return Purchase::addPurchase($data);
    }

    public function deletedPurchase($data)
    {
        Purchase::destroy(['id' => $data['id']]);
        return true;
    }

    public function selectItemsListForALL($purchaseItemsId)
    {
        $parent = $this->itemsParent();
        if (!empty($parent)) {
            foreach ($parent as $key => $val) {
                if ($val['id'] == $purchaseItemsId) {
                    $parent[$key]['selected'] = 'selected';
                } else {
                    $parent[$key]['selected'] = '';
                }
                $chlid = PurchaseItems::where(['is_deleted' => 0, 'parent_id' => $val['id']])->order('id', 'desc')->select()->toArray();
                if (!empty($chlid)) {
                    foreach ($chlid as $key2 => $value2) {
                        if ($value2['id'] == $purchaseItemsId) {
                            $chlid[$key2]['selected'] = 'selected';
                        } else {
                            $chlid[$key2]['selected'] = '';
                        }
                    }
                }
                $parent[$key]['child'] = $chlid;
            }
        }
        return $parent;
    }

    public function detailPurchase($data)
    {
        return Purchase::where(['id' => $data['id']])->find()->toArray();
    }

    public function purchaseEdit($data)
    {
        Purchase::purchaseEdit($data);
        return true;
    }
}
