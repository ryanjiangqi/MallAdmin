<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\mall\model;

use think\Model;
use think\Db;
use app\component\MyHelper;
use think\helper;

class Product extends Model
{
    public static function addProduct($data)
    {
        if (!empty($data['image'])) {
            $imageList = implode(',', $data['image']);
            $coverImage = $data['image'][0];
        }
        $productId = self::create([
            'name' => !empty($data['name']) ? $data['name'] : '',
            'plu' => !empty($data['plu']) ? $data['plu'] : date('YmdH') . MyHelper::uuid(),
            'items_id' => !empty($data['items']) ? $data['items'] : '',
            'status' => !empty($data['status']) ? $data['status'] : '',
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
            'discount_price' => !empty($data['discount_price']) ? $data['discount_price'] : '',
            'price' => !empty($data['product_price']) ? $data['product_price'] : '',
            'num' => !empty($data['num']) ? $data['num'] : '',
            'image' => !empty($imageList) ? $imageList : '',
            'sort' => !empty($data['sort']) ? $data['sort'] : '',
            'is_sale' => !empty($data['is_sale']) ? $data['is_sale'] : '',
            'is_hot' => !empty($data['is_hot']) ? $data['is_hot'] : '',
            'is_new' => !empty($data['is_new']) ? $data['is_new'] : '',
            'create_on' => date('Y-m-d H:i:s'),
            'cover_image' => !empty($coverImage) ? $coverImage : '',
        ]);
        //$productId->id
        if (!empty($data['ex_attribute_id'])) {
            foreach ($data['ex_attribute_id'] as $key => $val) {
                foreach ($val as $keys => $vals) {
                    ProductExattribute::create([
                        'product_id' => $productId->id,
                        'ex_attribute_id' => $key,
                        'ex_attribute_value' => $vals,
                        'create_on' => date('Y-m-d:H:i:s'),
                        'is_deleted' => 0,
                    ]);
                }
            }
        }
        if (!empty($data['sku'])) {
            foreach ($data['sku'] as $keyt => $valt) {
                ProductSku::create([
                    'product_id' => $productId->id,
                    'sku' => $valt,
                    'attribute_id' => $data['attribute_list_id'][$keyt],
                    'price' => $data['price'][$keyt],
                    'is_deleted' => 0,
                    'create_on' => date("Y-m-d H:i:s"),
                ]);
            }
        }
        return true;
    }

    public static function search($paginate, $param)
    {
        $searchParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['items'])) {
            $where['items_id'] = $param['items'];
            $searchParam['query']['items'] = $param['items'];
        }
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
            $searchParam['query']['status'] = $param['status'];
        }
        if (!empty($param['is_deal'])) {
            if ($param['is_deal'] == 1) {
                $where['is_hot'] = 1;
            }
            if ($param['is_deal'] == 2) {
                $where['is_sale'] = 1;
            }
            if ($param['is_deal'] == 3) {
                $where['is_new'] = 1;
            }
            $searchParam['query']['is_deal'] = $param['is_deal'];
        }
        if (!empty($param['content'])) {
            $searchParam['query']['content'] = $param['content'];
            $list = self::where($where)
                ->order('id', 'desc')
                ->where('name|plu', 'like', '%' . $param['content'] . '%')
                ->paginate($paginate, false, $searchParam);
        } else {
            $list = self::where($where)
                ->order('id', 'desc')
                ->paginate($paginate, false, $searchParam);
        }
        return $list;
    }

    public static function productEdit($data)
    {
        if (!empty($data['image'])) {
            $imageList = implode(',', $data['image']);
            $coverImage = $data['image'][0];
        }
        //获取已经存在sku的图片
        $sku = ProductSku::where(['product_id' => $data['id']])->select()->toArray();

        foreach ($sku as $key => $value) {
            foreach ($data['sku'] as $k => $v) {
                if($value['sku']==$v){
                    $data['sku_image'][$k]=$value['image'];
                }
            }
        }

        self::where(['id' => $data['id']])->update([
            'name' => !empty($data['name']) ? $data['name'] : '',
            'plu' => !empty($data['plu']) ? $data['plu'] : date('YmdH') . MyHelper::uuid(),
            'items_id' => !empty($data['items']) ? $data['items'] : '',
            'status' => !empty($data['status']) ? $data['status'] : '',
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
            'discount_price' => !empty($data['discount_price']) ? $data['discount_price'] : '',
            'price' => !empty($data['product_price']) ? $data['product_price'] : '',
            'num' => !empty($data['num']) ? $data['num'] : '',
            'image' => !empty($imageList) ? $imageList : '',
            'sort' => !empty($data['sort']) ? $data['sort'] : '',
            'is_sale' => !empty($data['is_sale']) ? $data['is_sale'] : '',
            'is_hot' => !empty($data['is_hot']) ? $data['is_hot'] : '',
            'is_new' => !empty($data['is_new']) ? $data['is_new'] : '',
            'cover_image' => !empty($coverImage) ? $coverImage : '',
        ]);
        ProductExattribute::destroy(['product_id' => $data['id']]);
        ProductSku::destroy(['product_id' => $data['id']]);
        if (!empty($data['ex_attribute_id'])) {
            foreach ($data['ex_attribute_id'] as $key => $val) {
                foreach ($val as $keys => $vals) {
                    ProductExattribute::create([
                        'product_id' => $data['id'],
                        'ex_attribute_id' => $key,
                        'ex_attribute_value' => $vals,
                        'create_on' => date('Y-m-d:H:i:s'),
                        'is_deleted' => 0,
                    ]);
                }
            }
        }
        if (!empty($data['sku'])) {
            foreach ($data['sku'] as $keyt => $valt) {
                ProductSku::create([
                    'product_id' => $data['id'],
                    'sku' => $valt,
                    'attribute_id' => $data['attribute_list_id'][$keyt],
                    'price' => $data['price'][$keyt],
                    'is_deleted' => 0,
                    'create_on' => date("Y-m-d H:i:s"),
                ]);
            }
        }
    }

    public static function attrDetail($productId)
    {
        $info = ProductSku::where(['product_id' => $productId])->select()->toArray();
        if (!empty($info)) {
            foreach ($info as $key => $val) {
                $data = PAttribute::whereIn('id', explode(',', $val['attribute_id']))->select()->toArray();
                $info[$key]['attri_name'] = '';
                foreach ($data as $keyT => $valT) {
                    $info[$key]['attri_name'] .= '&nbsp;&nbsp;' . $valT['attribute_value'];
                }
            }
        }
        return $info;
    }
}
