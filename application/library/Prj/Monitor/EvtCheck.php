<?php
/**
 * 队列事件检查
 *
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/14
 * Time: 16:21
 */
namespace Prj\Monitor;

class EvtCheck extends \Prj\Bll\_BllBase
{
    protected $interval = 3000; //间隔秒

    public function run($manual = false){
        \Prj\Loger::setKv('EvtCheck');
        $key = 'php:tmp:EvtCheck';
        $record = $this->getOne();
        if(!empty($record)){
            $evtId = $record['evtid'];
            sleep(1);
            $record = $this->getOne();
            if(!empty($record) && $evtId == $record['evtid']){
                //发送报警邮件
                if(!$manual){
                    $val = \Prj\Redis\Base::get($key);
                    if($val)return;
                }
                $title = '[evtque]事件通知阻塞，请尽快处理！';
                $content = json_encode($record , 256);
                $content .= "<br>From " . $this->getServerIP();
                \Prj\Tool\Warning::getInstance()->warnMAIL($title , $content);
                if(!$manual){
                    \Prj\Redis\Base::set($key , 1 , $this->interval);
                }
            }
        }
    }

    protected function getOne(){
        return \Prj\Model\Evtque::getRecord(null , ['!ret' => ''] , 'sort evtid');
    }

    protected function getServerIP(){
        $arr = explode(' ' , $_SERVER['SSH_CONNECTION']);
        return $arr['2'];
    }

    public function test(){
        $title = '[evtque]事件通知阻塞，请尽快处理！';
        $content = 'xxxxxxxxxxxxxxxxxxx';
        $content .= "\n<br>From " . $this->getServerIP();
        \Prj\Tool\Warning::getInstance()->warnMAIL($title , $content);
    }
}