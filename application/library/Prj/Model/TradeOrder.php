<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * 交易订单表
 * @package Prj\Model
 */
class TradeOrder extends _ModelBase
{
    public static $orderStatus_success = ['paySuccess','accepted','confirmed','done']; //交易成功的订单

    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_money_investor_tradeorder';
    }

    /**
     * Hand
     * 查找一条投资成功的订单
     * @param $userId
     * @return mixed
     */
    public static function getOneOrderByUserOid($userId){
        $db = static::getCopy('')->dbWithTablename();
        $where = [
            'investorOid' => $userId,
            'orderStatus' => static::$orderStatus_success,
            'orderType' => 'invest',
        ];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }
}