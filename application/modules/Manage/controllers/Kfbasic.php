<?php
/**
 * Description of Kfusrbasic
 *
 * @author simon.wang
 */
class KfbasicController extends \Rpt\Manage\ManageCtrl{
    public function indexAction()
    {
        $phone = $this->_request->get('phone')-0;
        if($phone>0){
            
            $userInfoPannel=$this->userBasicInfo_inPannel($phone);
        }else{
            $userInfoPannel='';
        }
        \Prj\Framework\NavFindUserSimple::factory()->render($userInfoPannel);
        
    }
    protected function userBasicInfo_inPannel($phone)
    {
        
        $userObj = \Prj\Model\User::getCopyByPhone($phone);
        $userObj->load();
        if(!$userObj->exists()){
            return $phone.'的用户不存在，请确认登入手机号是否正确。';
        }
        $userInfoPannel='查询的手机号：'.$phone."<br>";
        
        $contractid = $userObj->getField('channelid');
        $copartnerid = substr($contractid,0,4);
        
        $coparter = \Prj\GH\GHCopartner::getByCopartnerCode($copartnerid);
        $coparter->load();
        if($coparter->exists()){
            $userInfoPannel.="注册时间：" .$userObj->getField('createTime'). "   渠道：".$coparter->getCopartnerName()."<br>";
        }else{
            $userInfoPannel.="注册时间：" .$userObj->getField('createTime'). "   渠道：<font color=red>没找到</font><br>";
        }
        
        return $userInfoPannel;
    }
 
}
