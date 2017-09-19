<?php
namespace Prj\Events;

/**
 * 成功购买活期产品事件处理类
 *
 * @author simon.wang
 */
class BuyCurOk extends \Sooh2\EvtQue\EvtProcess{
    public function onEvt()
    {
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        return 'done';
    }
}