<?php
namespace Prj\Bll;
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/7
 * Time: 9:52
 */
class SendSmsForEvt extends \Prj\Bll\_BllBase
{
    /*投资成功*/
    public function SendInvestOkMsg($params)
    {
        if (!\Lib\Misc\Result::paramsCheck($params, ['orderNo'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "investmentSuccess";
        if (is_array($params['orderNo'])) {
            try {
                foreach ($params['orderNo'] as $k => $v) {
                    $productOrder = $this->getTraderOrderInfoByOrderId($v);
                    $productName = \Prj\Model\ZyBusiness\ProductInfo::getRecord("productName", ['productId' => $productOrder['productId']])['productName'];

                    $replace = ['{product_name}' => $productName];
                    \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $productOrder['userId'], $replace); //  发送短信
                }
            }catch (Exception $ex){
                return $this->resultError("发送短信失败,".$ex->getMessage());
            }
        } else {
            $productOrder = $this->getTraderOrderInfoByOrderId($params['orderNo']);
            if(empty($productOrder)) return $this->resultError('没有此订单信息');
            $productName = \Prj\Model\ZyBusiness\ProductInfo::getRecord("productName", ['productId' => $productOrder['productId']])['productName'];
            if(empty($productName)) return $this->resultError('没有此产品');
            $replace = ['{product_name}' => $productName];
            return \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $productOrder['userId'], $replace);
        }

    }






    /*充值成功*/
    public function sendRechargeOkMsg($params)
    {
        if (!\Lib\Misc\Result::paramsCheck($params, ['orderNo'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "rechargeSuccess";
        if (is_array($params['orderNo'])) {
            try {

                foreach ($params['orderNo'] as $k => $v) {
                    $order = $this->getBandOrderInfoByOrderId($v,'RECHARGE');

                    if (empty($order)) return $this->resultError("没有此订单信息");
                    $data = [
                        '{num1}' => floatval($order['orderAmount'])
                    ];
                    \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);   //发送短信

                }
            }catch(Exception $ex){
                return $this->resultError("发送充值成功短信失败，#".$ex->getMessage());
            }
        } else {
            $order = $this->getBandOrderInfoByOrderId($params['orderNo'],'RECHARGE');
            if(empty($order)) return $this->resultError("没有此订单信息");
            $data = ['{num1}' => floatval($order['orderAmount'])];
            \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);   //发送短信

        }
    }

    /*充值失败*/
    public function sendRechargeFailedMsg($params)
    {
        if (!\Lib\Misc\Result::paramsCheck($params, ['orderNo'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "rwFailed";
        if (is_array($params['orderNo'])) {
            try {


                foreach ($params['orderNo'] as $k => $v) {
                    $order = $this->getBandOrderInfoByOrderId($v, 'RECHARGE', 'FAILED');
                    if (empty($order)) return $this->resultError("没有此订单信息");
                    $bankInfo = \Prj\Bll\Newbie::getInstance()->getBankInfo($order['userId']);
                    $bankCode = substr($bankInfo['bankCardNo'], -4);
                    $data = [
                        '{bankCardSuffix}' => $bankCode,
                        '{orderTime}' => $order['updateTime'],
                        '{opeateType}' => "充值",
                        '{reason}' => $order['returnMsg']
                    ];
                    \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);   //发送短信

                }
            }catch (Exception $ex){
                return $this->resultError("发送充值失败短信失败！#".$ex->getMessage());
            }
        } else {
            $order = $this->getBandOrderInfoByOrderId($params['orderNo'], 'RECHARGE', 'FAILED');
            if (empty($order)) return $this->resultError("没有此订单信息");
            $bankInfo = \Prj\Bll\Newbie::getInstance()->getBankInfo($order['userId']);
            $bankCode = substr($bankInfo['bankCardNo'], -4);
            $data = [
                '{bankCardSuffix}' => $bankCode,
                '{orderTime}' => $order['updateTime'],
                '{opeateType}' => "充值",
                '{reason}' => $order['returnMsg']
            ];
            \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);

        }
    }

    /*计息*/
    public function sendHoldingMsg($params){
        if (!\Lib\Misc\Result::paramsCheck($params, ['orderNo'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "Holding";
        if(is_array($params['orderNo'])){
            foreach($params['orderNo'] as $k => $v){
                $tradeOrder= $this->getTraderOrderInfoByOrderId($v);
                if (empty($tradeOrder)) return $this->resultError("没有此订单信息");
                $productName = \Prj\Model\ZyBusiness\ProductInfo::getRecord("productName", ['productId' => $tradeOrder['productId']])['productName'];
                $replace = ['{product_name}' => $productName];
                \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $tradeOrder['userId'], $replace); //  发送短信
            }
        }else{
            $tradeOrder= $this->getTraderOrderInfoByOrderId($params['orderNo']);
            if (empty($tradeOrder)) return $this->resultError("没有此订单信息");
            $productName = \Prj\Model\ZyBusiness\ProductInfo::getRecord("productName", ['productId' => $tradeOrder['productId']])['productName'];
            $replace = ['{product_name}' => $productName];
            \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $tradeOrder['userId'], $replace); //  发送短信
        }
    }


    /*提现失败*/
    public function sendWithdrawFailedMsg($params)
    {
        if (!\Lib\Misc\Result::paramsCheck($params, ['orderNo'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "rwFailed";
        if (is_array($params['orderNo'])) {
            try {

                foreach ($params['orderNo'] as $k => $v) {
                    $order = $this->getBandOrderInfoByOrderId($v, 'WITHDRAW', 'FAILED');
                    if (empty($order)) return $this->resultError("没有此订单信息");
                    $bankInfo = \Prj\Bll\Newbie::getInstance()->getBankInfo($order['userId']);
                    $bankCode = substr($bankInfo['bankCardNo'], -4);
                    $data = [
                        '{bankCardSuffix}' => $bankCode,
                        '{orderTime}' => $order['updateTime'],
                        '{opeateType}' => "提现",
                        '{reason}' => $order['returnMsg']
                    ];
                    \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);   //发送短信

                }
            }catch(Exception $ex){
                return $this->resultError("发送提现失败短信失败！#".$ex->getMessage());
            }

        } else {
            $order = $this->getBandOrderInfoByOrderId($params['orderNo'], 'WITHDRAW', 'FAILED');
            $bankInfo = \Prj\Bll\Newbie::getInstance()->getBankInfo($order['userId']);
            $bankCode = substr($bankInfo['bankCardNo'], -4);
            $data = [
                '{bankCardSuffix}' => $bankCode,
                '{orderTime}' => $order['updateTime'],
                '{opeateType}' => "提现",
                '{reason}' => $order['returnMsg']
            ];
            \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);

        }
    }

    /*提现申请*/
    public function sendWithdrawApplyMsg($params)
    {
        if (!\Lib\Misc\Result::paramsCheck($params, ['orderNo'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "withdrawApply";

        if (is_array($params['orderNo'])) {
            try {
                foreach ($params['orderNo'] as $k => $v) {
                    $order = $this->getBandOrderInfoByOrderId($v, 'WITHDRAW', ['INIT', 'PROCESSING']);
                    if (empty($order)) return $this->resultError("没有此订单信息");
                    $data = [
                        '{time}' => $order['updateTime'],
                        '{num1}' => floatval($order['orderAmount'])
                    ];
                    \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);

                }
            }catch(Exception $ex){
                return $this->resultError("发送提现申请短信失败！".$ex->getMessage());
            }

        } else {
            $order = $this->getBandOrderInfoByOrderId($params['orderNo'], 'WITHDRAW', ['INIT', 'PROCESSING']);
            if (empty($order)) return $this->resultError("没有此订单信息");
            $data = [
                '{time}' => $order['updateTime'],
                '{num1}' => floatval($order['orderAmount'])
            ];
            \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);
        }

    }

    /*注册成功*/
    public function sendRegisterMsg($params)
    {
        if (!\Lib\Misc\Result::paramsCheck($params, ['userId'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "registerSuccess";
        $newbieStepbonus = \Prj\Bll\ActivityConfig::getInstance()->getActiveScheme('新手引导');
        $newbieList = \Rpt\Manage\ManageActivitySchemeConfig::getListByBASE64(bin2hex(json_encode($newbieStepbonus['id'])));
        $register_bonus = 0;
        foreach ($newbieList as $k => $v) {
            if ($v['flag'] == 'signin_register_bonus') $register_bonus = !empty($v['value']) ? $v['value'] : 0;
        }
        $data['{num1}'] = floatval($register_bonus);
        \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $params['userId'], $data);

    }


    /*提现到账通知*/
    public function sendWithdrawArriveMsg($params)
    {
        if (!\Lib\Misc\Result::paramsCheck($params, ['orderNo'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "withdrawArrive";

        if (is_array($params['orderNo'])) {
            try {

                foreach ($params['orderNo'] as $k => $v) {
                    $order = $this->getBandOrderInfoByOrderId($v, 'WITHDRAW', 'SUCCESS');
                    if (empty($order)) return $this->resultError("没有此订单信息");
                    $data = [
                        '{time}' => $order['updateTime'],
                        '{num1}' => floatval($order['orderAmount'])
                    ];
                    \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);

                }
            }catch(Exception $ex){
                return $this->resultError("发送提现到账通知失败！#".$ex->getMessage());
            }

        } else {
            $order = $this->getBandOrderInfoByOrderId($params['orderNo'], 'WITHDRAW', 'SUCCESS');
            if (empty($order)) return $this->resultError("没有此订单信息");
            $data = [
                '{time}' => $order['updateTime'],
                '{num1}' => floatval($order['orderAmount'])
            ];
            \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);
        }
    }



    public function sendReturnTimeOkMsg($params){
        if (!\Lib\Misc\Result::paramsCheck($params, ['orderNo'])) return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        $msgCode = "backMoney";
        if(is_array($params['orderNo'])){
            try{
                foreach ($params['orderNo'] as $k => $v) {
                    $order = $this->getTraderOrderInfoByOrderId($v, 'CASH', 'CONFIRMED');
                    if (empty($order)) return $this->resultError("没有此订单信息");
                    $productName = \Prj\Model\ZyBusiness\ProductInfo::getRecord("productName", ['productId' => $order['productId']])['productName'];
                    $data = [
                        '{product_name}' => $productName,
                        '{num1}' => floatval($order['orderAmount'])
                    ];
                    \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);
                    return 'ok';
                }
            }catch (\Exception $ex){
                return $this->resultError("发送回款通知失败！#".$ex->getMessage());
            }
        }else{
            $order = $this->getTraderOrderInfoByOrderId($params['orderNo'], 'CASH', 'SUCCESS');
            if (empty($order)) return $this->resultError("没有此订单信息");
            $productName = \Prj\Model\ZyBusiness\ProductInfo::getRecord("productName", ['productId' => $order['productId']])['productName'];
            $data = [
                '{product_name}' => $productName,
                '{num1}' => floatval($order['orderAmount'])
            ];
            \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($msgCode, $order['userId'], $data);
            return 'ok';
        }
    }




    public function getBandOrderInfoByOrderId($orderNo, $orderType = 'RECHARGE', $orderStatus = "SUCCESS")
    {
        $params = [
            'orderNo' => $orderNo,
            'orderType' => $orderType,
            'orderStatus' => $orderStatus
        ];
        $ret = \Prj\Model\Payment\BankOrder::getRecord("*", $params);
        return $ret;
    }


    public function getTraderOrderInfoByOrderId($orderNo, $orderType = 'INVEST', $orderStatus = "CONFIRMED")
    {
        $params = [
            'orderNo' => $orderNo,
            'orderType' => $orderType,
            'orderStatus' => $orderStatus
        ];
        $ret = \Prj\Model\ZyBusiness\TradOrder::getRecord("*", $params);
        return $ret;
    }




}