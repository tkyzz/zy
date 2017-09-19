<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class CdkeyUser extends _ModelBase
{
    public static $status_used = 'USED';
    public static $status_unused = 'UNUSED';
    /*
     [xxx]
      dbs[] = 'mysql.xxx'
     */
    protected function onInit(){
        $this->className = 'User';
        parent::onInit();
        $this->_tbName = 'tb_cdkey_user_0';
    }

    public static function add($params = []){
        if(empty($params['statusCode'])){
            \Prj\Loger::out('缺少参数 statusCode!!!');
            return null;
        }
        if(empty($params['fromUserId'])){
            \Prj\Loger::out('缺少参数 fromUserId!!!');
            return null;
        }
        $model = self::getCopy(\Lib\Misc\StringH::createOid());
        $model->load();
        if($model->exists()){
            \Prj\Loger::out('记录已存在!!!');
            return null;
        }
        foreach ($params as $k => $v){
            $model->setField($k , $v);
        }
        $model->setField('createTime' , date('Y-m-d H:i:s'));
        return $model->saveToDB();
    }

    public static function used($oid , $userId){
        if(empty($userId)){
            \Prj\Loger::out('缺少参数 userId!!!');
            return null;
        }
        $model = self::getCopy($oid);
        $model->load(true);
        if($model->getField('statusCode') != self::$status_unused)return null;
        $model->setField('statusCode' , self::$status_used);
        $model->setField('userId' , $userId);
        $model->setField('useTime' , date('Y-m-d H:i:s'));
        return $model->saveToDB();
    }
}