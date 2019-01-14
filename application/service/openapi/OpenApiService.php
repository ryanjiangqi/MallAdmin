<?php

/* 
 *errorCode
 * 0 success
 * 1000 错误接口信息
 * 1001 未开通该接口
 * 10000 用户不存在
 * 10001 购物车为空
 * 10002 用户不存在或未激活
 * 10003 商品已在购物车中
 * 10004 无效信息
 * 10005 超过可购买最大数量
 * 10006 下单失败
 * 10007 查询失败,请稍后再试
 * 10010 未找到相关记录
 * 10011 验证手机号
 * 10012 更新失败请稍后再试
 * 10014 账号未激活
 * 10015 账号已冻结
 */
namespace app\service\openapi ;
use app\service\BaseOpenApiService ;
use app\openapi\model\OpenApi;
use app\mall\model\Product;
use app\component\MyHelper;
use think\Model;
use think\Db;
use think\helper;
use think\db\exception;
use app\service\openapi\DecryptedService;


/************************************
 * $requestData   格式
 * ['m' =>方法，
 * ['t'] =>table,
 * ['o']=>order by ,
 *
 ************************************/

class OpenApiService extends BaseOpenApiService{
    
    private $apiMethod =["delCartItems","sessionInfo","userPhone","updateInfo","chooseSku","getOrdersInfo","getRecord","product" , "categories" ,"shoppingMsg" ,"attribute","carts" ,"getCarts" ,"editCarts","orders"];
    private $errMsg  = [];
    private $table="" ;
    private $sort="";
    private $userInfo ;
    private $appId ="wx78925e9724368618";
    private $appSecret = "2a91608a9da3f2e102af412abfd4f28f";
    
    public function apiRoute($requestData)
    {
        if(empty($requestData))
        {
            $this->errMsg["code"] = 1000;
            $this->errMsg["msg"]  = "错误接口信息！";
            return  $this->errMsg;
        }
        if(!in_array($requestData["m"],$this->apiMethod)){
            $this->errMsg["code"] = 1001;
            $this->errMsg["msg"]  = "未开通该接口！";
            return  $this->errMsg;
        }
        //验证token
        if(empty($requestData["token"]))
        {
            return ["code"=>10004,"errMsg"=>"无效信息"];
        }
        $userInfo = $this->checkSession($requestData["token"]);
        if(empty($userInfo))
        {
            return ["code"=>10002,"errMsg"=>"用户不存在或未激活"];
        }
        if($userInfo[0]["status"]=="已冻结")
        {
            return ["code"=>10015,"errMsg"=>"账号已冻结"];
        }
        $this->userInfo=$userInfo;
        $this->table =!empty($requestData["t"])?$requestData["t"]:"";
        $method = $requestData["m"];
        return $this->$method($requestData);
    }
    //检测用户session ，session 暂时存储在数据库后期再做优化
    private function checkSession($token){
        $query=Product::table("customer");
        return   $query->where(["token"=>$token,"is_deleted"=>0])->select()->toArray();

    }
    //结算
    public function orders($requestData)
    {

        $userInfo = $this->checkSession($requestData["token"]);

        if(empty($userInfo))
        {
            return ["code"=>10002,"errMsg"=>"用户不存在或未激活"];
        }
        $cartIdArr = json_decode($requestData["cart_id"]);
//        $cartIdArr = [44];
        $productInfo = $this->getPrice($cartIdArr,$userInfo[0]["id"]);
//        print_r($productInfo);die;
        $random  = mt_rand(10000,99999);
        $orderSn =date("YmdHis").$random;
        $orders=array();
        $orders["merchant_id"]=$userInfo[0]["merchant_id"];
        $orders["orders_sn"]=$orderSn;
        $orders["create_date"]=date("Y-m-d H:i:s");
        $orders["pay_type"]="微信";
        $orders["order_status"]="未支付";
        $orders["customer_id"]=$userInfo[0]["id"];
        $orders["nick_name"]=$userInfo[0]["nick_name"];
        $orders["mobile"]=$userInfo[0]["mobile"];
        $orders["pay_amount"]=0;
        $orders["pay_status"]="未付款";
        $orders["discount"]=$productInfo["discount"];
        $orders["amount"]=  $productInfo["amount"];
//        print_r($orders);die;
        //orders
        $order_id= $this->addtoDb("orders",$orders,"getId");
        //orderProducts
        $orderProducts = $productInfo["order_product"];
        foreach($orderProducts as $k=>$v){
            $orderProducts[$k]["orders_id"]=$order_id;
            $orderProducts[$k]["orders_sn"]=$orderSn;
        }
        $orpId =$this->addtoDb("orders_product",$orderProducts,"insertAll");
        if($order_id&&$orpId)
        {
            //清空当前购买产品
            $this->emptyCurrentProduct($userInfo[0]["id"],$cartIdArr);
            return ["code"=>0,"order_sn"=>$orderSn,"amount"=>$orders["amount"]];
        }
        return ["code"=>10006,"errMsg"=>"下单失败"];

    }
    public function delCartItems($requestData)
    {
       $customerId = $this->userInfo[0]["id"];
      if(!empty($requestData["delArr"])){

          $this->emptyCurrentProduct($customerId,json_decode($requestData["delArr"],true));
      }
    }
    //删除当前购买产品
    private function emptyCurrentProduct($customerId , $cartArr)
    {

        Db::startTrans();
        try{
            Db::table("carts")->delete($cartArr);

            Db::commit();
        } catch (\Exception $e)
        {
            echo $e->getMessage();
            Db::rollback();
        }
    }

