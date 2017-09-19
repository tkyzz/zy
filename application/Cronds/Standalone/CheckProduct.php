<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/24
 * Time: 10:34
 */
namespace PrjCronds\Standalone;

use Sooh2\Crond\Task;

class CheckProduct extends Task
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->toBeContinue = true;
        $this->_secondsRunAgain = 30*60;
        $this->_iissStartAfter = 0;
        $this->ret = new \Sooh2\Crond\Ret();
    }


    public function free()
    {
        parent::free(); // TODO: Change the autogenerated stub
    }



    public function onRun($dt)
    {

        if($this->_isManual){
            $m = "manual";
        }else{
            $m = "auto";
        }

        if($this->_counterCalled==0){
            error_log("[TRace]".__CLASS__.'# first by '.$m.'@'. getmypid().' for-time='.date('y-m-d H:i:s',$dt->timestamp()).'  stepcount='.$this->_counterCalled);
        }else{
            error_log("[TRace]".__CLASS__.'# continue by '.$m.'@'. getmypid().' for-time='.date('y-m-d H:i:s',$dt->timestamp()).' stepcount='.$this->_counterCalled);
        }
        $this->lastMsg = $this->ret->toString();//要在运行日志中记录的信息
        \Prj\Bll\CheckProduct::getInstance()->crond();
        return true;
    }
}