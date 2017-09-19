<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * 活动事件表
 * @package Prj\Model
 */
class TulipRule extends _ModelBase
{

    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_rule';
    }

}