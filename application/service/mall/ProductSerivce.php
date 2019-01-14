<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\service\mall;

use think\Model;
use think\Db;
use app\service\BaseSerivce;
use app\mall\model\PItems;
use app\mall\model\Product;
use app\mall\model\PAttribute;
use app\mall\model\ProductExattribute;
use app\mall\model\ProductSku;
use think\helper;

class ProductSerivce extends BaseSerivce
{

    public function productList($searchParam)
    {
        return Product::search(self::PAGINATE, $searchParam);
    }

    public function ItemsList($searchParam)
    {
        return PItems::search(self::PAGINATE, $searchParam);
    }

    public function parentItems()
    {
        return PItems::where(['is_deleted' => 0, 'parent_id' => 0])->order('id', 'desc')->select()->toArray();
    }

    public function itemsListForAll()
    {
        $parent = $this->parentItems();
        if (!empty($parent)) {
            foreach ($parent as $key => $val) {
                $chlid = PItems::where(['is_deleted' => 0, 'parent_id' => $val['id']])->order('id', 'desc')->select()->toArray();
                $parent[$key]['child'] = $chlid;
            }
        }
        return $parent;
    }

    public function addItems($data)
    {
        return PItems::addItems($data);
    }

    public function addProduct($data)
    {
        return Product::addProduct($data);
    }


    public function deleteditems($data)
    {
        PItems::destroy(['id' => $data['id']]);
        return true;
    }

    public function itemsDetail($data)
    {
        return PItems::where(['id' => $data['id']])->find();
    }

    public function itemsEdit($data)
    {
        PItems::itemsEdit($data);
        return true;
    }

    public function deletedProduct($data)
    {
        Product::destroy(['id' => $data['id']]);
        ProductExattribute::destroy(['product_id' => $data['id']]);
        ProductSku::destroy(['product_id' => $data['id']]);
        return true;
    }

    public function detailProduct($data)
    {
        $detail = Product::where(['id' => $data['id']])->find()->toArray();
        if (!empty($detail['image'])) {
            $img = explode(',', $detail['image']);
            $detail['img_array'] = $img;
        } else {
            $detail['img_array'] = [];
        }
        $attr = Product::attrDetail($data['id']);
        $detail['sku'] = $attr;
        return $detail;
    }

    public function productSelectItems($itemsId)
    {
        $initItems = $this->itemsListForAll();
        foreach ($initItems as $key => $val) {
            if ($val['id'] == $itemsId) {
                $initItems[$key]['selected'] = 'selected';
            } else {
                $initItems[$key]['selected'] = '';
            }
            foreach ($val['child'] as $keyT => $valT) {
                if ($valT['id'] == $itemsId) {
                    $initItems[$key]['child'][$keyT]['selected'] = 'selected';
                } else {
                    $initItems[$key]['child'][$keyT]['selected'] = '';
                }
            }
        }
        return $initItems;
    }

    public function productEdit($data)
    {
        Product::productEdit($data);
        return true;
    }

    public function attributeSearch($searchParam)
    {
        return PAttribute::search(self::PAGINATE, $searchParam);
    }

    public function attributeParent()
    {
        return PAttribute::where(['attribute_id' => 0])->select();
    }

    public function attributeAdd($data)
    {
        return PAttribute::PAttribute($data);
    }

    public function getAttributeList()
    {
        return PAttribute::getAttributeList();
    }

    public function getAttributeFromItems($itemsId)
    {
        return PAttribute::getAttributeFromItems($itemsId);
    }

    public function deletedAttr($data)
    {
        PAttribute::destroy(['id' => $data['id']]);
        return true;
    }

    public function attrDetail($data)
    {
        return PAttribute::where(['id' => $data['id']])->find()->toArray();
    }

    public function selectParentAttr($id)
    {
        return PAttribute::selectParentAttr($id);
    }

    public function selectAttributeList($attributeId)
    {
        return PAttribute::selectAttributeList($attributeId);
    }

    public function attriEdit($data)
    {
        PAttribute::updateAttr($data);
        return true;
    }

    public function selectAttributeFromItems($itemsId, $productId)
    {
        $data = PAttribute::getAttributeFromItems($itemsId);
        return PAttribute::selectAttributeFromItems($data, $productId);
    }


}
