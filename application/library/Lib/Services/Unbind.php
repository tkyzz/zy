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

class Unbind extends \Prj\Bll\_BllBase
{
    /*解綁銀行卡*/
    public function sendUnBind($params)
    {
        if (!Result::paramsCheck($params, ['userId'])) {

            return Result::get(RET_ERR, '参数错误[' . Result::$errorParam . ']');
        }
        $url = "http://" . Ini::getInstance()->getIni("application.serverip.ZYSettlement") . Ini::getInstance()->getIni("Urls.javaApiUnbindCard");
        $Curl = \Sooh2\Curl::factory();
        $data = [
            'data' => $params,
            'extendInfo' => array("string"),
            'reqTime' => time() . "000"
        ];
        \Prj\Loger::out(__METHOD__ . ' 请求参数: ' . json_encode($data, 256));

        \Prj\Loger::out(__METHOD__ . ' 请求地址: ' . $url);
        $ret = $Curl->httpPost($url, json_encode($data));
        Loger::out($ret);
        \Prj\Loger::out(__METHOD__ . ' 解绑结果: ' . $ret);
        return json_decode($ret, true);
    }

}