<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/7
 * Time: 10:52
 */
namespace Prj\Model\Tulip;

class UserCoupon extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'Tulip';
        parent::onInit();
        $this->_tbName = 't_user_coupon';
    }
}