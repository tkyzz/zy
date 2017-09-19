<?php

namespace Prj\Events\JavaEvt;

/**
 * 成立
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
USE Prj\Loger;

class SetPayPwd extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        \Prj\Loger::setKv(get_called_class());
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $res = \Prj\Bll\UserFinal::getInstance()->evtSetPayPwd($this->evtData->userId); //同步支付密码
        return 'done';
    }

}

