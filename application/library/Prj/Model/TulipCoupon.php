<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class TulipCoupon extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_coupon';
    }

    public static function getOneByOid($oid){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['oid' => $oid];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    public static function save($insert){
        $db = static::getCopy('')->dbWithTablename();
        return $db->addRecord($db->kvobjTable() , $insert);
    }

    public static function updateNum($oid , $field , $change){
        if($change < 0){
            $whereOt = " AND $field >= ". ($change * -1) ." ";
        }else{
            $whereOt = '';
        }
        $table = self::getTbname();
        $sql = <<<sql
            UPDATE $table SET $field = $field + $change WHERE oid = '$oid' $whereOt LIMIT 1 ;
sql;
        return self::query($sql);
    }

}