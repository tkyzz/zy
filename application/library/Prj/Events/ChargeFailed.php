<?php
namespace Prj\Events;

/**
 * 失败充值事件处理类
 *
 * @author simon.wang
 */
class ChargeFailed extends \Sooh2\EvtQue\EvtProcess{
    public function onEvt()
    {
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        return 'done';
    }
}