    /**
     * @param $table
     * @param $data
     * @param $type getId , insertAll
     * @return int|string
     */
    private function addtoDb($table,$data,$type)
    {
        $id="";
        Db::startTrans();
        try{
            if($type =="getId")
            {
                $id = Db::table($table)->insertGetId($data);
            }
            if($type == "insertAll")
            {
                $id = Db::table($table)->insertAll($data);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
             $e->getMessage();

            Db::rollback();
        }
        return $id;
    }
        //获取商品总价 折扣价
        private function getPrice($cart , $customerId)
        {

            $priceDet = [];
            $cartInfo =[];
            $orderProduct=[];
            $total = 0;
            $totalDiscount =0;
            foreach($cart as $k=>$v)
            {
                $cartInfo[] =Product::table("carts")
                    ->where(["id"=>$v,"customer_id"=>$customerId])->find()->toArray();
            }

            if(!empty($cartInfo))
            {
                foreach($cartInfo as $k=>$v){
                    //商品存在sku 拿sku价格不 存在拿Plu 价格
                    $skuId = $v["sku_id"];
                    $productId = $v["product_id"];
                    $productInfo = Product::table("product")
                        ->where(["id"=>$productId])->find()->toArray();
                    $productName = $productInfo["name"];
                    if(!empty($skuId))
                    {
                        $skuInfo = Product::table("product_sku")
                            ->where(["id"=>$skuId,"product_id"=>$productId])->find()->toArray();
                        $orderProduct[$k]["product_id"]=$v["product_id"];
                        $orderProduct[$k]["sku"]=$skuInfo["sku"];
                        $orderProduct[$k]["product_name"]=$productName;
                        $orderProduct[$k]["price"]=$skuInfo["price"];
                        $orderProduct[$k]["num"]=$v["num"];
                        $discount = $skuInfo["discount_price"]==0 ? 0 : ($skuInfo["price"]-$skuInfo["discount_price"])*$v["num"];
                        $orderProduct[$k]["discount"]=$discount;
                        $orderProduct[$k]["real_price"]=$skuInfo["price"];
                        $orderProduct[$k]["create_on"]=date("Y-m-d H:i:s");
                        $orderProduct[$k]["is_deleted"]=0;
                        $orderProduct[$k]["attribute_list"]=$skuInfo["attribute_id"];
                        $totalDiscount+=$discount;
                        $total+=$orderProduct[$k]["price"]*$orderProduct[$k]["num"];
                    }else{
                        $orderProduct[$k]["product_id"]=$v["product_id"];
                        $orderProduct[$k]["sku"]="";
                        $orderProduct[$k]["product_name"]=$productName;
                        $orderProduct[$k]["price"]=$productInfo["discount_price"];
                        $orderProduct[$k]["num"]=$v["num"];
                        $discount = $productInfo["discount_price"]==0 ? 0 :($productInfo["price"]- $productInfo["discount_price"] )*$v["num"];
                        $orderProduct[$k]["discount"]=$discount;
                        $orderProduct[$k]["real_price"]=$productInfo["price"];
                        $orderProduct[$k]["create_on"]=date("Y-m-d H:i:s");
                        $orderProduct[$k]["is_deleted"]=0;
                        $orderProduct[$k]["attribute_list"]="";
                        $totalDiscount+=$discount;
                        $total+=$orderProduct[$k]["price"]*$orderProduct[$k]["num"];
                    }

                }
            }

            $priceDet["amount"]=$total;
            $priceDet["discount"]=$totalDiscount;
            $priceDet["order_product"]=$orderProduct;
            return $priceDet;
        }





    //编辑购物车产品数量
    public function editCarts($requestData){

        $res = Product::table("product")->where("is_deleted=0 and num>=".$requestData['num']." and id=".$requestData['product_id'])->column("id");
        if(isset($requestData["sku_id"]) && $requestData["sku_id"]!=0)
        {
       //     $res = Product::table("product_sku")->where("is_deleted=0 and num>=".$requestData['num']." and id=".$requestData['sku_id'])->column("id");
        }
        if(!empty($res)){
            Product::table($this->table)->where(["customer_id"=>$this->userInfo[0]["id"],"product_id"=>$requestData["product_id"],"sku_id"=>$requestData["sku_id"]])->update(["num"=>$requestData["num"]]);
            return ["code"=>0];
        }
        return ["code"=>10005 ,"errMsg"=>"超过可购买最大数量"];
    }
    // 获取购物车数据
    public function getCarts($requestData)
    {
        $userInfo = $this->checkSession($requestData["token"]);
        if(empty($userInfo))
        {
            return ["code"=>10002,"errMsg"=>"用户不存在或未激活"];
        }
        $queryCart = Product::table($this->table);
        $carts = $queryCart->where(["customer_id"=>$userInfo[0]["id"] ,"is_deleted"=>0] )->order("id desc")->select()->toArray();
        if(empty($carts))
        {
            return ["code"=>10001 ,"errMsg"=>"购物车为空"];
        }

        $pro = $sku = [];
        foreach($carts as $k =>$v)
        {
            $attrVal= [];
            $query = Product::table("product");
            $pro[$k] = $query->where(["id"=>$v["product_id"]])->find()->toArray();
            $pro[$k]["sku_id"] = $v["sku_id"];
            $price = $pro[$k]["discount_price"];
            $querySku =Product::table("product_sku");
            $res  = $querySku->where(["product_id"=>$v["product_id"] ,"id"=>$v["sku_id"]])->find();
            $attrName = "";
            if(!empty($res))
            {
                $sku = $res->toArray();
                //获取sku属性
                $queryAttr = Product::table("p_attribute");
                $attrVal = $queryAttr->where("id","in",$sku['attribute_id'])->column(["attribute_value"]);
                $price=empty($sku["discount_price"])?$sku["price"]:$sku["discount_price"];
            }
            $pro[$k]["isSelect"]= false;
            $max = empty($sku["num"])?$pro[$k]["num"]:$sku["num"];
            $pic = $pro[$k]["cover_image"];
            $pro[$k]["count"]=["quantity"=>$v["num"],"max"=>$max,"min"=>1];
            $pro[$k]["cover_image"] = $pic;
            $pro[$k]["price"] = $price;
            $pro[$k]["attr"] = $attrVal;
            $pro[$k]["cart_id"] = $v["id"];
        }
        return ["code"=>0 ,"data"=>$pro] ;

    }
    //加入购物车
    public function carts($requestData){
       $userInfo = $this->checkSession($requestData["token"]);
       if(empty($userInfo))
       {
           return ["code"=>10002,"errMsg"=>"用户不存在或未激活"];
       }
       $data = [] ;

       $data["customer_id"] = $userInfo[0]["id"];
       $data["product_id"] = $requestData["product_id"];
       $data["sku_id"] = isset($requestData["sku_id"])?$requestData["sku_id"]:"";
       $data["num"] = 1;
       $data["source"] = "微信";
       $data["create_date"] = date("Y-m-d H:i:s");
       $query=Product::table($this->table);
       $res = $this->checkCarts($query,$data);

       return $res;
    }
    private function checkCarts($query,$data)
    {
       // 查询商品是否存在
        $query->where(["product_id"=>$data["product_id"] ,"customer_id"=>$data["customer_id"]]);
        if(!empty($data["sku_id"]))
        {
            $query->where(["sku_id"=>$data["sku_id"]]);
        }
        $res = $query->select()->toArray();
        if(empty($res)){//商品不存在加入数据库
            Product::table("carts")->insert($data);
            return ["code" => 0];
        }
            return ["code" => 10003 ,"errMsg"=>"商品已在购物车中"];
    }
    /**
     * 获取产品
     * 
     */
    public function product($requestData )
    {   

        $query=Product::table($this->table);
        $query->where(["is_deleted"=>0]); 
        if(isset($requestData["id"])){
            $query->where(["id"=>$requestData["id"]]); 
        }
        if(isset($requestData["lk"])){//模糊查询
            $query->where('name|price|discount_price','like','%'.$requestData["lk"].'%');
        }
        if(isset($requestData["is_hot"])){
            $query->where(["is_hot"=>$requestData["is_hot"]]);
        }
        if(isset($requestData["is_sale"])){
            $query->where(["is_sale"=>$requestData["is_sale"]]);
        }
        if(isset($requestData["items_id"])){

            if($requestData["items_id"] == "hot"){
                $query->where(["is_hot"=> 1]);
            }elseif($requestData["items_id"] == "discount"){
                $query->where(["is_sale"=> 1]);
            }else{
                $query->where(["items_id"=>$requestData["items_id"]]);
            }

        }
        return $query->select()->toArray();

    }
    /**
     * 获取类目
     */
    public function categories($requestData)
    {    
        $query=Product::table($this->table);
        $query->where(["is_deleted"=>0]); 
        if(isset($requestData["id"])){
            $query->where(["id"=>$requestData["id"]]); 
        }
        return $query->select()->toArray();
   
    }
 /**
  *
  * 返回当前商品所属类目 以及其子类目 与第一个sku   sku 做前端默认选中项
  **/
    public function attribute($requestData)
    {
        $res=$sku = array();
        $value =$defPrice= $defAttrName=$defSku="";
        $defAttr = array();
        $canUse =[];
        $query_itmes=Product::table("p_items");
        if(isset($requestData["id"])){
            $value = $query_itmes->where(["is_deleted"=>0,"id"=>$requestData["id"]])->value("attribute_id");
        }
        if(isset($requestData["proid"]))
        {

            $sku_all =Product::table("product_sku")->where(["product_id"=>$requestData["proid"],"is_deleted"=>0])->select()->toArray();
            if(!empty($sku_all))
            {
                foreach($sku_all as $k=>$v){
                    $temp = explode(",",$v["attribute_id"]);
                    foreach($temp as $tk=>$tv)
                    {
                        $canUse[$tk][] = $tv;
                    }
                }
                foreach($canUse as $k=>$v)
                {
                    $canUse[$k] = array_unique($v);
                }
            }

            $sku = Product::table("product_sku")->where(["product_id"=>$requestData["proid"],"is_deleted"=>0])
                ->limit(1)->select()->toArray();
            if(!empty($sku)){
                $defAttr =explode(",",$sku[0]["attribute_id"]);
                $defPrice =$sku[0]["price"];
                $defSku =$sku[0]["id"];
            }
        }
        if(!empty($value)){
            $valueArray = explode(",",$value);
            $query = Product::table($this->table);
            $res = $query->where(["id"=>["in",$valueArray],"is_deleted"=>0,"status"=>"发布","attribute_id"=>0])->select()->toArray();
            if(!empty($res) && is_array($res)){

                foreach($res as $k=>$v){
                    $query = Product::table($this->table);
                    $query->where(["attribute_id"=>$v["id"]]);
                    if(!empty($canUse))
                    {
                         $query->where(["id"=>["in",$canUse[$k]]]);
                    }
                    $res[$k]["child"] = $query->select()->toArray();
                    foreach($res[$k]["child"] as $ck=>$cv){
                        $res[$k]["child"][$ck]["is_select"] = false ;
                        if(!empty($defAttr) && in_array($cv['id'],$defAttr)){
                            $res[$k]["child"][$ck]["is_select"] = true ;
                            $defAttrName.=$cv["attribute_value"].",";
                        }
                    }
                }
            }
        }
        return array("cate"=>$res,"defPrice"=>$defPrice,"defName"=>substr($defAttrName,0,-1),"defAttr"=>$defAttr,"defSku"=>$defSku);
    }



    //获取用户 充值记录，消费信息，订单信息
    public function shoppingMsg($requestData)
    {
        return Product::table($this->table)->where($this->column)->order('id','desc')->select()->toArray();
    }
    //生成随机字符串 防止重复
    private function session3rd($openId , $session_key)
    {
      $time = date("YmdHis",time());
      $rand = mt_rand(1000,9999);
      $char  = $openId.$time.chr($rand).$session_key.$openId;
      return md5($char);
    }
    public function getUserInfo($token){
        $session3rd =$this->session3rd($token["openid"],$token["session_key"]);
//        $session3rd = md5($token["openid"].$token["session_key"]);
        $query=Product::table("customer");
        $query->where(["openid"=>$token['openid']]);
        if(isset($token["unionid"]))
        {
            $query->where(["unionid"=>$token['unionid']]);
        }
        $res = $query->select()->toArray();
        if(empty($res)){
            return array("code"=>10000,"msg"=>"用户不存在");
        }

        $storage = [$session3rd=>$token];
        $query=Product::table("customer");
        $query->where(["openid"=>$token['openid']])->update(["token"=>$session3rd,"session_key"=>$token["session_key"]]);
        if(empty($res[0]["mobile"]))
        {
            return array("code"=>10011,"errMsg"=>"验证手机号","token"=>$session3rd);
        }
        if($res[0]["status"]=="已冻结")
        {
            return array("code"=>10015,"errMsg"=>"账号已冻结",);
        }
        return array("code"=>0 ,"token"=>$session3rd);
    }
    //新用户加入数据库
    public function addCustomer($data)
    {
       $userInfo = json_decode($data,true);
       OpenApi::addWxCustomer($userInfo);
       return array("code"=>0);
    }
   public function getRecord($requestData)
   {
      $query  = Product::table($this->table);
      if($this->table =="customer")
      {
          $data = $query->where(["id"=>$this->userInfo[0]["id"]])->order("id desc")->select()->toArray();
          $account = Db::table("customer_account")->where(["customer_id"=>$this->userInfo[0]["id"]])->column("amount");
          $string = "";
          if(!empty($data))
          {
              $split = str_split($data[0]["user_name"],3);
              $string =implode(" ",$split);
              $data[0]["amount"] =!empty($account[0])?$account[0]:"";
              $data[0]["user_name"] = $string ;
          }
      }else{
          $data = $query->where(["customer_id"=>$this->userInfo[0]["id"]])->order("id desc")->select()->toArray();
      }
      if(!empty($data))
      {
          return ["code"=>0,"data"=>$data];
      }
         return ["code"=>10007 ,"errMsg"=>"查询失败,请稍后再试"];
   }

    public function getOrdersInfo($requestData)
    {
        $ordInfo = Product::table("orders")->where(["customer_id"=>$this->userInfo[0]["id"]])->order("id desc")->select()->toArray();
        $orderProduct=[];

        if(!empty($ordInfo)) {
            foreach ($ordInfo as $k => $v) {
                $orderProduct = Product::table("orders_product")->where(["orders_id" => $v["id"]])->select()->toArray();
                $total=0;
                foreach ($orderProduct as $ok => $ov) {

                    $total+=$ov["num"];
                    $proInfo = Product::table("product")
                        ->where(["id" => $ov["product_id"]])->find()->toArray();
                    if (!empty($ov["attribute_list"]) && !empty($ov["sku"])) {
                        $skuInfo = Product::table("product_sku")
                            ->where(["sku" => $ov["sku"], "product_id" => $ov["product_id"]])->find()->toArray();
                        $attr = Product::table("p_attribute")
                            ->where("id", "in", $ov["attribute_list"])->column("attribute_value");
                        $orderProduct[$ok]["cover_image"] = empty($skuInfo["image"]) ? $proInfo["cover_image"] : $skuInfo["image"];
                        $orderProduct[$ok]["attrName"] = $attr;
                    } else {
                        $orderProduct[$ok]["cover_image"] = $proInfo["cover_image"];
                        $orderProduct[$ok]["attrName"] = [];
                    }
                }
                $ordInfo[$k]["orders_product"] = $orderProduct;
                $ordInfo[$k]["total_num"] =$total;

            }
            return ["code"=>0,"data"=>$ordInfo];
        }
           return ["code"=>10010,"errMsg"=>"未找到相关记录"];
    }
    public function chooseSku($requestData)
    {
        $attrArr = json_decode($requestData["attr"],true);
//        $attrArr=[44,47,50];
        $attrInfo=[];
        $attrName='';
        $attr = implode(",",$attrArr);
        $res = Product::table($this->table)->where(["product_id"=>$requestData["product_id"],"attribute_id"=>$attr])
            ->find()->toArray();
        if(empty($res))
        {
            return ["code"=>10010,"errMsg"=>"未找到相关记录"];
        }
        $attrInfo =Product::table("p_attribute")
                        ->where(["id"=>["in",$attrArr]])
                        ->select()->toArray();
        if(!empty($attrInfo))
        {
            foreach($attrInfo as $v)
            {
                $attrName.=$v["attribute_value"].",";
            }
            $attrName = substr($attrName,0,-1);
        }
        $res["attrName"]=$attrName;
        return ["code"=>0,"data"=>$res];
    }
    public function updateInfo($requestData)
    {
        $res = Product::table($this->table)->where(["id"=>$this->userInfo[0]["id"]])
            ->update(["mobile"=>$requestData["mobile"],"status"=>"已激活"]);
        if($res)
        {
            return ["code"=>0];
        }
        return ["code"=>10012,"errMsg"=>更新失败请稍后再试];
    }
    public function userPhone($requestData)
    {
        $sessionKey  = $this->userInfo[0]["session_key"];
        $decryptedObject = new DecryptedService($this->appId,$sessionKey);
        $encryptedData=$requestData["encryptedData"];
        $iv = $requestData["iv"];
        $data='';
        $errCode=$decryptedObject->decryptData($encryptedData,$iv,$data);
        if($errCode == 0)
        {
            $telInfo  = json_decode($data, true );
            return ["code"=>0,"mobile"=>$telInfo['purePhoneNumber']];
        }
        return ["code"=>$errCode];
    }
    public function sessionInfo($requestData)
    {
        if($this->userInfo[0]['status']=="未激活" || empty($this->userInfo[0]['mobile']))
        {
            return ["code"=>10014];
        }
        return ["code"=>0] ;
    }
}
