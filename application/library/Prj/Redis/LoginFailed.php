<?php

namespace Prj\Redis;

/**
 * 登入失败的维护和锁定
 *
 * @author simon.wang
 */
class LoginFailed extends Base
{
    const maxRetry = 5;
    const lockHours = 24;

    /**
     * 最近登入失败次数（-1标示已经锁定）
     * @param string $phone
     * @param string $ip ip地址检查，null为跳过ip检查
     * @return int
     */
    public static function isLocked($phone,$ip)
    {
        $db = self::getDB();
        if(self::chkip($db, $ip)){
            $key = self::fmtKey($phone, true);
            if($db->exec(array(['exists',$key]))){
                return true;
            }else{
                return false;
            }
        }else{
            return 1;
        }
    }

    /**
     * 指定手机号上发生一次错误后调用，返回这次发生错误后是否锁定了用户
     * @param string $ip
     * @param string $phone
     * @return bool 
     */
    public static function errorOccur($ip,$phone)
    {
        $db = self::getDB();
        self::chkip($db, $ip);

        $ret = $db->exec([['exists', self::fmtKey($phone, true)]]);
        if ($ret) {
            return true;
        }

        $key = self::fmtKey($phone);
        if($db->exec(array(['exists',$key]))){
            $n1 = $db->exec(array(['incr',$key]));
            if($n1>=self::maxRetry){
                self::createLock($ip, $phone);
                return true;
            }
            return self::maxRetry - $n1;
        }
            $db->exec(array(['set',$key,$n1 = 1]));
            $db->exec(array(['setTimeout',$key,3600]));//60分钟过期
        return self::maxRetry - 1;
    }

    public static function createLock($ip, $phone)
    {
        $db = self::getDB();
        self::chkip($db, $ip);
        $key = self::fmtKey($phone, true);
        if ($db->exec(array(['exists', $key]))) {
            return false;
        } else {
            $db->exec(array(['set', $key, $n1 = 1]));
            $db->exec(array(['setTimeout', $key, 3600 * self::lockHours]));//24小时过期
            return false;
        }
    }

    protected static function fmtKey($phone, $isLock = false)
    {
        return $isLock ? "limitsChk:loginLocked:$phone" : "limitsChk:loginFailed:".$phone;
    }

    /**
     * 删除用户登入锁定
     * @param string $phone
     */
    public static function unlock($phone)
    {
        $db = self::getDB();
        $key = self::fmtKey($phone, true);
        $db->exec(array(['delete',$key]));
        $key = self::fmtKey($phone);
        $db->exec(array(['delete',$key]));
    }
}
