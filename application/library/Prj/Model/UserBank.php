<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 20:30
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * 用户充值表
 * Class UserBank
 * @package Prj\Model
 */
class UserBank extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_wfd_user_bank';
    }

    /**
     * Hand
     * 查询用户的一张卡
     * @param $userId
     * @return mixed
     */
    public static function getOneCardByUserOid($userId){
        $db = static::getCopy('')->dbWithTablename();
        return $db->getRecord($db->kvobjTable() , '*' , ['userOid' => $userId]);
    }
}