<?php
namespace Prj\Redis;

/**
 * 验证码类
 *
 * @author simon.wang
 */
class Vcode extends Base{
    const vcodeExpire = 300;//5分钟过期
    /**
     * 创建一个6位验证码
     * @param string $ip
     * @param string $phone
     * @param string $act
     * @param integer $vcodeExpire 有效期，单位秒
     * @return mixed 6位验证码，或false（比如该ip发送消息过度）
     */
    public static function createVCode($ip,$phone,$act='login', $vcodeExpire = 300){
        $db = self::getDB();
        if (self::chkip($db, $ip)) {
            if (\Prj\Tool\Debug::isTestEnv()) {
                $newCode = 111111;
            } else {
                $newCode = mt_rand(100000, 999999);
            }

            $db->exec(array(['set', "c:g:u:vc:{$phone}_{$act}", $newCode]));
            $db->exec(array(['setTimeout', self::fmtKey($phone, $act), $vcodeExpire]));
            return $newCode;
        } else {
            return false;
        }
    }
    protected static function fmtKey($phone,$act)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace("c:g:u:vc:{$phone}_{$act}");
        return "c:g:u:vc:{$phone}_{$act}";
    }
    /**
     * 获取发给该手机号的验证码
     * @param string $ip IP
     * @param string $phone phone
     * @param string $act action
     * @return mixed 数组包含6位验证码和剩余有效期，或false（比如该ip验证消息过度）
     */
    public static function fetchVCode($ip,$phone,$act='regist'){
        $db = self::getDB();
        if(self::chkip($db, $ip)){
            $rs = $db->exec(array(['get',self::fmtKey($phone, $act)]));
            if ($rs) {
                return ['value' => $rs, 'ttl' => $ttl = $db->exec([['ttl', self::fmtKey($phone, $act)]])];
            }
            return null;
        }else{
            return false;
        }
        
    }

    public static function fetchVCodeWithoutIp($phone, $act = 'regist')
    {
        $db = self::getDB();
        $rs = $db->exec([['get', self::fmtKey($phone, $act)]]);
        \Sooh2\Misc\Loger::getInstance()->app_trace('get vcode:' . $rs . ' from redis by phone:' . $phone);
        return $rs;
    }
}
