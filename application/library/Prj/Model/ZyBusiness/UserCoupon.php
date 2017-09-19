<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-19 16:25
 */

namespace Prj\Model\ZyBusiness;

use Prj\Model\_ModelBase;
use Sooh2\DB\KVObj;

class UserCoupon extends _ModelBase
{
    protected function onInit()
    {
        $this->className  = "ZyBusiness";
        parent::onInit();
        $this->_tbName = 'tpf_user_coupon';
    }
}