<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 20:30
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * mimosa交易订单表
 * Class MimosaProduct
 * @package Prj\Model
 */
class MimosaTradeOrder extends _ModelBase
{
    public static $orderStatus_success = ['paySuccess','accepted','confirmed','done']; //交易成功的订单

    protected function onInit(){
        $this->className = 'MimosaProduct';
        parent::onInit();
        $this->_tbName = 't_money_investor_tradeorder';
    }

    public static function getOneByProId($productId){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['oid' => $productId];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    /**
     * 获取标的的最新一笔成功的订单
     * @param $productId
     * @return mixed
     */
    public static function getLastSucOrderByProId($productId){
        $db = static::getCopy('')->dbWithTablename();
        $where = [
            'productOid' => $productId,
            'orderType' => 'invest',
            'orderStatus' => static::$orderStatus_success
        ];
        return $db->getRecord($db->kvobjTable() , '*' , $where , 'rsort createTime');
    }

    /**
     * Hand
     * 查找一条投资成功的订单
     * @param $userId
     * @return mixed
     */
    public static function getOneOrderByUserOid($userId){
        $db = static::getCopy('')->dbWithTablename();
        $where = [
            'investorOid' => $userId,
            'orderStatus' => static::$orderStatus_success,
            'orderType' => 'invest',
        ];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    public static function setActivityTypes($orderId , $type){
        $order = self::getCopy($orderId);
        $order->load();
        if(!$order->exists()){
            \Prj\Loger::out('orderId: '.$orderId.' does not exists !' , LOG_WARNING);
            return false;
        }
        try{
            $types = $order->getField('activityTypes');
        }catch (\Exception $e){
            $types = '';
        }
        $typeArr = empty($types) ? [] : explode(',' , $types);
        if(!in_array($type , $typeArr)){
            $typeArr[] = $type;
        }else{
            return true;
        }
        $ret = self::updateOne(['activityTypes' => implode(',',$typeArr)],[
            'oid' => $orderId,
        ]);
        \Prj\Loger::out(self::db()->lastCmd());
        return $ret;
    }
}