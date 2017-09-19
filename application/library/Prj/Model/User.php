<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-13 15:23
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * Class User
 * @package Prj\Model
 * @author lingtima@gmail.com
 */
class User extends \Prj\Model\_ModelBase
{

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_user_0 ';
    }

    /**
     * @param $oid
     * @return \Prj\Model\User
     * @author lingtima@gmail.com
     */
    public static function getCopy($oid = '')
    {
        return parent::getCopy($oid);
    }
    /**
     * 
     * @param type $phone
     * @return \Prj\Model\User
     */
    public static function getCopyByPhone($phone)
    {
        return parent::getCopy(['userAcc' => $phone]);
    }

    /**
     * 新版注册
     * @param string $uid userId
     * @param string $loginName string
     * @param string $loginType string
     * @return bool|KVObj
     * @author lingtima@gmail.com
     */
    public static function createNew($uid, $loginName, $loginType)
    {
        //不写入tb_user表，因为会与旧接口产生冲突

        //写入tb_user_login表
        UserLogin::create($uid, $loginName, $loginType);

        //写入tb_user_final表
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        \Sooh2\Misc\Loger::getInstance()->app_trace($ModelUserFinal->dbWithTablename()->lastCmd());
        if ($ModelUserFinal->exists()) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('create user to tb_user_final failed, because uid duplicate. uid:' . $uid);
            return false;
        }
        $time = time();
        $ModelUserFinal->setField('phone', $loginName);
        $ModelUserFinal->setField('ymdReg', date('Ymd', $time));
        $ModelUserFinal->setField('hisReg', date('His', $time));
        $ModelUserFinal->setField('dtLast', $time);
        $ret = $ModelUserFinal->saveToDB();
        \Sooh2\Misc\Loger::getInstance()->app_trace($ModelUserFinal->dbWithTablename()->lastCmd());
        \Sooh2\Misc\Loger::getInstance()->app_trace($ret);
        return $ModelUserFinal;
    }

    public static function createNewSelf($phone, $contractId, $args = [])
    {
        $retry = 5;
        while ($retry) {//尝试5次生成28位长度的id，注意长度不要超过28位
            $retry--;
            $uid = \Prj\Bll\User::getInstance()->createUid();
            \Sooh2\Misc\Loger::getInstance()->app_trace('=====|=====|=====uid=====|=====|=====uid:' . $uid);

            $NewModelUser = self::getCopy($uid);
            $NewModelUser->load();
            if ($NewModelUser->exists()) {
                continue;
            }

            $NewModelUser->setField('userAcc', $phone);
            $NewModelUser->setField('status', 'normal');
            $NewModelUser->setField('source', 'frontEnd');
            $NewModelUser->setField('sceneId', \Prj\Redis\InviteCode::getNext());
            $NewModelUser->setField('checkinBook', '');
            $NewModelUser->setField('memberOid', $uid);
            $NewModelUser->setField('createTime', date('Y-m-d H:i:s'));
            $NewModelUser->setField('updateTime', date('Y-m-d H:i:s'));
            $NewModelUser->setField('channelid', $contractId);
            if (isset($args['userPwd']) && !empty($args['userPwd'])) {
                $salt = mt_rand(10000000, 99999999);
                $NewModelUser->setField('userPwd', \Prj\Bll\User::getInstance()->encryptPwd($args['userPwd'], $salt));
                $NewModelUser->setField('Salt', $salt);
            } else {
                $NewModelUser->setField('userPwd', '');
                $NewModelUser->setField('Salt', '');
            }
            $NewModelUser->setField('payPwd', '');
            $NewModelUser->setField('paySalt', '');
            $NewModelUser->saveToDB();
            return $NewModelUser;
        }
        \Sooh2\Misc\Loger::getInstance()->app_trace('重试5次之后依然');
        return false;
    }

    /**
     * 注册
     * @param string $uid userId
     * @param string $loginName loginName
     * @param string $loginType loginType
     * @return bool|KVObj
     * @author lingtima@gmail.com
     */
    public static function create2($uid, $loginName, $loginType)
    {
        //不写入tb_user表，因为会与旧接口产生冲突

        //写入tb_user_login表
        UserLogin::create($uid, $loginName, $loginType);

        //写入tb_user_final表
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            \Prj\Loger::out('create user to tb_user_final failed, because uid duplicate. uid:' . $uid);
            return false;
        }
        $time = time();
        $ModelUserFinal->setField('phone', $loginName);
        $ModelUserFinal->setField('ymdReg', date('Ymd', $time));
        $ModelUserFinal->setField('hisReg', date('His', $time));
        $ModelUserFinal->setField('dtLast', $time);
        $ModelUserFinal->saveToDB();
        return $ModelUserFinal;
    }
}