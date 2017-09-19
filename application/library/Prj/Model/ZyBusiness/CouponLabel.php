<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 16:09
 */

namespace Prj\Model\ZyBusiness;

class CouponLabel extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'ZyBusiness';
        parent::onInit();
        $this->_tbName = 'tpf_coupon_label';
    }
}