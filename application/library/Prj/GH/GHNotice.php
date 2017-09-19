<?php

namespace Prj\GH;

/**
 * 国槐的cms的公告表
 *
 * @author simon.wang
 */
class GHNotice extends \Sooh2\DB\KVObj
{

    public function onInit()
    {
        $this->field_locker=null;
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = 't_platform_notice';//表名的默认模板
    }

    /**
     * 
     * @return \Libs\GuoHuai\GHNotice
     */
    public static function getInstance()
    {
        if(self::$_instance ==null){
            self::$_instance = self::getCopy(null);
            //self::$_instance->load();
        }
        return self::$_instance;
    }




}