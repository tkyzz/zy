<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-15 14:21
 */

namespace Prj\Events\JavaEvt;

/**
 * 订单持有事件
 * @package Prj\Events\JavaEvt
 * @author lingtima@gmail.com
 */
class OrderHolding extends \Sooh2\EvtQue\EvtProcess
{
    public function onEvt()
    {
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        \Prj\Loger::setKv(get_called_class());
        $msg = 'done';
        error_log("trace event on " . __CLASS__ . ' with data=' . json_encode($this->evtData));
        $orderNo = $this->evtData->objId;
        $orderInfo = \Prj\Model\ZyBusiness\TradOrder::getOne(['orderNo' => $orderNo]);
        if (empty($orderInfo)) {
            return $orderNo . ' 订单信息不存在!';
        }
        $proInfo = \Prj\Model\ZyBusiness\ProductInfo::getOne(['productId' => $orderInfo['productId']]);
        if (empty($proInfo)) {
            return $orderInfo['productId'] . ' 产品信息不存在!';
        }
        if ($proInfo['productType'] == 'REGULAR') {
            //开始发放返利
            $res = \Prj\Bll\Rebate::getInstance()->giveRedpacket($orderNo);
            $msg .= ',giveRedpacket#' . $res['message'];
        } else if ($proInfo['productType'] == 'CURRENT') {
            //活期
        } else {
            $msg = '未知的产品类型';
        }
        $ret = $this->sendHoldingMsg($orderNo);
        return $msg.",sendHoldingMsg#".$ret;
    }


    public function sendHoldingMsg($orderNo){
        $params = [
            'orderNo'   =>  $orderNo
        ];
        $ret = \Prj\Bll\SendSmsForEvt::getInstance()->sendHoldingMsg($params);
        return $ret;
    }
}