<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 20:30
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * UC用户表
 * Class UserBank
 * @package Prj\Model
 */
class UcUser extends _ModelBase
{
    public static function getCopy($oid = '')
    {
        return parent::getCopy(['oid' => $oid]);
    }

    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_wfd_user';
    }

    public static function getUserByOid($userId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['oid' => $userId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    public static function getCopyByPhone($phone)
    {
        return parent::getCopy(['userAcc' => $phone]);
    }
}