<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-18 15:36
 */

namespace Prj\Model;

class WechatUser extends _ModelBase
{
    public static function getCopy($openid = '')
    {
        return parent::getCopy(['openid' => $openid]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_wechat_user_0';
    }
}