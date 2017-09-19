<?php

namespace Prj\Events\JavaEvt;

/**
 * 满标事件处理类
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
USE Prj\Loger;

class PrdFull extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        \Prj\Loger::setKv(get_called_class());
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $ret = $this->listener_activity_onchui($this->evtData->objId);
        return 'done,listener_activity_onchui#' . $ret;
    }

    /**
     * Hand 一锤定音活动监听
     * @param $productId
     * @return null
     */
    protected function listener_activity_onchui($productId){
        try{
            $params['productId'] = $productId;
            $params['actCode'] = \Prj\Model\Activity::onechui_code;
            $ret = \Prj\Bll\EventCoupon::getInstance()->sendCoupon($params);
            return $ret['message'];
        }catch (\Exception $e){
            \Prj\Loger::out($e->getMessage() , LOG_ERR);
            return $e->getMessage();
        }
    }

    public function test($productId){
        return $this->listener_activity_onchui($productId);
    }
}

