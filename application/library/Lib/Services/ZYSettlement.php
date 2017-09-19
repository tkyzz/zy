<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/9/14
 * Time: 14:34
 */

namespace Lib\Services;

class ZYSettlement extends \Prj\Bll\_BllBase
{
    /**
     * Hand 查询用户的实名信息
     * @param $userId
     * @return array|mixed
     */
    public function getAccount($userId){
        if(empty($userId))return $this->resultError('参数错误[userId]');
        $url = "http://" . \Sooh2\Misc\Ini::getInstance()->getIni("application.serverip.ZYSettlement") .
            \Sooh2\Misc\Ini::getInstance()->getIni("Urls.javaApiGetAccount");

        $curl = \Sooh2\Curl::factory();
        $data = [
            'data' => [
                'userId' => $userId
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

