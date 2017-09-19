<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/17
 * Time: 11:11
 */
namespace Prj\EvtMsg;

class SendSmsByPhone extends Sender
{
    public function run($title, $content, $phones = [] ) {
        $ways = ['smsnotice'];
        $evtmsgid = 'custom';
        $logclass = $this->getLogerClassname();
        $this->_loger = $logclass::createNew($title,$content,$phones,$ways,$evtmsgid);
        if($this->_loger==null){
            \Sooh2\Misc\Loger::getInstance()->app_trace("EvtMSG[$evtmsgid] send $content to ". json_encode($phones)." failed as : log record create failed");
            return;
        }
        $ret = parent::sendCustomMsg($title, $content, $phones, $ways, $evtmsgid);
        return $ret;
    }

    protected function getUserForSender($phones,$sender){
        return $phones;
    }

    public function sendEvtMsg($evtmsgid, $user, $replace)
    {
        $this->_evtmsgid = $evtmsgid;
        $msgTpl = $this->getMsgTplCtrl($evtmsgid);
        if ($msgTpl) {
            $title = str_replace(array_keys($replace), $replace, $msgTpl->getTitleTpl());
            $content = str_replace(array_keys($replace), $replace, $msgTpl->getContentTpl());
            $ways = $msgTpl->getWays();
            $this->sendCustomMsg($title, $content, $user, $ways, $evtmsgid);
        } else {
            throw new \ErrorException('msg config not found');
        }
    }

    /**+
     * Hand
     * @param null $newInstance
     * @return self
     */
    public static function getInstance($newInstance=null)
    {
        if($newInstance){
            self::$_instance = $newInstance;
        }elseif(self::$_instance===null){
            $c = get_called_class();
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
}