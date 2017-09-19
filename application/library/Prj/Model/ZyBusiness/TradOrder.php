<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 16:09
 */

namespace Prj\Model\ZyBusiness;

class TradOrder extends \Prj\Model\_ModelBase
{
    public static $status_confirm = 'CONFIRMED';

    public static $type_invest = 'INVEST';

    protected function onInit(){
        $this->className = 'ZyBusiness';
        parent::onInit();
        $this->_tbName = 'tpf_investor_trade_order'; //tpf_investor_tradorder
    }

    public static function getLastByPrdId($productId){
        return self::getRecord(null , [
            'orderType' => self::$type_invest,
            'orderStatus' => self::$status_confirm,
            'productId' => $productId,
        ] , 'rsort createTime');
    }

    public static function getMaxByPrdId($productId){
        return self::getRecord(null , [
            'orderType' => self::$type_invest,
            'orderStatus' => self::$status_confirm,
            'productId' => $productId,
        ] , 'rsort orderAmount sort orderId');
    }

    public static function updActTag($orderId , $tag){
        $where = ['orderId' => $orderId];
        $orderInfo = self::getOne($where);
        if(empty($orderInfo))return false;
        $tags = empty($orderInfo['activityTypes']) ? [] : explode(',' , $orderInfo['activityTypes']);
        if(!in_array($tag , $tags))$tags[] = $tag;
        return self::updateOne([
            'activityTypes' => implode(',' , $tags),
        ] , $where);
    }

    public static function getLastDingOrder($userId){
        $dbName = self::getDbname();
        $tbName = self::getTbname();
        $prtTbName = \Prj\Model\ZyBusiness\ProductInfo::getTbname();
        $sql = <<<sql
SELECT a.* FROM $tbName a LEFT JOIN $prtTbName b ON a.productId = b.productId
WHERE a.userId = '$userId' AND a.orderType = 'INVEST' AND a.orderStatus = 'CONFIRMED'
AND b.productType = 'REGULAR'
sql;
        $ret = self::query($sql);
        if(empty($ret)){
            return [];
        }else{
            return $ret[0];
        }
    }
}