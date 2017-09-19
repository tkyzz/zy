<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/1
 * Time: 16:35
 */
namespace Prj\Model\Payment;

use Prj\Model\_ModelBase;

class BankOrder extends _ModelBase
{
    public static $type_recharge = 'RECHARGE';
    public static $type_withdraw = 'WITHDRAW';

    public function onInit()
    {
        $this->className = "Payment";
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = "t_bank_order";
    }

    public static function getFirstChargeInfo($userId){
        $firstCharge = \Prj\Model\Payment\BankOrder::getRecord(null , [
            'userId' => $userId,
            'orderType' => self::$type_recharge,
            'orderStatus' => 'SUCCESS'
        ] , 'sort completeTime sort orderId');
        return $firstCharge;
    }
}