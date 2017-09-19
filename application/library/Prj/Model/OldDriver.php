<?php


namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * @package Prj\Model
 */
class OldDriver extends _ModelBase
{
    protected function onInit(){
        $this->className = 'OldDriver';
        parent::onInit();
        $this->_tbName = 'tb_olddriver_0';
    }


}