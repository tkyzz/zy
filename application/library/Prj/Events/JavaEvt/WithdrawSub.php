<?php

namespace Prj\Events\JavaEvt;

/**
 * 提现申请
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class WithdrawSub extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Loger::setKv(get_called_class());
        $userId = $this->evtData->userId;
        $msg = 'done';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $ret = $this->send_sms($this->evtData->objId);
        return $msg.",sendWithdrawSub#".$ret;
    }


    public function send_sms($orderNo){
        $params = [
            'orderNo'   =>  $orderNo
        ];
        return \Prj\Bll\SendSmsForEvt::getInstance()->sendWithdrawApplyMsg($params);
    }

}

