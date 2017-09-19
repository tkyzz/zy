<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/12
 * Time: 17:41
 */

namespace Prj\Bll\Finals;

use Lib\Misc\Result;
use Prj\Bll\_BllBase;
use Prj\Bll\User;
use Prj\Loger;
use Prj\Model\BankOrder;
use Prj\Model\MimosaBankOrder;
use Prj\Model\MoneyInvestorBankOrder;
use Prj\Model\MoneyInvestorTradeOrder;
use Prj\Model\TradeOrder;

class UserFinal extends _BllBase
{

    /*
     * 增加绑卡*/
    public function addUserFinal($userId){
        \Prj\Loger::$prefix = '[addUserFinal]';
        if(empty($userId)){
            Loger::out("未获取到userId",LOG_ERR);
        }

        $bankOrderInfo = BankOrder::getOneRechargeByUserOid($userId);
        $userBankInfo = \Prj\Model\UserBank::getOneCardByUserOid($userId);
        $now = date('Ymd',time());
        $realVerifiedTimeStr = "IF(`realVerifiedTime`>'".$now."' OR `realVerifiedTime`=0,$now,`realVerifiedTime`)";
        $birth =  $data['birth'] = strlen($userBankInfo['idNumb'])==15 ? ('19' . substr($userBankInfo['idNumb'], 6, 6)) : substr($userBankInfo['idNumb'], 6, 8);

        $sql = "insert into".\Prj\Model\UserFinal::getTbname()."(uid,bindCardTime,realname,idCard,bindCardId,ymdBirthday,realVerifiedTime) 
            values('$userId',IF(`bindCardTime`>'".$now."' OR `bindCardTime`=0,$now,`bindCardTime`),'".$bankOrderInfo['realName']."','".$userBankInfo['idNumb']."','".$userBankInfo['cardNumb']."',".$birth.",".$realVerifiedTimeStr.")
            on DUPLICATE KEY update  `bindCardTime`= IF(`bindCardTime`>'".$now."' OR `bindCardTime`=0,$now,`bindCardTime`),realname='".$bankOrderInfo['realName']."'
            ,idCard='".$userBankInfo['idNumb']."',bindCardId='".$userBankInfo['cardNumb']."',realVerifiedTime=".$realVerifiedTimeStr."";
        $ret = \Prj\Model\UserBank::query($sql);
        if(!$ret){
            Loger::out("绑卡失败",LOG_ERR);
            return Result::get(RET_ERR,'绑卡失败');
        }

        return Result::get(RET_SUCC,"绑卡成功");


    }


    public function addChargeFinal($userId){
        \Prj\Loger::$prefix = '[addInvestorFinal]';
        if(empty($userId)) Loger::out('未获取到userId',LOG_ERR);
        $investBankOrder = MimosaBankOrder::getRecord("SUM(orderAmount) orderAmount,oid,createTime",array('investorOid'=>$userId,'orderType'=>'deposit','orderStatus'=>'done'),'sort createTime');
        if(empty($investBankOrder)){
            Loger::out('从money_investor_bankorder表中未获取到对应数据',LOG_ERR);
            return Result::get(RET_ERR,'未获取到对应充值数据');
        }
        $investBankOrder['oid'] = $investBankOrder['oid']?$investBankOrder['oid']:'';
        $investBankOrder['createTime'] = $investBankOrder['createTime']?date('Ymd',$investBankOrder['createTime']):'';
        $investBankOrder['orderAmount'] = $investBankOrder['orderAmount']?$investBankOrder['orderAmount']:'';
        Loger::out($investBankOrder);
        $sql = "insert into ".\Prj\Model\UserFinal::getTbname()."(uid,rechargeTime,rechargeId,rechargeTotalAmount) VALUES 
        ('$userId','".$investBankOrder['createTime']."','".$investBankOrder['oid']."',".$investBankOrder['orderAmount'].")
        ON DUPLICATE KEY update rechargeTime='".$investBankOrder['createTime']."',rechargeId='".$investBankOrder['oid']."',rechargeTotalAmount=".$investBankOrder['orderAmount'];
        $ret = \Prj\Model\UserBank::query($sql);
        if(!$ret){
            Loger::out("充值失败",LOG_ERR);
            return Result::get(RET_ERR,'充值失败');
        }

        return Result::get(RET_SUCC,"充值成功");

    }



