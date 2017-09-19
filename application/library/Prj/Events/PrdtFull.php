<?php

namespace Prj\Events;

/**
 * 满标事件处理类
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
USE Prj\Loger;

class PrdtFull extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        Loger::$prefix = '['. get_called_class() .']';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $this->listener_activity_onchui($this->evtData->objId);
        return 'done,listener_activity_onchui';
    }

    /**
     * 一锤定音活动监听
     * @param $productId
     */
    protected function listener_activity_onchui($productId){
        try{
            $params['productId'] = $productId;
            $params['actCode'] = \Prj\Model\Activity::onechui_code;
            \Prj\Bll\EventCoupon::getInstance()->sendCoupon($params);
        }catch (\Exception $e){
            \Prj\Loger::out($e->getMessage() , LOG_ERR);
        }
    }

    public function test_listener_activity_onchui($productId){
        return $this->listener_activity_onchui($productId);
    }
}

