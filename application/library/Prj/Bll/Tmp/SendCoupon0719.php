<?php
/**
 * 发送消息
 * User: amdin
 * Date: 2017/7/19
 * Time: 16:07
 */

namespace Prj\Bll\Tmp;

use Prj\Bll\_BllBase;
use Prj\Bll\User;
use Prj\EvtMsg\Sender;
use Prj\Loger;
use Rpt\Manage\HandMail;

class SendCoupon0719 extends _BllBase{

    protected $apporach = ['push','smsnotice','smsmarket','msg'];
    public function crond(){
        \Prj\Loger::$prefix = '[crond]';
//        if(!\Prj\Tool\Debug::isTestEnv()){
            if(!$this->getSendCouponCount()){
                Loger::out("没有未发的消息");
                return;
            }
//        }
        \Prj\Loger::out('crond start...');
        $this->resetCache();
    }


    public function getSendCouponCount(){
        return HandMail::getRecord("count(*)",array('statusCode'=>0));
    }

    public function resetCache(){
        $this->sendDataFromDb();
        return $this->resultOK();
    }


    public function sendDataFromDb()
    {
        $couponInfo = HandMail::getRecords('*', array('statusCode' => 0));
        if(isset($couponInfo['phone'])){
            $info[0] = $couponInfo;
        }else{
            $info = $couponInfo;
        }

        $checkdata = $this->checkDataInfo($info);
        if(!$checkdata['status']) return $this->resultError($checkdata['msg']);

        if ($info) {
            foreach ($info as $k => $v) {
                $userid = \Prj\Model\User::getRecord('oid', array('UserAcc' => $v['phone']));
                $approach = explode("|",$v['approach']);
                Sender::getInstance()->sendCustomMsg($v['title'], $v['content'], $userid['oid'], $approach);
                HandMail::updateOne(array('statusCode' => 8, 'ret' => '发送消息成功'), array('id' => $v['id']));
//                \Rpt\Manage\HandMail::updateOne(array('ret'=>'更新成功'),array('id'=>$v['id']));

            }
        }
    }


        public function checkDataInfo($info){
            foreach($info as $k => $v){
                if(!preg_match("/^1[34578]{1}\d{9}$/",$v['phone'])) {
                    Loger::out("手机号为：".$v['phone']." 的用户手机格式输入错误");
                    return array("status"=>0,"msg"=>"手机号为：".$v['phone']." 的用户手机格式输入错误");
//                    return $this->resultError("手机号为：".$v['phone']." 的用户手机格式输入错误");
                }
                $userid = \Prj\Model\User::getRecord('oid', array('UserAcc' => $v['phone']))['oid'];
                if(empty($userid)) {
                    Loger::out("未找到手机号为：".$v['phone']."的用户信息");
                    return array('status'=>0,"msg"=>"未找到手机号为：".$v['phone']." 的用户信息");
//                    return $this->resultError("手机号为：".$v['phone']." 的用户未找到此用户信息");
                }
                if(empty($v['approach'])) {
                    Loger::out("手机号为".$v['phone']."的发送通道为空");
                    return array("status"=>0,'msg'=>"手机号为".$v['phone']."的用户发送通道为空");
//                    return $this->resultError("手机号为".$v['phone']."的用户发送通道为空");
                }
                $approach = explode("|",$v['approach']);
                foreach($approach as $k1 => $v1){
                    if(!in_array($v1,$this->apporach)) {
                        Loger::out("手机号为".$v['phone']."的用户发送通道名为".$v1."为错误格式");
                        return array("status"=>0,'msg'=>"手机号为".$v['phone']."的用户发送通道名为".$v1."为错误格式");
//                        return $this->resultError("手机号为".$v['phone']."的用户发送通道名为".$v1."为错误格式");
                    }
                }
                if(empty($v['title'])) {
                    Loger::out("手机号为".$v['phone']."的标题不能为空");
                    return array("status"=>0,"msg"=>"手机号为".$v['phone']."的标题不能为空");
//                    return $this->resultError("手机号为".$v['phone']."的标题不能为空");
                }
                if(empty($v['content'])) {
                    Loger::out("手机号为".$v['phone']."的内容不能为空");
                    return array("status"=>0,"msg"=>"手机号为".$v['phone']."的内容不能为空");
//                    return $this->resultError("手机号为".$v['phone']."的内容不能为空");
                }
            }

            return array("status"=>1,"msg"=>"验证成功");
        }



}