<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class ActivityCoupon extends _ModelBase
{
    /*
     [xxx]
      dbs[] = 'mysql.xxx'
     */
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 'tb_activity_coupon_0';
    }

    public static function getOne($where){
        $db = static::getCopy('')->dbWithTablename();
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

}