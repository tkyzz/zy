<?php
namespace Rpt\Manage;
class Notice extends \Rpt\KVObjBase
{
    protected static $_dbAndTbName; //数据库对象+表名
    protected function onInit()
    {
        $this->className = 'Notice';
        parent::onInit();
        $this->field_locker=null;//  悲观锁用的字段名，默认使用'rowLock'，设置为null表明不需要悲观锁
        $this->_tbName = 'tb_manage_notice';//表名的默认模板
    }

    public static function getCopy($key)
    {
        return parent::getCopy(['oid' => $key]);
       // return parent::getCopy($key);
    }

    /**
     * 根据oid获取记录
     * @param string $oid
     */
    public static function getNoticeByOid(){

    }

    /**
     * @return \Sooh2\DB\Myisam\Broker
     */
    public static function db(){
        if (!is_array(static::$_dbAndTbName))static::$_dbAndTbName = static::getCopy('')->dbAndTbName();
        return isset(static::$_dbAndTbName[0]) ? static::$_dbAndTbName[0] : null;
    }

    public static function tb(){
        if (!is_array(static::$_dbAndTbName))static::$_dbAndTbName = static::getCopy('')->dbAndTbName();
        return isset(static::$_dbAndTbName[1]) ? static::$_dbAndTbName[1] : null;
    }

    public static function getNoticeOid(){
        $oid=static::db()->getRecordCount(static::tb())+1;
        $length=strlen($oid);
        $md5oid=md5('oid'.time().rand(1,999));
        $oid=substr($md5oid,0,'-'.$length).$oid;
        return $oid;
    }

    public static function getNoticeCount(){
        $count=static::db()->getRecordCount(static::tb());
        return $count;
    }

}


