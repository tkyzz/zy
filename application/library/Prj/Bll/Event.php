<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;
use \Lib\Misc\Result;

/**
 * Description of User
 *
 * @author simon.wang
 */
class Event extends _BllBase {
    /**
     * 通过活动事件的类型获取对应的奖励信息
     * @param $type
     * @return array|null
     */
    public static function getCouponByEventType($type){
        $event = \Prj\Model\Event::getOneByType($type);
        if(empty($event)){
            error_log($type . ' \Prj\Model\Event::getOneByType null');
            return [];
        }else if($event['isdel'] == 'no'){
            error_log('event isdel = no');
            return [];
        }
        $eventRule = \Prj\Model\EventRule::getOneByEventId($event['oid']);
        if(empty($eventRule)){
            error_log($type.' \Prj\Model\EventRule::getOneByEventId null');
            return [];
        }
        $couponRule = \Prj\Model\CouponRule::getOneByRuleId($eventRule['ruleId']);
        if(empty($couponRule)){
            error_log($type.' \Prj\Model\CouponRule::getOneByRuleId null');
            return [];
        }
        $coupon = \Prj\Model\Coupon::getOneByOid($couponRule['couponId']);
        if(empty($coupon)){
            error_log($type.' \Prj\Model\Coupon::getOneByOid null');
            return [];
        }
        return $coupon;
    }

    public static function getCouponInfoByEventId($eventId){
        if(empty($eventId)){
            return Result::get(RET_ERR , '活动ID不能为空!');
        }
        $eventRule = \Prj\Model\EventRule::getOneByEventId($eventId);
        if(empty($eventRule)){
            return Result::get(RET_ERR , '未定义活动规则!');
        }
        $couponRule = \Prj\Model\CouponRule::getOneByRuleId($eventRule['ruleId']);
        if(empty($couponRule)){
            return Result::get(RET_ERR , '未定义红包规则!');
        }
        $coupon = \Prj\Model\Coupon::getOneByOid($couponRule['couponId']);
        if(empty($coupon)){
            return Result::get(RET_ERR , '未查询到红包信息!');
        }
        return Result::get(RET_SUCC , '' , $coupon);
    }

    /**
     * 检查一个活动是否可用
     * @param $eventInfo
     * @return array
     */
    public static function checkOpenEvent($eventInfo){
        if(!Result::paramsCheck($eventInfo , ['statusCode' , 'isdel' , 'active' ]))
            return Result::get(RET_ERR , '参数错误['. Result::$errorParam .']');
        // if($eventInfo['type'] != 'custom')return Result::get(RET_ERR , '只能操作自定义活动');
        if($eventInfo['statusCode'] != 'pass')return Result::get(RET_ERR , '活动尚未审核通过');
        if($eventInfo['isdel'] != 'yes')return Result::get(RET_ERR , '活动已失效');
        if($eventInfo['active'] != 'on')return Result::get(RET_ERR , '活动已经下架');
        if(time() < strtotime($eventInfo['startTime']))return Result::get(RET_ERR , '活动尚未开始');
        if(time() > strtotime($eventInfo['finishTime']))return Result::get(RET_ERR , '活动已经结束');
        return Result::get(RET_SUCC);
    }

}
