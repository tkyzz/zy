<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class Flexible extends _ModelBase
{
    protected static $_flexTbName = '';

    protected static $_flexClassName = '';
    /*
     [xxx]
      dbs[] = 'mysql.xxx'
     */
    protected function onInit(){
        if(empty(self::$_flexClassName) || empty(self::$_flexTbName))throw new \ErrorException('未定义数据库和表');
        $this->className = self::$_flexClassName;
        parent::onInit();
        $this->_tbName = self::$_flexTbName;
    }

    public static function reset($dbConf = null , $tbName = null){
        if($dbConf)self::$_flexClassName = $dbConf;
        if($tbName)self::$_flexTbName = $tbName;
        return true;
    }
}