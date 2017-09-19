<?php

namespace Prj\Events\JavaEvt;

/**
 * 成功绑卡事件处理类
 *
 * @author simon.wang
 */
use Lib\Misc\Result;
use Prj\Bll\Finals\UserFinal;
USE Prj\Loger;

class BindOk extends \Sooh2\EvtQue\EvtProcess{

    public function onEvt()
    {
        $userId = $this->evtData->userId;
        $orderNo = $this->evtData->objId;
        \Prj\Model\_ModelBase::openForceReload(); //强制每次多查询数据库,防止旧数据被缓存
        \Prj\Loger::setKv(get_called_class());
        $msg = 'done';
        error_log("trace event on ".__CLASS__.' with data='. json_encode($this->evtData));
        $bindCouponMsg = $this->send_bind_coupon();
        $args = [];
        if(!is_array($this->evtData->args))$args = json_decode($this->evtData->args , true);
        $ret = \Prj\Bll\UserFinal::getInstance()->evtBindCard($userId , $orderNo , $args['idCardNo'] , $args['bankCardNo']);
        $msg .= ',send_bind_coupon#' . $bindCouponMsg . ',setInfo#' . $ret['message'];
        return $msg;
    }

    /**
     * Hand 发送绑卡红包
     * @param string $userId
     * @return mixed
     */
    protected function send_bind_coupon($userId = ''){
        \Prj\Loger::out('send_bind_coupon begin...');
        $userId = $userId ? $userId : $this->evtData->userId;
        if($this->isFirstBind($userId)){
            $params = [
                'userId' => $userId,
                'actCode' => \Prj\Model\Activity::bind_code,
            ];

            $ret = \Prj\Bll\EventCoupon::getInstance()->sendCoupon($params);
            return $ret['message'];
        }else{
            return '非首次绑卡';
        }
    }

    /**
     * Hand 是否首次绑卡
     * @param $userId
     * @return bool
     */
    protected function isFirstBind($userId){
        $count = \Prj\Model\Payment\BankBind::getCount([
            'userId' => $userId,
        ]);
        if($count == 1){
            return true;
        }else{
            \Prj\Loger::outVal('绑卡记录数量' , $count);
            return false;
        }
    }

    public function test($userId){
        return $this->send_bind_coupon($userId);
    }
}

