<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/7/3
 * Time: 11:48
 */
namespace Prj\Model\Mimosa;

class Label extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'Mimosa';
        parent::onInit();
        $this->_tbName = 't_money_platform_label';
    }

    public static function getLabelMap(){
        $records = self::getRecords(null , ['isOk' => 'yes']);
        $tmp = [];
        foreach ($records as $v){
            $tmp[$v['oid']] = $v['labelName'];
        }
        return $tmp;
    }
}