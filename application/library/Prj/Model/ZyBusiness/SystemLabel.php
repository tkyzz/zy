<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 16:09
 */

namespace Prj\Model\ZyBusiness;

class SystemLabel extends \Prj\Model\_ModelBase
{
    protected static $pkeyName = 'labelId';

    protected function onInit(){
        $this->className = 'ZyBusiness';
        parent::onInit();
        $this->_tbName = 'tpf_system_label';
    }

    public static function getLabelMap($labelType = ['general']){
        $records = self::getRecords(null , ['isUsed' => 1 , 'labelType' => $labelType]);
        $tmp = [];
        foreach ($records as $v){
            $tmp[$v['labelId']] = $v['labelName'];
        }
        return $tmp;
    }
}