<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/10
 * Time: 10:37
 */
namespace Prj\Bll\Award;

class AwardCoupon extends _AwardBase
{
    public function send($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['couponId' , 'userId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $couponId = $params['couponId'];
        $userId = $params['userId'];
        $sender = \Lib\Services\SendCouponLocal::getInstance();
        return $sender->setCouponId($couponId)->sendCoupon($userId);
    }
}