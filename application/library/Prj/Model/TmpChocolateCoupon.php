<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/16
 * Time: 15:44
 */

namespace Prj\Model;

class TmpChocolateCoupon extends _ModelBase
{
    protected function onInit(){
        $this->className = 'User';
        parent::onInit();
        $this->_tbName = 'tb_tmp_chocolate_coupon_0';
    }

}