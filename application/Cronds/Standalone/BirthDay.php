<?php
namespace PrjCronds\Standalone;
/**
 * 定时更新活动排行榜
 * php /var/www/licai_php/run/crond.php "__=crond/run&task=Standalone.ZyHeroRank0721" 2>&1
 * @author Simon Wang <hillstill_simon@163.com>
 */
class BirthDay extends \Sooh2\Crond\Task{
	public function init() {
		parent::init();
		$this->toBeContinue=true;
		$this->_secondsRunAgain= 60 * 60;//每60分钟启动一次
		$this->_iissStartAfter = 1;//每小时00分后启动

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

		if(date('H') == '08'){
            //每天检测过生日的人
            \Prj\Bll\ActRuleZy\ActBirth::getInstance()->crondBirSMS();

		    if(date('d') == '01'){
		        //每个月1号开始发放礼包
		        \Prj\Bll\ActRuleZy\ActBirth::getInstance()->crondBirth();
            }
        }

		return true;
	}

}
