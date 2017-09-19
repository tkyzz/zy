<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/8/18
 * Time: 15:30
 */

namespace Lib\Services;

use Lib\Misc\Result;
use Prj\Loger;
use Sooh2\Misc\Ini;

class GetUpdateApp extends \Prj\Bll\_BllBase
{
    //读取接口
    public static function sendUpdateApp($params){
//        //检查参数是否存在
//        if (!Result::paramsCheck($params, ['contractId','curver'])) {
//            return Result::get(RET_ERR, '参数错误[' . Result::$errorParam . ']');
//        }
        $url = "http://" . Ini::getInstance()->getIni("application.serverip.appupdate").'/jzucapp/getUpdateApp';
        $Curl = \Sooh2\Curl::factory();
        $params = ['avReq' => $params];
        \Prj\Loger::out(__METHOD__ . ' 请求参数: ' . json_encode($params, 256));

        \Prj\Loger::out(__METHOD__ . ' 请求地址: ' . $url);
        $ret = $Curl->httpPost($url, json_encode($params));
        Loger::out($ret);
        \Prj\Loger::out(__METHOD__ . ' 解绑结果: ' . $ret);
        return json_decode($ret, true);
    }
}