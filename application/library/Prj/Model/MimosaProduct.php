<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 20:30
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * mimosa产品表
 * Class MimosaProduct
 * @package Prj\Model
 */
class MimosaProduct extends _ModelBase
{
    public static $codeMap = [
        '002' => '新手标',
        '004' =>'稳定收益',
        '005' =>'悦享盈',
        '003' =>'悦月盈',
        '001' => '掌薪宝',
    ];

    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_gam_product';
    }

    public static function getOneByProId($productId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['oid' => $productId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }
}