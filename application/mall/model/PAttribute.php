<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\mall\model;

use think\Model;
use think\Db;
use think\helper;
use app\mall\model\PItems;
use app\mall\model\Product;

class PAttribute extends Model
{
    public static function search($paginate, $param = null)
    {
        $searchParam = [];
        $where['is_deleted'] = 0;
        if (!empty($param['parent'])) {
            $where['attribute_id'] = $param['parent'];
            $searchParam['query']['parent'] = $param['parent'];
        }
        if (!empty($param['status'])) {
            $where['status'] = $param['status'];
            $searchParam['query']['status'] = $param['status'];
        }
        if (!empty($param['content'])) {
            $pageParam['query']['content'] = $param['content'];
            $list = self::where($where)
                ->where('attribute_value', 'like', '%' . $param['content'] . '%')
                ->order('attribute_id', 'asc')
                ->paginate($paginate, false, $searchParam);
        } else {
            $list = self::where($where)
                ->order('attribute_id', 'asc')
                ->paginate($paginate, false, $searchParam);
        }


        if (!empty($list)) {
            foreach ($list as $key => $value) {
                if ($value['attribute_id'] == 0) {
                    $list[$key]['parent_name'] = '顶级属性';
                } else {
                    $parentInfo = self::where(['id' => $value['attribute_id']])->find();
                    $list[$key]['parent_name'] = $parentInfo['attribute_value'];
                }
            }
        }
        return $list;
    }

    public static function PAttribute($data)
    {
        $addData = [
            'attribute_id' => !empty($data['attribute_id']) ? $data['attribute_id'] : '',
            'sort' => !empty($data['sort']) ? $data['sort'] : '0',
            'attribute_value' => !empty($data['attribute_value']) ? $data['attribute_value'] : '',
            'create_on' => !empty($data['create_on']) ? $data['create_on'] : date('Y-m-d H:s:i'),
            'is_deleted' => !empty($data['is_deleted']) ? $data['is_deleted'] : '0',
            'type' => !empty($data['type']) ? $data['type'] : '二级属性',
            'price_type' => !empty($data['price_type']) ? $data['price_type'] : '二级属性',
            'image' => !empty($data['image']) ? $data['image'] : '',
            'status' => !empty($data['status']) ? $data['status'] : '发布',
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
        ];
        if ($addData['attribute_id'] != 0) {
            $addData['type'] = '二级属性';
            $addData['price_type'] = '二级属性';
        }
        return self::create($addData);
    }

    public static function getAttributeList()
    {
        $parent = self::where(['is_deleted' => 0, 'attribute_id' => 0])->select()->toArray();
        if (!empty($parent)) {
            foreach ($parent as $key => $value) {
                $child = self::where(['is_deleted' => 0, 'attribute_id' => $value['id']])->select()->toArray();
                if (!empty($child)) {
                    $parent[$key]['child'] = $child;
                } else {
                    $parent[$key]['child'] = '';
                }
            }
        }
        return $parent;
    }

    public static function selectAttributeList($attributeId)
    {
        $attributeArray = [];
        if (!empty($attributeId)) {
            $attributeArray = explode(',', $attributeId);
        }
        $parent = self::where(['is_deleted' => 0, 'attribute_id' => 0])->select()->toArray();
        if (!empty($parent)) {
            foreach ($parent as $key => $value) {
                if (in_array($value['id'], $attributeArray)) {
                    $parent[$key]['checked'] = 'checked';
                } else {
                    $parent[$key]['checked'] = '';
                }

                $child = self::where(['is_deleted' => 0, 'attribute_id' => $value['id']])->select()->toArray();
                if (!empty($child)) {
                    $parent[$key]['child'] = $child;
                } else {
                    $parent[$key]['child'] = '';
                }
            }
        }
        return $parent;
    }

