<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/3
 * Time: 20:05
 */
namespace Prj\Bll\ActRuleZy;

class ActOneChui extends \Prj\Bll\ActRuleZy\_ActRuleBase
{
    protected $activityTypes = 'hammer';

    protected function getActCode(){
        return \Prj\Model\Activity::onechui_code;
    }

    protected function getAmount($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['productId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $productId = $params['productId'];
        $productInfo = \Prj\Model\ZyBusiness\ProductInfo::getOne(['productId' => $productId]);
        if($productInfo['productType'] !== \Prj\Model\ZyBusiness\ProductInfo::$type_ding){
            return $this->resultError('活动仅适用定期产品');
        }
        $orderInfo = \Prj\Model\ZyBusiness\TradOrder::getLastByPrdId($productId);
        if(empty($orderInfo))return $this->resultError('查无订单');
        $this->orderInfo = $orderInfo;
        $this->userId = $orderInfo['userId'];
        return $this->resultOK([
            'amount' => $orderInfo['orderAmount'],
        ]);
    }
}