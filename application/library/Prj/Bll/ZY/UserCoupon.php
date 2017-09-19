<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll\ZY;
use \Lib\Misc\Result;
use \Prj\Loger;

/**
 * Description of User
 *
 * @author simon.wang
 */
class UserCoupon extends \Prj\Bll\_BllBase {

    /**
     * Hand 获取优惠券列表 ZyBusiness
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
           'couponStatus' => $params['status'],
       ];
        if(isset($params['couponId']))$where['couponId'] = $params['couponId'];
       \Prj\Loger::outVal('$content' , $where);

       $size = $params['rows'];

       $content = \Prj\Model\ZyBusiness\UserCoupon::getRecords('' , $where , 'rsort expireTime' , $params['rows'] , ($params['page'] - 1) * $params['rows']);
       $total = \Prj\Model\ZyBusiness\UserCoupon::getRecord('count(1) as total' , $where)['total'];

       return $this->resultOK([
           'content' => $content ,
           'size' => $size,
           'total' => $total,
           'totalPages' => ceil($total / $size),
       ]);
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
        $labels = \Prj\Model\ZyBusiness\ProductLabel::getLabels($params['productId']); //新的产品标签
        $this->log($labels , 'labels');

        $couponBatchRes = \Prj\Bll\ZY\Coupon::getInstance()->getCouponIdByLabelCodes($labels);
        if(!$this->checkRes($couponBatchRes))return $couponBatchRes;
        $couponBatchs = $couponBatchRes['data']['list'];
        $this->log($couponBatchs , '$couponBatchs');
        //没有此标签的券
        if(empty($couponBatchs))return $this->resultOK([
            'content' => [] ,
            'size' => 1000,
            'total' => 0,
            'totalPages' => 0,
        ]);
        $listRes = $this->getUserCoupon([
            'userId' => $params['userId'],
            'status' => ['notUsed'],
            'couponId' => $couponBatchs,
            'page' => 1,
            'rows' => 1000,
        ]);

        return $listRes;

    }

    /**
     * Hand 老接口拦截的格式化
     * @param $res
     * @param string $type
     */
//   public function formatForOldver(&$res , $type = ''){
//       if(isset($res['data']) && isset( $res['data']['content'] )  ){
//           foreach ($res['data']['content'] as &$v){
//               if($type == 'old'){
//                   $v['finish'] .= ' 00:00:00';
//               }else{
//                   $this->str2time($v['finish']);
//                   $this->str2time($v['leadTime']);
//                   $this->str2time($v['useTime']);
//               }
//               $v['amount'] = floatval($v['amount']);
//               $v['minAmt'] = floatval($v['investAmount']);
//           }
//       }
//       if($type == 'old'){
//           $res['data']['rows'] = $res['data']['content'];
//           unset($res['data']['content']);
//       }
//   }

