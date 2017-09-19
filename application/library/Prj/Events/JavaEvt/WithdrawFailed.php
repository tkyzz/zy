<?php

namespace Prj\Events\JavaEvt;

/**
 * 提现到账失败
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class WithdrawFailed extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Loger::setKv(get_called_class());
        $userId = $this->evtData->userId;
        $msg = 'done';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $ret = $this->sendWithDrawFailedSms($this->evtData->objId);
        return $msg.",sendWithDrawFailedSms#".$ret;
    }



    public function sendWithDrawFailedSms($orderNo){
        $params = [
            'orderNo'   =>  $orderNo
        ];
        return \Prj\Bll\SendSmsForEvt::getInstance()->sendWithdrawFailedMsg($params);
    }

}

