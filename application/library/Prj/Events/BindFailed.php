<?php
namespace Prj\Events;

/**
 * 失败绑卡事件处理类
 *
 * @author simon.wang
 */
class BindFailed extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        return 'done';
    }
}
