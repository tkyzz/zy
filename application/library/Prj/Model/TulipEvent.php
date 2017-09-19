<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * 活动事件表
 * @package Prj\Model
 */
class TulipEvent extends _ModelBase
{

    public static $statusMap = [
        'pending' => '待审批',
        'pass' => '通过',
        'refused' => '驳回',
    ];

    public static $activeMap = [
        'wait' => '待上架',
        'on' => '已上架',
        'off' => '已下架',
    ];

    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_event';
    }

    /**
     * 通过类型查找一个活动
     * @param $type
     * @return mixed
     */
    public static function getOneByType($type){
        $db = static::getCopy('')->dbWithTablename();
        $where = [
            'type' => $type,
            'status' => 'pass',
        ];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    /**
     * 通过活动ID查找一个活动
     * @param $eventId
     * @return mixed
     */
    public static function getOneByEventId($eventId){
        $db = static::getCopy('')->dbWithTablename();
        $where = [
            'oid' => $eventId,
        ];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

}