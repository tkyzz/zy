<?php
namespace Prj\Events\JavaEvt;

/**
 * 注册事件处理类
 *
 * @author simon.wang
 */

class RegisterOk extends \Sooh2\EvtQue\EvtProcess{

    public function init($evtData)
    {
        \Prj\Model\_ModelBase::openForceReload();
        return parent::init($evtData); // TODO: Change the autogenerated stub
    }

    public function onEvt()
    {
        $userId = $this->evtData->userId;
        // $msg1 = $this->send_register_sms_for_activity($userId); //注册欢迎短信
        $msg2 = $this->listen_register_coupon($userId); //注册红包监听
        $msg3 = $this->register_Sms($userId);
        $msg4 = $this->buildQrcode($userId);
        return 'done'.',send_register_coupon#' . $msg2."#send_register_sms#".$msg3 . '  #buildQrcode result#' . $msg4;
    }

    /**
     * 注册时即生成此人的邀请码
     * @param string $userId 用户ID
     * @return bool
     * @author lingtima@gmail.com
     */
    public function buildQrcode($userId)
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($userId);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            $url = \Prj\Bll\Invite::getInstance()->buildJumpUrl($userId);
            $name = $ModelUserFinal->getField('inviteCode');
            try {
                $ret = \Prj\Bll\Invite::getInstance()->buildQrcode($name, $url);
                $ModelUserFinal->setField('inviteQrcode', $ret);
                $ModelUserFinal->saveToDB();
                return true;
            } catch (\Exception $e) {
                \Sooh2\Misc\Loger::getInstance()->app_warning('build Qrcode Error,info:');
                \Sooh2\Misc\Loger::getInstance()->app_trace($e->getMessage());
            }
        }
        return false;
    }

    /**
     * Hand 活动发送注册短信
     * @param $userId
     * @return array|string
     */
    public function send_register_sms_for_activity($userId){
        $user = \Prj\Model\User::getCopy($userId);
        $user->load();
        if($user->exists()){
            $userInfo = $user->dump();
            return \Prj\Bll\MiActivy::getInstance()->sendRigisterSMS($userInfo)['message'];
        }else{
            return '用户不存在';
        }
    }

    /**
     * Hand 注册红包监听
     * @param $userId
     * @return mixed
     */
    public function listen_register_coupon($userId){
        $params = [
            'actCode' => \Prj\Model\Activity::register_code,
            'userId' => $userId,
        ];
        $ret = \Prj\Bll\EventCoupon::getInstance()->sendCoupon($params);
        return $ret['message'];
    }

    public function register_Sms($userId , $bool = false){
        if(!$bool){
            $params = ['userId'=>$userId];
            $ret = \Prj\Bll\SendSmsForEvt::getInstance()->sendRegisterMsg($params);
            return $ret;
        }

    }

    public function test_coupon($userId){
        return $this->listen_register_coupon($userId);
    }

    public function test_sms($userId){
        return $this->send_register_sms_for_activity($userId);
    }



}
