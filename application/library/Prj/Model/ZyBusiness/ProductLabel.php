<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 16:09
 */

namespace Prj\Model\ZyBusiness;

class ProductLabel extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'ZyBusiness';
        parent::onInit();
        $this->_tbName = 'tpf_product_label';
    }

    public static function getLabels($productId){
        $where = [
            'productId' => $productId,
        ];

        $records = self::getRecords(null , $where);
        if(!count($records))return [];

        $tmp = [];
        foreach ($records as $v){
            $tmp[] = $v['labelId'];
        }
        return $tmp;
    }
}