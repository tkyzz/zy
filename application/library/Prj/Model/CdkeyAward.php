<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class CdkeyAward extends _ModelBase
{
    public static $type_coupon = 'COUPON';
    /*
     [xxx]
      dbs[] = 'mysql.xxx'
     */
    protected function onInit(){
        $this->className = 'User';
        parent::onInit();
        $this->_tbName = 'tb_cdkey_award';
    }

    //兑换码列表
    public function getAwardList(){
        $sql = 'SELECT cdk.oid AS cdkId,cdk.name AS name,cdk.count AS `count`,cdk.getCount AS getCount,cdk.words AS words,cdk.start AS start,cdk.finish AS finish,cou.title AS title,cdk.statusCode,cdka.typeCode,cdka.createTime AS createTime 
                FROM jz_db.tb_cdkey as cdk 
                LEFT JOIN jz_db.tb_cdkey_award cdka ON cdk.oid = cdka.cdkeyId 
                LEFT JOIN jz_db.tb_coupon_0 AS cou ON cdka.couponId = cou.oid';
        return self::query($sql);
    }

    //兑换码详情
    public function getAwardDetail($oid){
        if( empty($oid) ){
            return false;
        }
        $sql = 'SELECT cdk.oid AS cdkId,cdk.name AS name,cdk.count AS `count`,cdk.getCount AS getCount,cdk.words AS words,cdk.start AS start,cdk.finish AS finish,cou.title AS title,cdk.statusCode,cdka.typeCode,cdka.couponId,cdka.createTime AS createTime 
                FROM jz_db.tb_cdkey as cdk 
                LEFT JOIN jz_db.tb_cdkey_award cdka ON cdk.oid = cdka.cdkeyId 
                LEFT JOIN jz_db.tb_coupon_0 AS cou ON cdka.couponId = cou.oid
                WHERE cdk.oid='.$oid;
        return self::query($sql);
    }

    //插入一条
    public function insertOne($params){
        $time = time();
        $obj = parent::getCopy(true);
        //cdkeyId
        $obj->setField('cdkeyId',$params['cdkeyId']);
        //typeCode
        $obj->setField('typeCode',$params['typeCode']);
        //statusCode
        $obj->setField('statusCode',$params['statusCode']);
        //couponId
        $obj->setField('couponId',$params['coupon']);
        //createTime
        $obj->setField('createTime',date('Y-m-d H:i:s'));
        if( $obj->saveToDB() ){
            return $obj->pkey()['oid'];
        }else{
            return false;
        }
    }

    //更新一条记录
    public static function updateOne($inputs = [],$where = [])
    {
        $params = array();
        if( !empty($inputs['typeCode']) ){
            $params['typeCode'] = $inputs['typeCode'];
        }
        if( !empty($inputs['statusCode']) ){
            $params['statusCode'] = $inputs['statusCode'];
        }
        if( !empty($inputs['coupon']) ){
            $params['couponId'] = $inputs['coupon'];
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
}