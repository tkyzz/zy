<?php

namespace Prj\Events\JavaEvt;

/**
 * 绑卡失败的事件通知
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class BindFailed extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Loger::setKv(get_called_class());
        $userId = $this->evtData->userId;
        $msg = 'done';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        return $msg;
    }

}

