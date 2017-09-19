<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * 活动事件表
 * @package Prj\Model
 */
class Event extends KVObj
{

    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_event';
    }
    /**
     * @param array $key
     * @return static
     */
    public static function getCopy($key)
    {
        return parent::getCopy(['oid' => $key]);
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