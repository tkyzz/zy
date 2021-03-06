<?php

namespace Prj\Events\JavaEvt;

/**
 * 按月付息-定期订单派息结束
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class OrderRepayOk extends \Sooh2\EvtQue\EvtProcess{

    public function init($evtData)
    {
        \Prj\Loger::setKv(get_called_class());
        return parent::init($evtData); // TODO: Change the autogenerated stub
    }

    public function onEvt()
    {
        $msg = 'done';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        return $msg;
    }

}

