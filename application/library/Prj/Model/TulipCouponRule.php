<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class TulipCouponRule extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_coupon_rule';
    }

    public static function getOneByRuleId($ruleId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['ruleId' => $ruleId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

}