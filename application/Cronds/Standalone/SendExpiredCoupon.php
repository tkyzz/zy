<?php
namespace PrjCronds\Standalone;

use Prj\Loger;
use Sooh2\Crond\Task;
/*
 * 发送过期红包*/
class SendExpiredCoupon extends Task{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->toBeContinue=true;
        $this->_secondsRunAgain= 60 * 60;//每30分钟启动一次
        $this->_iissStartAfter = 0;//每小时00分后启动

        $this->ret = new \Sooh2\Crond\Ret();
    }

    public function free()
    {
        parent::free(); // TODO: Change the autogenerated stub
    }


    protected function onRun($dt)
    {
        if($this->_isManual){
            $m = 'manual';
        }else{
            $m = "auto";
        }

        if($this->_counterCalled==0){
            error_log("[TRace]".__CLASS__.'# first by '.$m.'@'. getmypid().' for-time='.date('y-m-d H:i:s',$dt->timestamp()).'  stepcount='.$this->_counterCalled);
        }else{
            error_log("[TRace]".__CLASS__.'# continue by '.$m.'@'. getmypid().' for-time='.date('y-m-d H:i:s',$dt->timestamp()).' stepcount='.$this->_counterCalled);
        }
        $this->lastMsg = $this->ret->toString();
        $hour = date('H');
        Loger::outVal("hour",$hour);
        if($hour == 10){
            \Prj\Bll\ExpiredCoupon::getInstance()->crond();
        }
//        SendCoupon0719::getInstance()->crond();
        return true;
    }
}