<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/15
 * Time: 15:50
 */


namespace Lib\Services;

use Lib\Misc\Result;
use Prj\Loger;
use Sooh2\Misc\Ini;

class Freeze extends \Prj\Bll\_BllBase
{
    /*冻结账户*/
    public function sendFreeze($params)
    {
        if (!Result::paramsCheck($params, ['userId'])) {

            return Result::get(RET_ERR, '参数错误[' . Result::$errorParam . ']');
        }
        $url = "http://" . Ini::getInstance()->getIni("application.serverip.ZYSettlement") . Ini::getInstance()->getIni("Urls.javaApiFreeze");
        $Curl = \Sooh2\Curl::factory();
        $data = [
            'data' => $params,
//            'extendInfo' => array("string"),
            'reqTime' => time() . "000"
        ];
        \Prj\Loger::out(__METHOD__ . ' 请求参数: ' . json_encode($data, 256));

        \Prj\Loger::out(__METHOD__ . ' 请求地址: ' . $url);
        $ret = $Curl->httpPost($url, json_encode($data));
        if(Result::check(json_decode($ret,true))){
            $res = \Prj\Bll\User::getInstance()->freeze($params['userId']);
            Loger::outVal("fdfdfd",$res);
        }
        Loger::out($ret);
        \Prj\Loger::out(__METHOD__ . ' 冻结结果: ' . $ret);
        return json_decode($ret, true);
    }



    /*解冻账号*/
    public function sendUnfreeze($params){
        if (!Result::paramsCheck($params, ['userId'])) {

            return Result::get(RET_ERR, '参数错误[' . Result::$errorParam . ']');
        }
        $url = "http://" . Ini::getInstance()->getIni("application.serverip.ZYSettlement") . Ini::getInstance()->getIni("Urls.javaApiUnfreeze");
        $Curl = \Sooh2\Curl::factory();
        $data = [
            'data' => $params,
//            'extendInfo' => array("string"),
            'reqTime' => time() . "000"
        ];
        \Prj\Loger::out(__METHOD__ . ' 请求参数: ' . json_encode($data, 256));

        \Prj\Loger::out(__METHOD__ . ' 请求地址: ' . $url);
        $ret = $Curl->httpPost($url, json_encode($data));
        if(Result::check(json_decode($ret,true))){
            \Prj\Bll\User::getInstance()->unfreeze($params['userId']);
        }
        Loger::out($ret);
        \Prj\Loger::out(__METHOD__ . ' 解冻结果: ' . $ret);
        return json_decode($ret, true);
    }

}