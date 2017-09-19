<?php
namespace PrjCronds\Standalone;
/**
 * 检查失败的订单，回复本金券状态
 * php /var/www/licai_php/run/crond.php "__=crond/run&task=Standalone.CheckNeedUnfreeze" 2>&1
 * @author Simon Wang <hillstill_simon@163.com>
 */
class Test01 extends \Sooh2\Crond\Task{
	public function init() {
		parent::init();
		$this->toBeContinue=true;
		$this->_secondsRunAgain=300;//每5分钟启动一次
		$this->_iissStartAfter=1000;//每小时01分后启动

		$this->ret = new \Sooh2\Crond\Ret();

	}
	public function free() {
		parent::free();
	}

	/**
	 * @param \Sooh2\Crond\Time $dt
	 */
	protected function onRun($dt) {
		if($this->_isManual){
			$m='manual';
		}else{
			$m='auto';
		}
		
		if($this->_counterCalled==0){
			error_log("[TRace]".__CLASS__.'# first by '.$m.'@'. getmypid().' for-time='.date('y-m-d H:i:s',$dt->timestamp()).'  stepcount='.$this->_counterCalled);
		}else{
			error_log("[TRace]".__CLASS__.'# continue by '.$m.'@'. getmypid().' for-time='.date('y-m-d H:i:s',$dt->timestamp()).' stepcount='.$this->_counterCalled);
		}
		$this->lastMsg = $this->ret->toString();//要在运行日志中记录的信息
		return true;
	}
}
