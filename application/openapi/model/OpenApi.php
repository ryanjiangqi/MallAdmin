<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/28 0028
 * Time: 下午 8:44
 */

namespace app\openapi\model;
use app\customer\model\Customer;
use app\customer\model\CustomerAccount;
use think\Model;
use think\Db;
use think\helper;
use app\component\MyHelper;

class OpenApi extends Model
{
  public static function  addWxCustomer($userInfo)
  {
      Db::transaction(function () use ($userInfo) {
          $member_card = MyHelper::uniqueRand(100000000,999999999,9);
         $customer = Customer::create( [
              "openid" => $userInfo["openId"],
              "unionid" => isset($userInfo["unionId"]) ? $userInfo["unionId"] : "",
              "source" => "微信",
              "avatarUrl" => $userInfo["avatarUrl"],
              "city" => $userInfo["city"],
              "country" => $userInfo["country"],
              "province" => $userInfo["province"],
              "gender" => $userInfo["gender"],
              "nick_name" => $userInfo["nickName"],
              "create_date" => date("Y-m-d H:i:s"),
              "user_name" => $member_card[0],
              "password" => md5('88888888'),
          ]);

         CustomerAccount::create([
              'customer_id' => $customer->id,
              'amount' => '0',
              'create_on' =>  date('Y-m-d H:i:s'),
              'is_deleted' =>  '0',
              'modify_on' => date('Y-m-d H:i:s'),
              'login_status' => '未登录',
          ]);
      });
  }
}