<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/7/3
 * Time: 11:48
 */
namespace Prj\Model\Mimosa;

class LabelProduct extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'Mimosa';
        parent::onInit();
        $this->_tbName = 't_money_platform_label_product';
    }

    public static function getLabels($productId){
        $where = [
            'productOid' => $productId,
        ];

        $records = self::getRecords(null , $where);
        if(!count($records))return [];

        $tmp = [];
        foreach ($records as $v){
            $tmp[] = $v['labelOid'];
        }
        return $tmp;
    }

}