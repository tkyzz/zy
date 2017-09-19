<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-13 10:47
 */

namespace Lib\Services;

use Prj\Tool\TimeTool;
use Prj\Tool\Random;
use Sooh2\Misc\Ini;

/**
 * 本地发券的服务,这里只支持发放系统已配置的券
 * Class CheckinBook
 * @package Lib\Services
 */

class SendCouponLocal extends \Prj\Bll\_BllBase
{
    protected $amount;

    protected $eventId;

    protected $couponId;

    protected $investAmount;

    protected $expire;

    /**
     * Hand
     * @param $amount 单位分
     * @return $this
     */
    public function setAmount($amount){
        \Prj\Loger::outVal('指定金额: ', $amount);
        $this->amount = $amount;
        return $this;
    }

    public function setEventId($eventId){
        $this->eventId = $eventId;
        return $this;
    }

    public function setCouponId($couponId){
        $this->couponId = $couponId;
        return $this;
    }

    public function setInvestAmount($investAmount){
        $this->investAmount = $investAmount;
        return $this;
    }

    public function setExpire($days){
        $this->expire = $days;
        return $this;
    }

    public function sendCoupon($userId){
        \Prj\Loger::setKv('SendCouponLocal');
        \Prj\Loger::out('begin sendCoupon...');
        $amount = $this->amount;
        $eventId = $this->eventId;
        $couponId = $this->couponId;
        $investAmount = $this->investAmount;
        $expire = $this->expire;
        return \Prj\Bll\Coupon::getInstance()->sendCouponByCouponTplId($userId , $couponId , $amount , $eventId , $investAmount , $expire);
    }

    protected function free(){
        $this->amount = null;
        $this->eventId = null;
    }
}
