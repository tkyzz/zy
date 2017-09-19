<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class Cdkey extends _ModelBase
{
    /*
     [xxx]
      dbs[] = 'mysql.xxx'
     */
    protected function onInit(){
        $this->className = 'User';
        parent::onInit();
        $this->_tbName = 'tb_cdkey';
    }

    public static function getOneByWords($words){
        if(empty($words))return [];
        $where = [
            'words' => $words,
        ];
        return self::getRecord(null , $where , 'rsort start');
    }

    //更新一条记录
    public static function updateOne($inputs = [],$where = [])
    {
        $params = array();
        if( !empty($inputs['name']) ){
            $params['name'] = $inputs['name'];
        }
        if( !empty($inputs['count']) ){
            $params['count'] = $inputs['count'];
        }
        if( !empty($inputs['words']) ){
            $params['words'] = $inputs['words'];
        }
        if( !empty($inputs['start']) ){
            $params['start'] = $inputs['start'];
        }
        if( !empty($inputs['finish']) ){
            $params['finish'] = $inputs['finish'];
        }
        if( isset($inputs['statusCode']) ){
            $params['statusCode'] = $inputs['statusCode'];
        }
        if( empty($params) || empty($where)){
            return false;
        }
        $ret = parent::updateOne($params,$where);
        if( $ret ){
            return true;
        }else{
            return false;
        }
    }

    public static function insertOne($params)
    {
//        $time = time();
        $cdkeyObj = parent::getCopy(true);
        //name
        $cdkeyObj->setField('name',$params['name']);
        //count
        $cdkeyObj->setField('count',$params['count']);
        //words
        $cdkeyObj->setField('words',$params['words']);
        //start
        $cdkeyObj->setField('start',$params['start']);
        //finish
        $cdkeyObj->setField('finish',$params['finish']);
        //statusCode
        $cdkeyObj->setField('statusCode',$params['statusCode']);
        //createTime
        $cdkeyObj->setField('createTime',date('Y-m-d H:i:s'));
        if( $cdkeyObj->saveToDB() ){
            return $cdkeyObj->pkey()['oid'];
        }else{
            return false;
        }
    }
}