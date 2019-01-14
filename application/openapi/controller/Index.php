<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/10
 * Time: 11:16
 */

namespace app\openapi\controller;
use app\service\openapi\DecryptedService;
use app\service\openapi\SignatureHelperService;
use think\Controller;
use think\Request;
use think\helper;
use app\service\openapi\OpenApiService;


class Index extends Controller
{
    private $appId ="wx78925e9724368618";
    private $appSecret = "2a91608a9da3f2e102af412abfd4f28f";
    private $session_key="";
    private $access_key = "umIKcQtYplU14g9W";
    private $accessKeySecret ="gOtqV0xMIkTL2WnbgnXjdtMAowzHey";
    /**
     * @param Request $request arrya(m=>product,data=>[1,2,3,4])
     * method = [product , category ,login ]
     */
    public function index(Request $request){
        
       $requestData = $request->param();
       $openApiService = OpenApiService::instance();
       $res = $openApiService->apiRoute($requestData);
       echo json_encode($res);
    }
    public function login(Request $request){
        $requestData = $request->param();
        $code = $requestData["code"];
        $userToken = $this->getUserToken($code);

        if($userToken["error"] == 0)
        {
            $res = $this->checkUserInfo($userToken["errMsg"]);
            return json_encode($res);exit;
        }
        return json_encode($userToken);exit ;
    }
    //获取Openid session key  返回[error ,msg]
    private function getUserToken($code)
    {
        $url = trim("https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appId."&secret=".$this->appSecret."&js_code=".$code."&grant_type=authorization_code");
        $timeout = 10000;
        $path = "/home/www/html/www.leternal.cn/html/public/testLog.txt";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $output = curl_exec($ch);
        if($output === FALSE){
            $err = "CURL Error:".curl_error($ch);
            file_put_contents($path, $err."===".date("Y-m-d H:i:s"));
            return array("error"=>10001 ,"errMsg"=>"获取用户openid失败");
        }
        curl_close($ch);
        file_put_contents($path, print_r($output,true)."\n===".$httpcode.'====\n'.$url."\n", FILE_APPEND);
        return  array("error"=>0,"errMsg"=>json_decode($output ,true));
    }
    private function checkUserInfo($token)
    {
        $openApiService = OpenApiService::instance();
        return $openApiService->getUserInfo($token);
    }
    public function decrypted(Request $request){
        $requestData = $request->param();
        $code = $requestData["code"];
        $token = $this->getUserToken($code);
        $path = "/home/www/html/www.leternal.cn/html/public/testLog.txt";
        file_put_contents($path,$token["errMsg"]["session_key"]."===解密sessionKey\n",FILE_APPEND);




        $decryptedObject = new DecryptedService($this->appId,$token["errMsg"]["session_key"]);
        $encryptedData=$requestData["encryptedData"];
        $iv = $requestData["iv"];
        $data='';
        $errCode=$decryptedObject->decryptData($encryptedData,$iv,$data);
        if ($errCode == 0) {
        //解密成功存入数据库
            $openApiService = OpenApiService::instance();
            $res = $openApiService->addCustomer($data);
            echo json_encode($res) ; exit;
        } else {

            echo json_encode(["code"=>$errCode]);exit;
        }
    }
    public function sendSms(Request $request) {
        $requestData = $request->param();
        $params = array ();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = $this->access_key;
        $accessKeySecret = $this->accessKeySecret;

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $requestData["mobile"];

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "Zwen";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_138165029";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $code = mt_rand(1000 ,9999);
        $params['TemplateParam'] = Array (
            "code" => $code
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelperService();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
         ,true
        );
        //短信发送成功
        $result = (array)$content ;
       if($result["Message"] == "OK" && $result["Code"] == "OK")
       {
           echo  json_encode(["code"=>0,"smsCode"=>$code]);exit;
       }
           echo json_encode(["code"=>10013,"msg"=>"短信发送失败，请稍后再试"]);exit;
//        echo  json_encode(["code"=>0,"smsCode"=>$code]);exit;
    }
}