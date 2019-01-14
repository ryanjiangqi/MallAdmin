<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * web socket 对接基础类
 */

namespace app\base\controller;

use app\service\system\SetSerivce;
use think\Config;
use think\Controller;
use think\Log;
use Workerman\Worker;
use \Workerman\Lib\Timer;
use Workerman\Mysql\Connection;

require_once ROOT_PATH . '/extend/workerman/Autoloader.php';

/**
 * Description of comm
 *
 * @author RyanJiang
 */
class Basesocket extends Controller
{
    //websocket 业务详细配置
    protected $soketConfig;
    //websocket对接不同客户端（web，c++）
    protected $routeConfig;
    //workman 进程数设置
    protected $count;
    protected $worker;

    public function __construct()
    {
        $this->soketConfig = Config::get('socketconfig');
        $this->setOptions();
        $this->worker = new Worker($this->soketConfig[$this->routeConfig]['ip']);
        // ====这里进程数必须必须必须设置为1====
        $this->worker->count = $this->count;
        // 新增加一个属性，用来保存uid到connection的映射(uid是用户id或者客户端唯一标识)
        $this->worker->uidConnections = array();
        $this->workermanDb = new Connection('47.98.113.6', '3306', 'root', '123456', 'esports');
    }

    /**\
     * @desc 如果数据库中设置了， 基础参数，则读取数据库配置 ，否则将使用默认配置
     * @return  bool
     *
     */
    private function setOptions()
    {
        $setInfo = SetSerivce::instance()->optionList();
        //每小时费用
        if (!empty($setInfo['hour_fee'])) {
            $this->soketConfig['hourFee'] = $setInfo['hour_fee']['option_value'];
        }
        //余额提醒
        if (!empty($setInfo['remind_fee'])) {
            $this->soketConfig['balance_not_enough'] = $setInfo['remind_fee']['option_value'];
        }
        //计费时间间隔
        if (!empty($setInfo['count_fee_time'])) {
            $this->soketConfig['countFee'] = $setInfo['count_fee_time']['option_value'];
        }
        return true;
    }

    /**
     * 接收client发送数据的初始化
     * @param $data
     * @return array
     */
    protected function initDataFromClient($data, $connection)
    {
        $responseData = $this->dataDecrypt($data);
        $result['method'] = !empty($responseData['method']) ? $responseData['method'] : '';
        $result['user_name'] = !empty($responseData['user_name']) ? $responseData['user_name'] : '';
        $result['password'] = !empty($responseData['password']) ? $responseData['password'] : '';
        $result['timestemp'] = !empty($responseData['timestemp']) ? $responseData['timestemp'] : '';
        $result['pc_no'] = !empty($responseData['pc_no']) ? $responseData['pc_no'] : '';
        $result['req_id'] = !empty($responseData['req_id']) ? $responseData['req_id'] : '';
        $result['ip'] = $connection->getRemoteIp();
        return $result;
    }

    /**
     * 接收web端发送数据的初始化
     * @param $data
     * @return array
     */
    protected function initDataFromWeb($data)
    {
        return $data;
    }

    protected function onConnect()
    {
        $this->worker->onConnect = function ($connection) {
        };
    }

    protected function onMessageForClient()
    {
        $this->worker->onMessage = function ($connection, $data) {
            $dataParam = $this->initDataFromClient($data, $connection);
            Log::info(' client入参：' . json_encode($dataParam));
            switch ($dataParam['method']) {
                case 'login':
                    $result = $this->login($connection, $dataParam);
                    break;
                case 'logout':
                    $result = $this->logout($dataParam, $connection);
                    break;
                case 'logoutremote':
                    //通知客户锁屏
                    $result = $this->logoutRemote($dataParam);
                    break;
                default:
                    $result = [];
                    break;
            }
            Log::info(' client出参：' . json_encode($result));
            $connection->send($this->sendDataRequestId($result, $dataParam));
        };
    }

    protected function onMessageForWeb()
    {
        $this->worker->onMessage = function ($connection, $data) {
            $dataParam = $this->initDataFromWeb($data);
            $this->pcStatus($connection, $dataParam);
        };
    }

    protected function onWorkerStart($action = null)
    {
        $this->worker->onWorkerStart = function ($task) use ($action) {
            switch ($action) {
                case 'client_cout_fee':
                    $this->clientCountFee();
                    break;
            }
        };
    }


    /**
     * 数据解密
     * @param $dataString
     * @return mixed
     */
    protected function dataDecrypt($dataString)
    {
        return json_decode(base64_decode($dataString), true);
    }

    /**
     * 数据加密
     * @param $dataArray
     * @return string
     */
    protected function dataEncryption($dataArray)
    {
        return base64_encode(json_encode($dataArray));
    }

    /**
     * @desc 返回客户端发送的request id,如果request id 不存在 则 request id 为0
     * @param $dataArray
     * @param $requestId
     * @return array;
     */
    protected function sendDataRequestId($dataArray, $dataParam = [])
    {
        if (!empty($dataParam['req_id'])) {
            $dataArray['req_id'] = $dataParam['req_id'];
        } else {
            $dataArray['req_id'] = '0';
        }
        return $this->dataEncryption($dataArray);
    }

    /**
     * uid 唯一识别码设置方式
     * @param $responseData
     * @return 识别码
     */
    protected function onlyCode($responseData)
    {
        //return $responseData['user_name'];
        return $responseData['pc_no'] . $responseData['user_name'];
    }


}
