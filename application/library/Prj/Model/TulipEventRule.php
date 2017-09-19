<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class TulipEventRule extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_event_rule';
    }

    public static function getOneByEventId($eventId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['eventId' => $eventId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

}