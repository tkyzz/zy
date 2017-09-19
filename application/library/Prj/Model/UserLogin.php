<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-29 14:17
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

class UserLogin extends KVObj
{
    public static function getCopy($loginName = null, $loginType = 'phone')
    {
        return parent::getCopy($loginName === null ? null : ['loginname' => $loginName, 'logintype' => $loginType]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_user_login_0';
    }

    public static function create($uid, $loginName, $loginType)
    {
        $Model = self::getCopy(null);
        $Model->setField('loginName', $loginName);
        $Model->setField('loginType', $loginType);
        $Model->setField('uid', $uid);
        $time = time();
        $Model->setField('createTime', date('YmdHis', $time));
        $Model->setField('createYmd', date('Ymd', $time));
        $Model->saveToDB();
        return $Model;
    }
}