<?php
/**
 * 代码报警通知类
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/14
 * Time: 16:47
 */
namespace Prj\Tool;

class Warning extends \Prj\Bll\_BllBase
{
    protected $mailArr = [
        'tanggaohang@zhangyuelicai.com',
        'chengzihao@zhangyuelicai.com'
    ];

    protected $phoneArr = [
        '13262798028'
    ];

    public function sendSMS($phones = [] , $msg){
        \Prj\Loger::out('【报警通知】sms ' . implode(',' , $phones) . ' ' .$msg);
        return \Prj\EvtMsg\SendSmsByPhone::getInstance()->run('WARNING' , $msg , $phones );
    }

    public function sendMAIL($addr = [] , $title = '' , $content = ''){
        \Prj\Loger::out('【报警通知】mail ' . implode(',' , $addr) . ' ' .$title.' '.$content);
        $mail = new \Prj\Tool\Email();
        return $mail->sendMail($addr , $title , $content);
    }

    public function warnSMS($msg){
        $msg = '【报警】' . $msg;
        return $this->sendSMS($this->phoneArr , $msg);
    }

    public function warnMAIL($title , $content){
        $title = '【报警】' . $title;
        return $this->sendMAIL($this->mailArr , $title , $content);
    }
}