    public static function getAttributeFromItems($itemsId)
    {
        $itemsInfo = PItems::where(['id' => $itemsId])->find()->toArray();
        if (!empty($itemsInfo['attribute_id'])) {
            $attrArray = explode(',', $itemsInfo['attribute_id']);
            foreach ($attrArray as $key => $value) {
                $parent = self::where(['id' => $value])->find()->toArray();
                if (!empty($parent)) {
                    foreach ($parent as $key2 => $value2) {
                        $child = self::where(['attribute_id' => $value])->select()->toArray();
                        if (!empty($child)) {
                            $parent['child'] = $child;
                        } else {
                            $parent['child'] = '';
                        }
                    }
                }
                $attrArray[$key] = $parent;
            }
        } else {
            $attrArray = [];
        }
        return $attrArray;
    }

    public static function selectParentAttr($attributeId)
    {
        $parent = self::where(['attribute_id' => 0])->select()->toArray();
        if (!empty($parent)) {
            foreach ($parent as $key => $value) {
                if ($attributeId == $value['id']) {
                    $parent[$key]['selected'] = 'selected';
                } else {
                    $parent[$key]['selected'] = '';
                }
            }
        }
        return $parent;
    }

    public static function updateAttr($data)
    {
        $dataEdit = [
            'attribute_id' => !empty($data['attribute_id']) ? $data['attribute_id'] : '',
            'sort' => !empty($data['sort']) ? $data['sort'] : '0',
            'attribute_value' => !empty($data['attribute_value']) ? $data['attribute_value'] : '',
            'is_deleted' => !empty($data['is_deleted']) ? $data['is_deleted'] : '0',
            'type' => !empty($data['type']) ? $data['type'] : '二级属性',
            'price_type' => !empty($data['price_type']) ? $data['price_type'] : '二级属性',
            'image' => !empty($data['image']) ? $data['image'] : '',
            'status' => !empty($data['status']) ? $data['status'] : '发布',
            'keyword' => !empty($data['keyword']) ? $data['keyword'] : '',
        ];
        if ($dataEdit['attribute_id'] != 0) {
            $dataEdit['type'] = '二级属性';
            $dataEdit['price_type'] = '二级属性';
        }
        self::where(['id' => $data['id']])
            ->update($dataEdit);
        return true;
    }

    public static function selectAttributeFromItems($data, $productId)
    {
        $attrNew = [];
        $exAttr = [];
        $info = ProductExattribute::where(['product_id' => $productId])->select()->toArray();
        if (!empty($info)) {
            foreach ($info as $key => $val) {
                $exAttr[] = $val['ex_attribute_value'];
            }
        }
        $Attrinfo = ProductSku::where(['product_id' => $productId])->select()->toArray();
        if (!empty($Attrinfo)) {
            foreach ($Attrinfo as $keys => $vals) {
                $attr = explode(',', $vals['attribute_id']);
                if (!empty($attr)) {
                    foreach ($attr as $keyT => $valT) {
                        $attrNew[] = $valT;
                    }
                }
            }
        }
        foreach ($data as $keyW => $valW) {
            if (!empty($valW['child'])) {
                foreach ($valW['child'] as $keyU => $vlaUs) {
                    if ($valW['price_type'] == '关联价格' && in_array($vlaUs['id'], $attrNew)) {
                        $data[$keyW]['child'][$keyU]['checked'] = 'checked';
                        $data[$keyW]['child'][$keyU]['selected'] = 'selected';
                    }
                    if ($valW['price_type'] == '关联价格' && !in_array($vlaUs['id'], $attrNew)) {
                        $data[$keyW]['child'][$keyU]['checked'] = '';
                        $data[$keyW]['child'][$keyU]['selected'] = '';
                    }

                    if ($valW['price_type'] == '不关联价格' && in_array($vlaUs['id'], $exAttr)) {
                        $data[$keyW]['child'][$keyU]['checked'] = 'checked';
                        $data[$keyW]['child'][$keyU]['selected'] = 'selected';
                    }
                    if ($valW['price_type'] == '不关联价格' && !in_array($vlaUs['id'], $exAttr)) {
                        $data[$keyW]['child'][$keyU]['checked'] = '';
                        $data[$keyW]['child'][$keyU]['selected'] = '';
                    }
                }
            }
        }
        return $data;
    }
}
