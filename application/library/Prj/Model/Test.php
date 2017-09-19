<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class Test extends _ModelBase
{
    /*
     [xxx]
      dbs[] = 'mysql.xxx'
     */
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 'xxx';
    }

}