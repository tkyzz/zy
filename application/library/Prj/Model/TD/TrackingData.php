<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/15
 * Time: 10:07
 */
namespace Prj\Model\TD;

class TrackingData extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'User';
        parent::onInit();
        $this->_tbName = 'tb_td_tracking_data_0';
    }

    public static function getLastRecord($where = []){
        return self::getRecord(null , $where , 'rsort createTime');
    }
}