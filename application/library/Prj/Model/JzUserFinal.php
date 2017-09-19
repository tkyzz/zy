<?php


namespace Prj\Model;

use Sooh2\DB\KVObj;


class JzUserFinal extends _ModelBase
{
    public static function getCopy($userId = '')
    {
        return parent::getCopy(['userId' => $userId]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_user_final';
    }

    public static function getUserByWfdUserId($userId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['wfdUserId' => $userId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    public static function getUserByUserId($userId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['userId' => $userId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

}