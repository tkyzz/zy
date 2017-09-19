<?php


namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * @package Prj\Model
 */
class OldDriverPassenger extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 'tb_olddriver_passenger_0';
    }


}