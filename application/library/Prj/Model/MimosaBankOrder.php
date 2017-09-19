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
class MimosaBankOrder extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_money_investor_bankorder';
    }

    public static function getOne($where){
        $db = self::getCopy('')->dbWithTablename();
        return $db->getRecord($db->kvobjTable() , '*' , $where , 'sort completeTime');
    }
}