    public function addBuyTimeOk($userId){
        \Prj\Loger::$prefix = '[addBuyTimeOk]';
        if(empty($userId)) Loger::out('未获取到userId',LOG_ERR);
        $investorTradeOrder = TradeOrder::getOneOrderByUserOid($userId);
//        $investorTradeOrder =  TradeOrder::getRecord('createTime,oid',array('investorOid'=>$userId,'orderStatus'=>'confirmed','orderType'=>'invest'),'sort createTime');
        if(empty($investorTradeOrder)){
            Loger::out('t_money_investor_tradeorder表中未获取到数据',LOG_ERR);
            return Result::get(RET_ERR,'未获取到对应购买数据');
        }
        $sql = "insert into ".\Prj\Model\UserFinal::getTbname()."(uid,orderTime,orderId) VALUES 
              ('$userId',".date('Ymd',$investorTradeOrder['createTime']).",'".$investorTradeOrder['oid']."') ON
              DUPLICATE KEY update orderTime=".date('Ymd',$investorTradeOrder['createTime']).",orderId='".$investorTradeOrder['oid']."'";
        $ret = \Prj\Model\UserBank::query($sql);
        if(!$ret){
            Loger::out("购买失败",LOG_ERR);
            return Result::get(RET_ERR,'购买失败');
        }
        return Result::get(RET_SUCC,"购买成功");

    }





    public function saveUserFinalBuyInfo($orderInfo = [])
    {
        $fistOrder = \Prj\Bll\ZY\TradeOrder::getInstance()->getFirstOrderInfo($orderInfo['userId']);
        if (empty($fistOrder)) return \Lib\Misc\Result::get(RET_ERR, '[异常]查无订单');
        $currentProduct = \Prj\Model\ZyBusiness\ProductInfo::getRecords("productId", ['productType' => "CURRENT"]);
        $currentProduct = array_column($currentProduct, "productId");

        if ($fistOrder['orderNo'] == $orderInfo['orderNo'] && !empty($fistOrder['orderId'])) {  //首次购买
            if (in_array($fistOrder['productId'], $currentProduct)) {
                $data['typeFirstBuy'] = "CURRENT";
            } else {
                $data['typeFirstBuy'] = in_array($fistOrder['productId'], $currentProduct) ? "CURRENT" : "REGULAR";
            }
            $data['amountFirstBuy'] = $fistOrder['orderAmount'] * 100;
            $data['orderCodeLastRecharge'] = $orderInfo['orderNo'];
            $ret = \Prj\Model\UserFinal::updateOne($data, ['uid' => $orderInfo['userId']]);
            return $ret;
        } else {
            $params = [
                'userId' => $orderInfo['userId'],
                'orderType' => "INVEST",
                'orderStatus' => "CONFIRMED"
            ];
            $list = \Prj\Model\ZyBusiness\TradOrder::getRecords("*", $params, 'sort confirmedTime', 3);
            $lastList = \Prj\Model\ZyBusiness\TradOrder::getRecord("*", $params, 'rsort confirmedTime');
            $maxInfo = \Prj\Model\ZyBusiness\TradOrder::getRecord("*", $params, 'rsort orderAmount');
            $where['orderCodeLastRecharge'] = $orderInfo['orderNo'];
            if (!empty($maxInfo)) {
                $where['ymdMaxBuy'] = date("Ymd", strtotime($maxInfo['confirmedTime']));
                $where['typeMaxBuy'] = in_array($maxInfo['productId'], $currentProduct) ? "CURRENT" : "REGULAR";
                $where['amountMaxBuy'] = $maxInfo['orderAmount'] * 100;
                $where['orderCodeMaxBuy'] = $maxInfo['orderNo'];
            }
            if (!empty($lastList)) {
                $where['ymdLastBuy'] = date("Ymd", strtotime($lastList['confirmedTime']));
                $where['typeLastBuy'] = in_array($lastList['productId'], $currentProduct) ? "CURRENT" : "REGULAR";
                $where['amountLastBuy'] = $lastList['orderAmount'] * 100;
            }
            if (!empty($list)) {

                if (!empty($list[0])) {
                    $where['amountFirstBuy'] = $list[0]['orderAmount'] * 100;
                    $where['typeFirstBuy'] = in_array($list[0]['productId'], $currentProduct) ? "CURRENT" : "REGULAR";
                    $where['orderCodeFirstBuy'] = $list[0]['orderNo'];

                }
                if (!empty($list[1])) {
                    $where['amountSecBuy'] = $list[1]['orderAmount'] * 100;
                    $where['typeSecBuy'] = in_array($list[1]['productId'], $currentProduct) ? "CURRENT" : "REGULAR";
                    $where['orderCodeSecBuy'] = $list[1]['orderNo'];
                }
                if (!empty($list[2])) {
                    $where['ymdThirdBuy'] = date("Ymd", strtotime($list[2]['confirmedTime']));
                    $where['typeThirdBuy'] = in_array($list[2]['productId'], $currentProduct) ? "CURRENT" : "REGULAR";
                    $where['amountThirdBuy'] = $list[2]['orderAmount'] * 100;
                    $where['orderCodeThirBuy'] = $list[2]['orderNo'];
                }
                $where['orderCodeLastBuy'] = $orderInfo['orderNo'];
            }

            if (!empty($where)) {
                return \Prj\Model\UserFinal::updateOne($where, ['uid' => $orderInfo['userId']]);
            } else {
                return "";
            }

        }

    }
}
