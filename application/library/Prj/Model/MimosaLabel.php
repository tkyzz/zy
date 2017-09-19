<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 20:30
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * mimosa 标签表
 * Class MimosaProduct
 * @package Prj\Model
 */
class MimosaLabel extends _ModelBase
{
    protected function onInit(){
        $this->className = 'MimosaProduct';
        parent::onInit();
        $this->_tbName = 't_money_platform_label';
    }

    public static function getOneByLabelName($labelName){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['labelName' => $labelName];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }
}