<?php

namespace Prj\Events;

/**
 * 标的募集结束的事件
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
USE Prj\Loger;

class PrdtRaiseEnd extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        Loger::addPrefix(get_called_class());
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $this->listener_activity_yiming($this->evtData->objId);
        return 'done,listener_activity_yiming';
    }

    /**
     * 一鸣惊人的活动监听
     * @param $productId
     */
    protected function listener_activity_yiming($productId){
        try{
            $params['productId'] = $productId;
            $params['actCode'] = \Prj\Model\Activity::yiming_code;
            \Prj\Bll\EventCoupon::getInstance()->sendCoupon($params);
        }catch (\Exception $e){
            \Prj\Loger::out($e->getMessage() , LOG_ERR);
        }
    }

    public function test_listener_activity_yiming($productId){
        return $this->listener_activity_yiming($productId);
    }
}

