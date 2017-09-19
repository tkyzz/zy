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
class MimosaLabelProduct extends _ModelBase
{
    protected function onInit(){
        $this->className = 'MimosaProduct';
        parent::onInit();
        $this->_tbName = 't_money_platform_label_product';
    }

    public static function getOne($where = []){
        $db = static::getCopy('')->dbWithTablename();
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }
}