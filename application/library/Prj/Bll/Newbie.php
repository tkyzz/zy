<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/1
 * Time: 16:41
 */
namespace Prj\Bll;

class Newbie extends _BllBase
{
    protected $userId;

    public function __construct($userId = '')
    {
        if (!empty($userId)) {
            $this->userId = $userId;
        }
    }


    /**
     * Hand 是否绑卡
     * @return int|null
     */
    public function checkBind()
    {
        if (empty($this->userId)) return 0;
        $card = \Prj\Model\Payment\BankBind::hasBind($this->userId);
        if ($card) {
            //绑卡
            return 1;
        } else {
            $unbindNum = \Prj\Model\Payment\BankBind::unbindCount($this->userId);
            if($unbindNum){
                //解绑未绑卡
                return 2;
            }else{
                //从未绑卡
                return 0;
            }
        }
    }



    /**
     * Hand
     * 是否充值
     * @return bool|null
     */
    public function checkRecharge()
    {
        if (empty($this->userId)) return null;
        $recharge = \Prj\Model\Payment\BankOrder::getRecord("*",['orderType'=>'RECHARGE','userId'=>$this->userId,'orderStatus'=>'SUCCESS'],'rsort updateTime');
        return empty($recharge) ? false : true;
    }


    public function getBankInfo($userId){
        $bankInfo = \Prj\Model\Payment\AccountBankBind::getRecord("*",['userId'=>$userId,"status"=>"BIND"]);
        return $bankInfo;
    }


    public function checkRechargeResult($orderId,$orderType="RECHARGE"){
        $where = [
            'orderType'     =>  $orderType,
            'orderId'       =>  $orderId
        ];
        $returnMsg = \Prj\Model\Payment\BankOrder::getRecord("*",$where);
        return $returnMsg;
    }


//    /**
//     * Hand
//     * 是否购买过
//     * @return bool|null
//     */
//    public function checkBuy()
//    {
//        if (empty($this->userId)) return null;
//        $status = \Prj\Model\Payment\TradeOrder::getRecord("status",['orderType'=>'INVEST'])['status'];
//        return ($status == "CONFIRMED")?true:false;
//    }

    public function checkBuy(){
        if (empty($this->userId)) return null;
        $lastOrder = \Prj\Model\ZyBusiness\TradOrder::getLastDingOrder($this->userId);
        return empty($lastOrder) ? false : true;
    }

}