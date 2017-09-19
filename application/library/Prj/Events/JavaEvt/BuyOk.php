<?php

namespace Prj\Events\JavaEvt;

/**
 * 成功绑卡事件处理类
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class BuyOk extends \Sooh2\EvtQue\EvtProcess
{

    public function onEvt()
    {
        $msgMap = [];
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        \Prj\Loger::setKv(get_called_class());
        $msg = 'done';
        error_log("trace event on " . __CLASS__ . ' with data=' . json_encode($this->evtData));
        $orderNo = $this->evtData->objId;
        $orderInfo = \Prj\Model\ZyBusiness\TradOrder::getOne(['orderNo' => $orderNo]);
        if (empty($orderInfo)) return $orderNo . ' 订单信息不存在!';
        $proInfo = \Prj\Model\ZyBusiness\ProductInfo::getOne(['productId' => $orderInfo['productId']]);
        if (empty($proInfo)) return $proInfo . ' 产品信息不存在!';
        $baoBaoTree = $this->send_bbt_order();
        $msg .= "send_bbt_order#" . $baoBaoTree;
        if ($proInfo['productType'] == 'REGULAR') {
            //定期
            \Prj\Bll\UserFinal::getInstance()->evtOrderHolding($this->evtData->userId);

            //发放返利
            \Prj\Bll\Rebate::getInstance()->runRebateOnBuy($orderInfo, $proInfo);

            $msgMap['send_buy_coupon'] = $this->send_buy_coupon($orderInfo);

            \Prj\Bll\UserFinal::getInstance()->evtBuyTime($this->evtData->userId); //这条更新用户数据放在最后执行

        } else if ($proInfo['productType'] == 'CURRENT') {
            //活期
            $msgMap['活期'] = '';
            \Prj\Bll\UserFinal::getInstance()->evtBuyCurrent($this->evtData->userId); //这条更新用户数据放在最后执行
        } else {
            $msgMap['未知的产品类型'] = '';
        }
        //发送通知
        $ret = $this->sendSms($orderNo);

        $res = \Prj\Bll\Finals\UserFinal::getInstance()->saveUserFinalBuyInfo($orderInfo);
        $sendDataWhenBuyRes = $this->sendDataWhenBuy($orderInfo);
        $msgMap['sendDataWhenBuy'] = $sendDataWhenBuyRes['message'];
        $msgMap['sendSms'] = $ret;
        $msgMap['saveUserFinalBuyInfo'] = $res;

        foreach ($msgMap as $k => $v) {
            $tmp[] = $k . '#' . $v;
        }
        return implode(',', $tmp);
    }

    protected function send_buy_coupon($orderInfo = [])
    {
        $res = $this->isFirstBuy($orderInfo);
        if (!\Lib\Misc\Result::check($res)) return $res['message'];
        $isFirst = $res['data']['isFirstBuy'];
        if ($isFirst) {
            //首次购买发放奖励
            $params['actCode'] = \Prj\Model\Activity::buy_code;
            $params['userId'] = $orderInfo['userId'];
            $res = \Prj\Bll\EventCoupon::getInstance()->sendCoupon($params);
            return $res['message'];

        } else {
            return '非首次购买';
        }
    }

    protected function isFirstBuy($orderInfo = [])
    {
        $fistOrder = \Prj\Bll\ZY\TradeOrder::getInstance()->getFirstDingOrderInfo($orderInfo['userId']);
        if (empty($fistOrder)) return \Lib\Misc\Result::get(RET_ERR, '[异常]查无订单');
        if ($fistOrder['orderNo'] == $orderInfo['orderNo'] && !empty($fistOrder['orderId'])) {
            return \Lib\Misc\Result::get(RET_SUCC, '', [
                'isFirstBuy' => 1
            ]);
        } else {
            \Prj\Loger::outVal('首次订单', $fistOrder['orderNo']);
            return \Lib\Misc\Result::get(RET_SUCC, '', [
                'isFirstBuy' => 0
            ]);
        }
    }


    public function send_bbt_order()
    {
        $orderId = $this->evtData->objId;
        return \Prj\Bll\BaobaoTreeZy::getInstance()->sendOrder($orderId);
    }




    public function test($orderNo)
    {
        $orderInfo = \Prj\Model\ZyBusiness\TradOrder::getOne(['orderNo' => $orderNo]);
        return $this->send_buy_coupon($orderInfo);
    }

    public function sendSms($orderNo)
    {
        $params = [
            'orderNo' => $orderNo
        ];
        return \Prj\Bll\SendSmsForEvt::getInstance()->SendInvestOkMsg($params);
    }

    public function sendDataWhenBuy($orderInfo)
    {
        $params = \Prj\Model\UserFinal::getRecord("realname,phone,certNo as idCard",['uid'=>$orderInfo['userId']]);
        $product= \Prj\Model\ZyBusiness\ProductInfo::getRecord("productNo,productType", ['productId' => $orderInfo['productId']]);
        $params['orderNo'] = $orderInfo['orderNo'];
        $params['orderTime'] = $orderInfo['createTime'];
        $params['userId'] = $orderInfo['userId'];
        $params['productNo'] = $product['productNo'];
        $params['productType'] = $product['productType'];
        $ret = \Lib\Services\Signature::getInstance()->sendDataWhenBuy($params);
        return $ret;
    }
}

