<?php
namespace Prj\Tool;

/**
 * 发送邮件的类
 *
 * @author simon.wang
 */
class Email {
    private $user = 'it_yunwei@zhangyuelicai.com';
    private $pass = 'Zhangyue1234';
    private $curAction;
    public function dailyChkResultAction($filename=null)
    {
        $this->curAction = __FUNCTION__;
        $sendTo = array('wangning@zhangyuelicai.com',
            'taoman@zhangyuelicai.com','wangyuanyuan@zhangyuelicai.com',
            'yeyi@zhangyuelicai.com' );
        
        $content = nl2br(file_get_contents($filename));
        
        $this->_sendHTMLMail($sendTo,"每日对账结果(".date('Y-m-d').")", $content); //设置邮件主题、内容
    }
    protected function _sendHTMLMail($sendTo,$title,$content)
    {
        $mail = new \Sooh2\SMTP();
        //$mail->setServer("smtp@126.com", "XXXXX@126.com", "XXXXX"); //设置smtp服务器，普通连接方式
        $mail->setServer("smtp.exmail.qq.com", $this->user, $this->pass, 465, true); //设置smtp服务器，到服务器的SSL连接
        $mail->setFrom($this->user); //设置发件人
        foreach ($sendTo as $u){
            $mail->setReceiver($u); //设置收件人，多个收件人，调用多次
        }
        //$mail->setCc("XXXX"); //设置抄送，多个抄送，调用多次
        //$mail->setBcc("XXXXX"); //设置秘密抄送，多个秘密抄送，调用多次
        //$mail->addAttachment("XXXX"); //添加附件，多个附件，调用多次
        $mail->setMail($title, $content); //设置邮件主题、内容
        $ret = $mail->sendMail(); //发送
        if($ret==false){
            error_log("send mail failed({$this->curAction}):".$mail->error()."\n");
            echo "send mail failed({$this->curAction}):".$mail->error()."\n";
        }
    }
    public function addrForHttpdError()
    {
        return array('wangning@zhangyuelicai.com');
    }
    /**
     * 
     * @return \Sooh2\SMTP
     */
    public function getMail()
    {
        $mail = new \Sooh2\SMTP();
        //$mail->setServer("smtp@126.com", "XXXXX@126.com", "XXXXX"); //设置smtp服务器，普通连接方式
        $mail->setServer("smtp.exmail.qq.com", $this->user, $this->pass, 465, true); //设置smtp服务器，到服务器的SSL连接
        $mail->setFrom($this->user); //设置发件人
        return $mail;
    }

    public function sendMail($sendTo , $title , $content){
        return $this->_sendHTMLMail($sendTo , $title , $content);
    }

}
