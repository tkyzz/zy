<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-09-14 10:49
 */

namespace Prj\Bll;

class Code extends _BllBase
{
    /**
     * 检查、校验短信验证码
     * @param string $phone phone
     * @param string $action action
     * @param string $smsCode code
     * @param string $returnMessage 返回消息内容
     * @param string $returnCode 返回消息错误码
     * @return bool true:未发送、校验成功
     * @author lingtima@gmail.com
     */
    public function checkSMSCode($phone, $action, $smsCode = '', $returnMessage = '', $returnCode = 99995)
    {
        $redisCode = \Prj\Redis\Vcode::fetchVCode(\Sooh2\Util::remoteIP(), $phone, $action);
        if ($smsCode) {
            if ($redisCode && $redisCode['value'] == $smsCode) {
                return true;
            }
            \Prj\Bll\View::getInstance()->fill(['ttl' => $redisCode['ttl'], 'message' => $returnMessage, 'code' => $returnCode]);
            return false;
        }
        if ($redisCode) {
            \Prj\Bll\View::getInstance()->fill(['ttl' => $redisCode['ttl'], 'message' => $returnMessage, 'code' => $returnCode]);
            return true;
        }
        return false;
    }
}