<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/13 0013
 * Time: 下午 1:41
 */
class OldDriverController extends \Prj\Framework\Ctrl
{

    /**
     * @SWG\Post(path="/actives/OldDriver/driverInfo", tags={"Activity"},
     *   summary="老司机活动",
     *   description="老司机活动信息",
     * )
     */
    public function driverInfoAction(){
        \Prj\Loger::outVal('cookie' , $_COOKIE);
        $userOid = $this->getUidInSession();
        if (!$userOid) {
            return $this->assignCodeAndMessage('not login', 99999);
        }
         \Prj\Loger::outVal('driverInfo---userOid' , $userOid);
         //$userOid="ff8080815ca53f41015ca54d017f0000";//司机
         //$userOid="ff8080815ca5f6d1015ca996be9c0002";//乘客
         //$userOid="ff8080815ca5f6d1015cab81b6d00003"; //司机加乘客
         //$userOid="ff8080815ca5f6d1015cabb890430004"; //没有成为司机
         //$userOid="ff8080815ca5f6d1015ca66f30b70001"; //被邀请没有成为乘客
         //$userOid="428ff61017283759e02e0cb2b2d4";
        $arr=\Prj\Model\JzUserFinal::getRecord("*",['wfdUserId'=>$userOid]);
        $phone=$arr['phone'];
        //$inviteByUser=$arr['inviteByUser'];//查找我的邀请人
        //$contractId=$arr['contractId'];

        $myInviteCode=$arr['myInviteCode'];
        $myName=$arr['realname'];
        $invite=\Prj\Model\JzUserFinal::getUserByUserId($arr['inviteByUser']);
        $inviteByUser=$invite['wfdUserId'];
        //查找channelid
        $ucUser=\Prj\Model\UcUser::getUserByOid($userOid);
        $channelId=$ucUser['channelid'];

        $iniCid=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.contractId');
        if($inviteByUser=="" || $channelId != $iniCid){
            $count=\Prj\Model\OldDriver::getCount(['driveroid'=>$userOid]);
            if(!$count){
                $data['status']=4;
                $data['inviteCode']=$myInviteCode;
                $data['myPhone']=$phone;
                $data['myName']=$myName;
                $this->_view->assign('data' ,$data);
                return $this->assignCodeAndMessage('您还没有成为老司机!');
            }else{
                $data['status']=1;
                $data['inviteCode']=$myInviteCode;
                $data['myPhone']=$phone;
                $data['myName']=$myName;
                $data['myCarStatus']=$this->getCarStatus($userOid);
                $data['passenger']=$this->getMyPassenger($userOid);
                if($data['myCarStatus']){
                    $data['totalMoney']=88+(count($data['passenger'])-2)*66;
                }else{
                    $data['totalMoney']=0;
                }
                $this->_view->assign('data' ,$data);
                return $this->assignCodeAndMessage('已经成为老司机');
            }
        }else{
            $count=\Prj\Model\OldDriverPassenger::getCount(['passengeroid'=>$userOid]);
            if(!$count){
                $data['status']=5;
                $data['inviteCode']=$myInviteCode;
                $data['myPhone']=$phone;
                $data['myName']=$myName;
                $this->_view->assign('data' ,$data);
                return $this->assignCodeAndMessage('您还没有投资成为乘客!' );
            }else{
                $countDriver=\Prj\Model\OldDriver::getCount(['driveroid'=>$userOid]);
                if(!$countDriver){
                    $data['status']=2;
                    $data['inviteCode']=$myInviteCode;
                    $data['myPhone']=$phone;
                    $data['myName']=$myName;
                    $data['inviteCarStatus']=$this->getCarStatus($inviteByUser);
                    if($data['inviteCarStatus']){
                        $data['totalMoney']=$data['inviteMoney']=66;
                    }else{
                        $data['totalMoney']=$data['inviteMoney']=0;
                    }
                    $data['myDriver']=$this->getDriverPhone($inviteByUser);
                    $this->_view->assign('data' ,$data);
                    return $this->assignCodeAndMessage('您已经上车成为乘客!' );
                }else{
                    $data['status']=3;
                    $data['inviteCode']=$myInviteCode;
                    $data['myPhone']=$phone;
                    $data['myName']=$myName;
                    $data['myDriver']=$this->getDriverPhone($inviteByUser);
                    $data['inviteCarStatus']=$this->getCarStatus($inviteByUser);
                    $data['myCarStatus']=$this->getCarStatus($userOid);
                    $data['passenger']=$this->getMyPassenger($userOid);
                    if($data['inviteCarStatus']){
                        $data['inviteMoney']=66;
                    }else{
                        $data['inviteMoney']=0;
                    }
                    if($data['myCarStatus']){
                        $data['totalMoney']= $data['inviteMoney']+88+(count($data['passenger'])-2)*66;
                    }else{
                        $data['totalMoney']= $data['inviteMoney'];
                    }

                    $this->_view->assign('data' ,$data);
                    return $this->assignCodeAndMessage('投资大于两笔，上车成功并且成为老司机成功' );
                }
            }
        }

    }


    /**
     * 获取司机的手机号
     * @param string $oid 司机oid
     * @return string
     */
    protected function getDriverPhone($oid){
        $obj=\Prj\Model\OldDriver::getRecord("driverPhone",['driveroid'=>$oid]);
        $driverPhone=$obj['driverPhone'];
        return $driverPhone;
    }

    /**
     *获取是否已经发车成功
     * @param string $oid 司机oid
     * @return boolean
     */
    protected function getCarStatus($oid){
        $count=\Prj\Model\OldDriverPassenger::getCount(['driveroid'=>$oid]);
        if($count>=2){
            return true;
        }else{
            return false;
        }
    }

    /**
     *获取司机的乘客列表
     * @param string $oid 司机oid
     * @return array
     */
    protected function getMyPassenger($oid){
        $passenger=\Prj\Model\OldDriverPassenger::getRecords("*",['driveroid'=>$oid]);
        $data=array();
        foreach($passenger as $v){
            //$data[]=\Prj\Model\JzUserFinal::getRecord("*",['wfdUserId'=>$v['passengeroid']])['phone'];
            //$data[]=substr_replace($v['passengerPhone'],'****',3,4);
            $data[]=$v['passengerPhone'];
        }
        return $data;
    }

    /**
     *获取用户手机号
     * @param string $userOid  用户的userOid
     * @return string
     */
    protected function getUserPhone($userOid){
        $obj=\Prj\Model\JzUserFinal::getRecord("phone",['wfdUserId'=>$userOid]);
        return $obj['phone'];

    }

    /**
     *获取用户登陆信息
     */
    protected function getUidInSession($userOid = null)
    {
        if(!empty($userOid))return $userOid;
        return \Prj\Session::getInstance()->getUid();
    }
}