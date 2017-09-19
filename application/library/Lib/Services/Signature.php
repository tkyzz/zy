<?php
/**
 * 电子签章通讯
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/15
 * Time: 15:50
 */


namespace Lib\Services;

use Lib\Misc\Result;
use Prj\Loger;
use Sooh2\Misc\Ini;

class Signature extends \Prj\Bll\_BllBase
{

    /**
     * Hand 当订单确认的时候发送信息给电子签章
     * @param array $params
     * @return array
     */
    public function sendDataWhenBuy($params = []){
        // realname,idCard 非必须
        if(!\Lib\Misc\Result::paramsCheck($params , ['phone','orderNo','orderTime','userId','productNo','productType'])){
            return $this->resultError('参数错误');
        }
        $url = $url = "http://" . \Sooh2\Misc\Ini::getInstance()->getIni("application.serverip.agreement") .
            \Sooh2\Misc\Ini::getInstance()->getIni("Urls.javaSignOrder");
        $curl = \Sooh2\Curl::factory();
        $data = [
            'data' => [
                "orderNo" => $params['orderNo'],
               "investorOid" => $params['userId'],
               "productNo" => $params['productNo'],
               "orderTime" => $params['orderTime'],
               "userName" => $params['realname'],
               "userPhone" => $params['phone'],
               "idCard" => $params['idCard'],
                "productType" => $params['productType'],
            ],
            'extendInfo' => array("string"),
            'reqTime' => time() . "000"
        ];
        \Prj\Loger::out(__METHOD__ . ' 请求参数: ' . json_encode($data, 256));

        \Prj\Loger::out(__METHOD__ . ' 请求地址: ' . $url);
        $ret = $curl->httpPost($url, json_encode($data));
        \Prj\Loger::out(__METHOD__ . ' 请求结果: ' . $ret);
        return json_decode($ret, true);
    }

    /**
     * Hand 当产品成立的时候发送信息给电子签章
     * @param array $params
     * @return array
     */
    public function sendDataWhenSetUp($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['productNo'])){
            return $this->resultError('参数错误');
        }
        $url = $url = "http://" . \Sooh2\Misc\Ini::getInstance()->getIni("application.serverip.agreement") .
            \Sooh2\Misc\Ini::getInstance()->getIni("Urls.javaSignProduct");
        $curl = \Sooh2\Curl::factory();
        $data = [
            'data' => [
                "productNo" => $params['productNo'],
            ],
            'extendInfo' => array("string"),
            'reqTime' => time() . "000"
        ];
        \Prj\Loger::out(__METHOD__ . ' 请求参数: ' . json_encode($data, 256));

        \Prj\Loger::out(__METHOD__ . ' 请求地址: ' . $url);
        $ret = $curl->httpPost($url, json_encode($data));
        \Prj\Loger::out(__METHOD__ . ' 请求结果: ' . $ret);
        return json_decode($ret, true);
    }
}