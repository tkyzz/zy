<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class TulipCouponRange extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_coupon_range';
    }

}