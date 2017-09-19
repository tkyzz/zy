<?php

namespace Prj\Events\JavaEvt;

/**
 * 赎回事件
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class RedeemOk extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        \Prj\Loger::setKv(get_called_class());
        $msg = 'done';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));

        return $msg;
    }
}

