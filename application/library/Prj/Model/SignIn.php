<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 20:30
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

class SignIn extends KVObj
{
    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 't_sign_in';
    }

    /**
     * @param array $key
     * @return KVObj
     * @author lingtima@gmail.com
     */
    public static function getCopy($key)
    {
        return parent::getCopy(['oid' => $key]);
    }

    /**
     * 根据用户ID获取最后一次签到记录
     * @param $uid
     * @return mixed
     */
    public static function getOneLastByUid($uid)
    {
        $db = static::getCopy(null)->dbWithTablename();
        $ret = $db->getRecord($db->kvobjTable() , '*' , ['userId' => $uid], 'rsort signInTime');
        return $ret;
    }

    /**
     * 检查今天是否签到
     * @param $uid
     * @param int $time 时间戳
     * @return bool true已签到，false未签到
     * @author lingtima@gmail.com
     */
    public static function checkSign($uid, $time = null)
    {
        $lastOne = static::getOneLastByUid($uid);
        error_log(var_export($lastOne, true));
        if ($lastOne && isset($lastOne['signInTime'])) {
            if (date('Ymd',$time ? : time())==date('Ymd',strtotime($lastOne['signInTime']))) {
                return true;
            }
        }
        return false;
    }


    /**
     * 添加一条签到记录    同步国槐数据
     * @param $uid
     * @param $time
     * @return bool|string
     * @author lingtima@gmail.com
     */
    public static function add($uid, $time)
    {
        //生成UUID
        $funcProduceUUID = function () use ($uid) {
            list($usec, $sec) = explode(' ', microtime());
            $usecNum = 10000 * ((float)$usec + (float)$sec);
            $uuid = 'php' . $usecNum . rand(1000, 9999) . substr($uid, -10);
            return $uuid;
        };

        $fields = [
            'oid' => $funcProduceUUID(),
            'userId' => $uid,
            'signInTime' => date('Y-m-d H:i:s', $time),
        ];
        $db = static::getCopy(null)->dbWithTablename();

        try {
            $ret = $db->addRecord($db->kvobjTable(), $fields);
            return true;
        } catch (\ErrorException $e) {
            \Prj\Loger::out($e->getMessage());
            return false;
        }
    }
}