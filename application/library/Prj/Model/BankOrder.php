<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 20:30
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * 充值提现表
 * Class UserBank
 * @package Prj\Model
 */
class BankOrder extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_bank_order';
    }

    /**
     * Hand
     * 获取一条卡记录
     * @param $userId
     * @return array|null
     */
    public static function getOneRechargeByUserOid($userId){
        $db = static::getCopy('')->dbWithTablename();
        $where = [
            'userOid' => $userId,
            'status' => 1,
        ];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }
}