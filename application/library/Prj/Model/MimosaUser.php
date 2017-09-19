<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 20:30
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * mimosa用户表
 * ucid ：userOid
 * setid ：memberId
 * Class UserBank
 * @package Prj\Model
 */
class MimosaUser extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_money_investor_baseaccount';
    }

    public static function getUserByUcUserId($userId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['userOid' => $userId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    public static function getUserByMiUserId($userId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['oid' => $userId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }
}