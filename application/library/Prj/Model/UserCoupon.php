<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-19 16:25
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

class UserCoupon extends _ModelBase
{
    protected function onInit()
    {
        $this->className  = "ZyBusiness";
        parent::onInit();
        $this->_tbName = 't_user_coupon';
    }
}