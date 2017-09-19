<?php

namespace Prj\Events\JavaEvt;

/**
 * 成功绑卡事件处理类
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class Test extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Loger::setKv(__CLASS__);
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
    }

}

