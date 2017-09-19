<?php
namespace Prj\Events;

/**
 * 定期订单回款完毕
 *
 * @author simon.wang
 */
class ReturnTimeOk extends \Sooh2\EvtQue\EvtProcess{

    protected $ret;

    public function onEvt()
    {
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        $this->ret = 'done';
        \Prj\Loger::$prefix = '['. get_called_class() .']';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));

        $this->listener_jiaxi_red();

        return $this->ret;
    }

    /**
     * 监听加息红包的发放
     * @return array
     */
    protected function listener_jiaxi_red(){
        $res = \Prj\Bll\EventCoupon::getInstance()->sendJiaXiRedpakets($this->evtData->objId);
        $this->ret .= ',send_jiaxi_red:'. $res['message'];
        return $res;
    }

}
