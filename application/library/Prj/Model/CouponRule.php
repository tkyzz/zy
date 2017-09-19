<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class CouponRule extends KVObj
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_coupon_rule';
    }
    /**
     * @param array $key
     * @return static
     */
    public static function getCopy($key)
    {
        return parent::getCopy(['oid' => $key]);
    }

    public static function getOneByRuleId($ruleId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['ruleId' => $ruleId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

}