<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class EventRule extends KVObj
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_event_rule';
    }
    /**
     * @param array $key
     * @return static
     */
    public static function getCopy($key)
    {
        return parent::getCopy(['oid' => $key]);
    }

    public static function getOneByEventId($eventId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['eventId' => $eventId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

}