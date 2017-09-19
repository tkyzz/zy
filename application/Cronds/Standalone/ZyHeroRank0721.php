<?php
namespace PrjCronds\Standalone;
/**
 * 定时更新活动排行榜
 * php /usr/local/openresty/nginx/html/php/console/run.php request_uri=/console/hourly?task=Standalone.ZyHeroRank0721&ymdh=2017072110 2>&1
 * @author Simon Wang <hillstill_simon@163.com>
 */
class ZyHeroRank0721 extends \Sooh2\Crond\Task{
	public function init() {
		parent::init();
		$this->toBeContinue=true;
		$this->_secondsRunAgain= 60 * 30;//每30分钟启动一次
		$this->_iissStartAfter = 0;//每小时00分后启动

		$this->ret = new \Sooh2\Crond\Ret();

	}
	public function free() {
		parent::free();
	}

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
        \Prj\Bll\Tmp\ZyHeroRank0913::getInstance()->crond();
		return true;
	}
}
