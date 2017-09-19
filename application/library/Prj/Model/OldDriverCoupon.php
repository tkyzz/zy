<?php


namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * @package Prj\Model
 */
class OldDriverCoupon extends _ModelBase
{
    protected function onInit(){
        $this->className = 'OldDriverCoupon';
        parent::onInit();
        $this->_tbName = 'tb_olddriver_coupon_0';
    }


}