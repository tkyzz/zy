<?php


namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * @package Prj\Model
 */
class GamProduct extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_gam_product';
    }

    public static function getProductByOid($productOid){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['oid' => $productOid];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }


}