    /**
     * Hand 我的券列表格式化
     * @param $res
     */
   public function formatForMyCoupons(&$res){
       if(isset($res['data']) && isset( $res['data']['content'] )  ){
           foreach ($res['data']['content'] as $k => &$v){
               $tmp['oid'] = $v['ucId'];
               $tmp['name'] = $v['name'];
               $tmp['type'] = strtoupper($v['couponType']);
               $tmp['typeCH'] = \Prj\Model\Coupon::$type_map[$v['couponType']];
               $labelNamesRes = $this->getUserCouponLabelsDetail($v);
               $this->log($labelNamesRes , '$labelNamesRes');
               if(!$this->checkRes($labelNamesRes)){
                   \Prj\Loger::out($labelNamesRes['message'] , LOG_ERR);
                   $labelNames = 'NaN';
               }else{
                   $labelNames = implode(',' , $labelNamesRes['data']['labelNames']);
               }
               $tmp['labelId'] = $labelNamesRes['data']['labelIds'];
               $tmp['products'] = $labelNamesRes['data']['labelNames'];
               $tmp['productsCH'] = !$labelNames ? '适用全场' : ('仅限投资'.$labelNames);
               $tmp['investAmount'] = floatval($v['limitInvestAmount']);
               $tmp['investAmountCH'] = $v['limitInvestAmount'] ? ('满'.$tmp['investAmount'].'元使用') : '';
               $tmp['amount'] = floatval($v['couponAmount']);
               $tmp['finish'] = strtotime($v['expireTime']);
               $tmp['start'] = strtotime($v['effectTime']);
               $tmp['_start'] = $v['effectTime'];
               $tmp['_finish'] = $v['expireTime'];
               $v = $tmp;
           }
       }
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

    /**
     * Hand 获取用户卡券的标签 ZyBusiness
     * @param $userCouponInfo
     * @return array
     */
    public function getUserCouponLabels($userCouponInfo){
       if(!\Lib\Misc\Result::paramsCheck($userCouponInfo , ['isLimitLabel','couponId'])){
           return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
       }

       if($userCouponInfo['isLimitLabel'] == 0){
           return $this->resultOK([
               'list' => [],
           ]);
       }

       return \Prj\Bll\ZY\Coupon::getInstance()->getCouponLabels($userCouponInfo['couponId']);
    }

    /**
     * Hand 获取卡券标签详情 ZyBusiness
     * @param $userCouponInfo
     * @return array
     */
    public function getUserCouponLabelsDetail($userCouponInfo){
        if(!\Lib\Misc\Result::paramsCheck($userCouponInfo , ['couponId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        return \Prj\Bll\ZY\Coupon::getInstance()->getCouponLabelsDetail($userCouponInfo['couponId']);
    }

    public function add($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['couponInfo','userId','amount','isFloat'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        if(!\Lib\Misc\Result::paramsCheck($params['couponInfo'] , [
            'type','oid','name','investAmount','labels'
        ])){
            return $this->resultError('券参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $couponInfo = $params['couponInfo'];
        $time = date('Y-m-d H:i:s');
        $couponInfo['disableDate'] = $couponInfo['disableDate'] ?: $couponInfo['expire'];
        $couponInfo['disableDate'] = (!empty($params['expire']) || $params['expire'] === 0) ? $params['expire'] : $couponInfo['disableDate'];
        $finish = date('Y-m-d 23:59:59' , strtotime('+'.$couponInfo['disableDate'].' days'));
        $amount = $params['amount'];
        $this->log($couponInfo['labels'] , 'labels');
        $isLimitLabel = $couponInfo['labels'] ? 1 : 0;
        $labelRes = \Prj\Bll\ZY\Coupon::getInstance()->getCouponLabelsDetail($couponInfo['oid']);
        if(!$this->checkRes($labelRes))return $labelRes;
        $this->log($labelRes , 'labelRes');
        $limitLabels = implode(',' , $labelRes['data']['labelNames']);
        $ucId = \Lib\Misc\StringH::createOid();
        $insertData = [
            'ucId' => $ucId,
            'couponId' => $couponInfo['oid'],
            'userId' => $params['userId'],
            'name' => $couponInfo['name'],
            'couponType' => strtoupper($couponInfo['type']),
            'couponStatus' => strtoupper('notUsed'),
            'couponAmount' => $amount,
            'limitInvestAmount' => $params['investAmount'] ?: $couponInfo['investAmount'],
            'limitLabels' => $limitLabels,
            'isLimitLabel' => $isLimitLabel,
            'lenderTime' => $time,
            'effectTime' => $time,
            'expireTime' => $finish,
            //'useTime' => '',
            'createTime' => $time,
            'updateTime' => $time,
        ];
        $this->log($insertData , 'insertData to zy');
        $ret = \Prj\Model\ZyBusiness\UserCoupon::saveOne($insertData);
        if($ret){
            $insertData['oid'] = $ucId;
            $this->log('zy 卡券发放成功');
            return $this->resultOK([
                'info' => $insertData,
            ]);
        }else{
            return $this->resultError('卡券发放失败!');
        }
    }
}
