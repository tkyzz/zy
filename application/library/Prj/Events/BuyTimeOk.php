<?php
namespace Prj\Events;

/**
 * 成功购买定期产品事件处理类
 *
 * @author simon.wang
 */
class BuyTimeOk extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        $msg = 'done';
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        \Prj\Loger::setKv(get_called_class());
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $ret = $this->send_bbt_order();
        $msg .= ',send_bbt_order#' . $ret['message'];
        //$ret2 = $this->send_old_driver();
        //$ret3 = $this->bind_user_final();
        $ret4 = $this->chocolate_add_times();
        $msg .= ',chocolate_add_times#' . $ret4['message'];
        return $msg;

    }

    public function send_bbt_order(){
        $orderId = $this->evtData->objId;
        return \Prj\Bll\BaobaoTree::getInstance()->sendOrder($orderId);
    }

    //老司机活动任务处理
    public function send_old_driver(){
        $orderId = $this->evtData->objId;
        return \Prj\Bll\OldDriver::getInstance()->sendOrder($orderId);
    }


    public function bind_user_final(){
        $userId = $this->evtData->userId;
        return \Prj\Bll\Finals\UserFinal::getInstance()->addBuyTimeOk($userId);
    }

    public function chocolate_add_times(){
        $orderId = $this->evtData->objId;
        return \Prj\Bll\Tmp\Chocolate0828::getInstance()->checkAddTimes([
            'orderId' => $orderId
        ]);
    }

}
