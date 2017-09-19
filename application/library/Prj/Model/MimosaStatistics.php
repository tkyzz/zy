<?php


namespace Prj\Model;

use Sooh2\DB\KVObj;


class MimosaStatistics extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_money_investor_statistics';
    }

    public static function getUserByMimosaId($id){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['investorOid' => $id];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

}