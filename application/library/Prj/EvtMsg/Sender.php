<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\EvtMsg;

/**
 * 发送消息的封装类
 * 支持的消息通道（msg：站内信，pushstd：标准推送，pushext：扩展推送，smsnotice：通知短信，smsmarket：营销短信,log 日志）
 * 注意，发送多个用户的情况，重试也是多个用户重复发
 * @author simon.wang
 */
class Sender extends \Sooh2\Messager\BrokerWithLog{
    
    /**
     * 根据发送器标示获取发送器
     * @param string $id
     * @return \Sooh2\Messager\Sender
     */
    protected function getSenderCtrl($id){
        $conf = \Sooh2\Misc\Ini::getInstance()->getIni('Messager.'.$id);
        $class = $conf['class'];
        return $class::getInstance($conf['ini']);
    }
    
    /**
     * 
     * @param mixed $user
     * @param \Sooh2\Messager\Sender $sender 
     * @return mixed 返回sender需要的用户的列表
     */
    protected function getUserForSender($user,$sender){
        if(!is_array($user)){
            $user = explode(',', $user);
        }

        switch ($sender->needsUserField()){
            case 'phone':
                foreach($user as $i=>$uid){
                    $user[$i] = $this->getUsrField($uid, 'userAcc');
                    if(empty($user[$i])){
                        unset($user[$i]);
                        \Sooh2\Misc\Loger::getInstance()->app_warning('Error found when try send msg: phone of u:'.$uid.' not found for '.$this->_evtmsgid);
                    }
                }
                break;
            case 'innerid'://直接返回userOid列表,应该是用于站内信的
                break;
            case 'outerid'://个推的那里用的
                throw new \ErrorException('todo outerid not coded');
                break;
            case 'email':
                throw new \ErrorException('todo email not coded');
            case 'custom' :
                $user = $sender->getUser($user);
                break;
            default :
                throw new \ErrorException('user field for send msg not support: '.$sender->needsUserField());
        }
        return $user;
    }
    private function getUsrField($uid,$field)
    {
        $obj = \Prj\Model\User::getCopy($uid);
        $obj->load();
        if($obj->exists()){
            $ret = $obj->getField($field);
        }else{
            $ret = null;
        }
        \Prj\Model\User::freeCopy($obj);
        return $ret;
    }

    /**
     * Hand 设置发送器的配置
     * @param $senderId
     * @param array $params
     * @return $this
     */
    public function setSender($senderId , $params = []){
        $sender = $this->getSenderCtrl($senderId);
        foreach ($params as $k => $v){
            $sender->conf[$k] = $v;
        }
        return $this;
    }


}
