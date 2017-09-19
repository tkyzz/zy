<?php
namespace Prj\Events\JavaEvt;

/**
 * 失败充值事件处理类
 *
 * @author simon.wang
 */
class ChargeFailed extends \Sooh2\EvtQue\EvtProcess{
    public function onEvt()
    {
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $this->sendErrorMsg();
        $this->sendFailedMsg($this->evtData->objId);
        return 'done,sendFailMsg';
    }


    public function sendErrorMsg(){
        $userId = $this->evtData->userId;
        $orderId = $this->evtData->objId;
        $orderMsg = \Prj\Bll\Newbie::getInstance()->checkRechargeResult($orderId);
        $bankInfo = \Prj\Bll\Newbie::getInstance()->getBankInfo($userId);
        $bankCode = substr($bankInfo['bankCardNo'],-4);
        $data = [
            'bankCardSuffix'    =>  $bankCode,
            'orderTime'         =>  date('Y-m-d H:i:s'),
            'opeateType'        =>  "充值",
            'reason'            =>  $orderMsg
        ];
        \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg("rwFailed",$userId,$data);
    }


    public function sendFailedMsg($orderNo){
        $params = [
            'orderNo'   =>  $orderNo
        ];
        \Prj\Bll\SendSmsForEvt::getInstance()->sendRechargeFailedMsg($params);
    }
}

