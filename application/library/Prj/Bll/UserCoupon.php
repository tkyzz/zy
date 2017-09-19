<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;
use \Lib\Misc\Result;
use \Prj\Loger;

/**
 * Description of User
 *
 * @author simon.wang
 */
class UserCoupon extends _BllBase {

    /**
     * Hand 获取优惠券列表
     * content=列表,size=单页长度,total=总条数,totalPages=总页数
     * @param array $params
     * @return array
     */
   public function getUserCoupon($params = []){
       // \Prj\Loger::outVal('params' , $params);
       if(!\Lib\Misc\Result::paramsCheck($params , ['userId','status','page','rows'])){
           return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
       }

       $where = [
           'userId' => $params['userId'],
           'status' => $params['status'],
       ];
        if(isset($params['couponBatch']))$where['couponBatch'] = $params['couponBatch'];


       $size = $params['rows'];

       $content = \Prj\Model\Tulip\UserCoupon::getRecords('' , $where , 'rsort finish' , $params['rows'] , ($params['page'] - 1) * $params['rows']);
       $total = \Prj\Model\Tulip\UserCoupon::getRecord('count(1) as total' , $where)['total'];
       return $this->resultOK([
           'content' => $content,
           'size' => $size,
           'total' => $total,
           'totalPages' => ceil($total / $size),
       ]);
   }

    /**
     * Hand 老接口拦截的格式化
     * @param $res
     * @param string $type
     */
   public function formatForOldver(&$res , $type = ''){
       if(isset($res['data']) && isset( $res['data']['content'] )  ){
           foreach ($res['data']['content'] as &$v){
               if($type == 'old'){
                   $v['finish'] .= ' 00:00:00';
               }else{
                   $this->str2time($v['finish']);
                   $this->str2time($v['leadTime']);
                   $this->str2time($v['useTime']);
               }
               $v['amount'] = floatval($v['amount']);
               $v['minAmt'] = floatval($v['investAmount']);
           }
       }
       if($type == 'old'){
           $res['data']['rows'] = $res['data']['content'];
           unset($res['data']['content']);
       }
   }

    /**
     * Hand 我的券列表格式化
     * @param $res
     */
   public function formatForMyCoupons(&$res){
       if(isset($res['data']) && isset( $res['data']['content'] )  ){
           foreach ($res['data']['content'] as $k => &$v){
               $tmp['oid'] = $v['oid'];
               $tmp['name'] = $v['name'];
               $tmp['typeCH'] = \Prj\Model\Coupon::$type_map[$v['type']];
               $tmp['productsCH'] = $v['products'] == '适用全场' ? $v['products'] : ('仅限投资'.$v['products']);
               $tmp['investAmountCH'] = $v['investAmount'] ? ('满'.floatval($v['investAmount']).'元使用') : '';
               $tmp['amount'] = floatval($v['amount']);
               $tmp['finish'] = strtotime($v['finish']);
               $tmp['start'] = strtotime($v['start']);
               $tmp['_start'] = $v['start'];
               $tmp['_finish'] = $v['finish'];
               $v = $tmp;
           }
       }
   }

    /**
     * Hand 根据产品ID获取我的券列表
     * @param array $params
     * @return array
     */
   public function getMyListByProId($params = []){
       if(!\Lib\Misc\Result::paramsCheck($params , ['productId' , 'userId'])){
           return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
       }
       $labels = \Prj\Model\Mimosa\LabelProduct::getLabels($params['productId']);
       $this->log($labels , 'labels');

       $labelCodes = [];
       foreach ($labels as $v){
           $la = \Prj\Model\MimosaLabel::getCopy($v);
           $la->load();
           $labelCodes[] = $la->getField('labelCode');
       }
       if(empty($labelCodes))$this->fatalErr('产品标签不能为空');

       $couponBatchRes = \Prj\Bll\Coupon::getInstance()->getCouponIdByLabelCodes($labelCodes);
       if(!$this->checkRes($couponBatchRes))return $couponBatchRes;
       $couponBatchs = $couponBatchRes['data']['list'];
       if(empty($couponBatchs))return $this->resultOK([
           'list' => [],
       ]);
       $listRes = $this->getUserCoupon([
           'userId' => $params['userId'],
           'status' => ['notUsed'],
           'couponBatch' => $couponBatchs,
           'page' => 1,
           'rows' => 1000,
       ]);

       return $listRes;

   }



    /**
     * Hand 获取用户未使用的券的数量
     * @param array $params
     * @return array
     */
   public function getNotUsedCount($params = []){
       if(!\Lib\Misc\Result::paramsCheck($params , ['userId'])){
           return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
       }
       $params['status'] = ['notUsed'];
       $params['page'] = 1;
       $params['rows'] = 1;
       $res = $this->getUserCoupon($params);
       if(!$this->checkRes($res))return $res;
       return $this->resultOK([
           'total' => $res['data']['total'],
       ]);
   }



   public function getUserCouponAmount($uid){
       $where=array(
           'userId'=>$uid,
           'status'=>'notUsed',
       );
       $arr=\Prj\Model\Tulip\UserCoupon::getRecords('amount',$where);
       $num=0;
       foreach ($arr as $k=>$v){
           $num+=$v['amount'];
       }
       return $num;
   }
}
