<?php
namespace Prj\Events\JavaEvt;

/**
 * 成功充值事件处理类
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
USE Prj\Loger;

class ChargeOk extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        $userId = $this->evtData->userId;
        Loger::setKv(get_called_class());
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $sendCouponMsg = $this->send_charge_coupon();
        $res = \Prj\Bll\UserFinal::getInstance()->evtRecharge($userId);
        $chargeOkMsg = $this->send_charge_ok_sms($this->evtData->objId);
        $chargeMsg = $this->saveUserFinalChargeInfo($this->evtData->userId,$this->evtData->objId);
        return 'done,send_charge_coupon#' . $sendCouponMsg."#chargeOkMsg".$chargeOkMsg.",saveUserFinalChargeInfo#".$chargeMsg;
    }

    /**
     * Hand 发放充值红包
     * @param string $userId
     * @param string $chargeId
     * @return mixed|string
     */
    protected function send_charge_coupon($userId = '' , $chargeId = ''){
        $chargeId = $chargeId ? $chargeId : $this->evtData->objId;
        $userId = $userId ? $userId : $this->evtData->userId;
        if(empty($chargeId)){
            Loger::out('充值订单ID为空!' , LOG_ERR);
            return '充值订单ID为空!';
        }
        $isFirstRet = $this->isFirstCharge($userId , $chargeId);
        if(!Result::check($isFirstRet))return $isFirstRet['message'];
        $isFirst = $isFirstRet['data']['isFirst'];
        if($isFirst){
            $params = [
                'userId' => $userId,
                'actCode' => \Prj\Model\Activity::charge_code,
            ];
            $sendRet = \Prj\Bll\EventCoupon::getInstance()->sendCoupon($params);
            return $sendRet['message'];
        }else{
            Loger::out('不是首次充值');
            Loger::out('首单充值的ID是: '.$isFirstRet['data']['orderNo']);
            return '不是首次充值';
        }
    }

    /**
     * Hand 检查是否首次充值
     * @param $userId
     * @param $chargeId
     * @return array
     */
    protected function isFirstCharge($userId , $chargeId){
        $firstCharge = \Prj\Model\Payment\BankOrder::getFirstChargeInfo($userId);

        if(empty($firstCharge))return \Lib\Misc\Result::get(RET_ERR , '异常#查不到充值记录');
        if($firstCharge['orderNo'] == $chargeId){
            return \Lib\Misc\Result::get(RET_SUCC , '' , [
                'isFirst' => 1,
                'orderNo' => $firstCharge['orderNo']
            ]);
        }else{
            return \Lib\Misc\Result::get(RET_SUCC , '' , [
                'isFirst' => 0,
                'orderNo' => $firstCharge['orderNo']
            ]);
        }
    }

    public function test($userId , $chargeId){
        return $this->send_charge_coupon($userId , $chargeId);
    }


    public function send_charge_ok_sms($orderNo){
        $params = [
            'orderNo'   =>  $orderNo
        ];
        return \Prj\Bll\SendSmsForEvt::getInstance()->sendRechargeOkMsg($params);
    }



    public function saveUserFinalChargeInfo($userId,$chargeId){
        $firstCharge = \Prj\Model\Payment\BankOrder::getFirstChargeInfo($userId);

        if(empty($firstCharge))return \Lib\Misc\Result::get(RET_ERR , '异常#查不到充值记录');
        if($firstCharge['orderNo'] == $chargeId){
            $data['amountLastRecharge'] = $firstCharge['orderAmount']*100;
            $data['orderCodeFirstRecharge'] = $firstCharge['orderNo'];
            $data['orderCodeLastRecharge'] = $chargeId;
            $data['orderCodeMaxRecharge'] = $chargeId;
            $data['orderCodeLastRecharge'] = $chargeId;
            $ret = \Prj\Model\UserFinal::updateOne($data,['uid'=>$userId]);
            return $ret;
        }else{
            $where['orderCodeLastRecharge'] = $chargeId;
            $secondList = \Prj\Model\Payment\BankOrder::getRecords("*",['orderType'=>"RECHARGE",'userId'=>$userId,'orderStatus'=>"SUCCESS"],'sort createTime',2);
            if(!empty($secondList[0])){
                $where['amountFirstRecharge'] = $firstCharge['orderAmount']*100;

            }
            if(!empty($secondList[1])){
                $where['ymdSecRecharge'] = date("Ymd",strtotime($secondList[1]['createTime']));
                $where['amountSecRecharge'] = $secondList[1]['orderAmount']*100;
                $where['orderCodeSecRecharge'] = $secondList[1]['orderNo'];
            }
            $lastList = \Prj\Model\Payment\BankOrder::getRecord("*",['orderType'=>"RECHARGE",'userId'=>$userId,'orderStatus'=>"SUCCESS"],'rsort createTime');
            if(!empty($lastList)){
                $where['ymdLastRecharge'] = date("Ymd",strtotime($lastList['createTime']));
                $where['amountLastRecharge'] = $lastList['orderAmount']*100;
                $where['orderCodeLastRecharge'] = $lastList['orderNo'];
            }

            $maxList = \Prj\Model\Payment\BankOrder::getRecord("*",['orderType'=>"RECHARGE",'userId'=>$userId,'orderStatus'=>"SUCCESS"],'rsort orderAmount');
            if(!empty($maxList)){
                $where['ymdMaxRecharge'] = date("Ymd",strtotime($maxList['completeTime']));
                $where['amountMaxRecharge'] = $maxList['orderAmount']*100;
                $where['orderCodeMaxRecharge'] = $maxList['orderNo'];
            }
            Loger::outVal("sqls",$where);
            $ret = \Prj\Model\UserFinal::updateOne($where,['uid'=>$userId]);
            return $ret;
        }
    }

}
