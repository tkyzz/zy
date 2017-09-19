<?php

namespace Prj\Events\JavaEvt;

/**
 * 提现到账成功
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class WithdrawOk extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Loger::setKv(get_called_class());
        $userId = $this->evtData->userId;
        $msg = 'done';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $ret = $this->sendWithDrawOkSms($this->evtData->objId);
        return $msg.",sendWithDrawOkSms#".$ret;
    }


    public function sendWithDrawOkSms($orderNo){
        $params =[
            'orderNo'   =>  $orderNo
        ];
        return \Prj\Bll\SendSmsForEvt::getInstance()->sendWithdrawArriveMsg($params);
    }

}

