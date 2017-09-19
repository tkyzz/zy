<?php

namespace Prj\Bll;

use Lib\Misc\Result;

class OldDriver extends \Prj\Bll\_BllBase
{
    protected function init(){
        \Prj\Loger::$prefix .= '[OldDriver]';
    }

    public function sendOrder($orderId){
        $this->log('orderId: '.$orderId);
        $orderInfo = \Prj\Model\MimosaTradeOrder::getOne(['oid' => $orderId]);
        $this->log('productId: '.$orderInfo['productOid']);
        $res = $this->checkOrder($orderInfo);
        return $res;

    }


    /**
     *检查订单条件是成为司机还上是成为乘客
     * @param array $orderInfo 订单信息
     * @return array
     */
    protected  function checkOrder($orderInfo){
        if(!count($orderInfo)){
            $this->fatalErr('未查询到订单信息!');
        }

        $startTime=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.startTime');
        $endTime=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.endTime');
        $startTime=strtotime($startTime);
        $endTime=strtotime($endTime);
        $orderTime=strtotime($orderInfo['orderTime']);
        if($orderTime>$endTime || $orderTime<$startTime){
            return $this->resultError('订单时间不在活动时间范围内!');
        }


        if(!in_array($orderInfo['orderStatus'] , \Prj\Model\MimosaTradeOrder::$orderStatus_success)){
            return $this->resultError('订单尚未交易成功!');
        }

        if($orderInfo['orderType']!="invest"){
            return $this->resultError('订单类型不是投资，不符合活动规则！');
        }
        if($orderInfo['orderAmount']<2000){
            return $this->resultError('订单金额过小，不符合活动规则!');
        }

        $product=\Prj\Model\GamProduct::getProductByOid($orderInfo['productOid']);
        $durationPeriodDays=$product['durationPeriodDays'];
        if($durationPeriodDays<120){
            return $this->resultError('订单期限过短，不符合活动规则!');
        }

        $miUser = \Prj\Model\MimosaUser::getUserByMiUserId($orderInfo['investorOid']);
        if(empty($miUser)){
            $this->fatalErr('未查询到投资者信息!');
        }
        $phone=$miUser['phoneNum'];
        $userOid=$miUser['userOid'];
        //查找channelid
        $ucUser=\Prj\Model\UcUser::getUserByOid($userOid);
        $channelId=$ucUser['channelid'];
        $jzUser=\Prj\Model\JzUserFinal::getUserByWfdUserId($userOid);

        $invite=\Prj\Model\JzUserFinal::getUserByUserId($jzUser['inviteByUser']);//查找我的邀请人
        $inviteByUser=$invite['wfdUserId'];
        //$contractId=$jzUser['contractId'];

        $iniCid=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.contractId');

        if($durationPeriodDays=='180' && $orderInfo['orderAmount']>=2000 ){
            if($inviteByUser=="" || $channelId != $iniCid){
               return $this->checkDriver($userOid,$phone);
            }else{
               return $this->checkDriverPassenger($inviteByUser,$userOid,$phone);
            }
        }
        else if($durationPeriodDays=='120' && $orderInfo['orderAmount']>=3000){
            //todo
            if($inviteByUser=="" || $channelId != $iniCid){
                return $this->checkDriver($userOid,$phone);
            }else{
                return $this->checkDriverPassenger($inviteByUser,$userOid,$phone);
            }
        }else{
            return $this->resultError('产品期限'.$durationPeriodDays.'天，投资金额'.$orderInfo['orderAmount'].'元不符合活动规则!');
        }
    }

    /**
     * 成为司机
     * @param string $userOid 用户oid
     * @param string $phone  用户手机
     * @return array
     */
    protected function checkDriver($userOid,$phone){
        $obj=\Prj\Model\OldDriver::getCopy(['driveroid'=>$userOid]);
        $obj->load();
        if(!$obj->exists()){
            $obj->setField('createTime', date("Y-m-d H:i:s"));
            $obj->setField('driverPhone', $phone);
            $ret=$obj->saveToDB();
            if($ret){
                $this->log($userOid.'记录入库成为司机');
                $this->log($userOid.'发送老司机通知短信');
                $smsContent=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.msg.smsContent');
                $pushTitle=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.msg.pushTitle');
                $pushContent=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.msg.pushContent');
                $pushTplId=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.msg.pushTplId');
                \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('老司机短信',$smsContent,$userOid, array('smsnotice'), 'olddriver');
                $this->log($userOid.'发送老司机推送');
                \Prj\EvtMsg\JavaApiPush::getInstance('')->setTemplateId($pushTplId);
                \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($pushTitle , $pushContent, array($userOid) , ['push'] , 'olddriver');
                return Result::get(RET_SUCC , '成为司机成功!');
            }else{
                $this->log($userOid.'记录入库成为司机失败');
                return $this->resultError('记录入库成为司机失败');
            }
        }else{
            $this->log($userOid.'已经是司机');
            return $this->resultError('已经是司机');
        }
    }

    /**
     * 成为司机或者乘客
     * @param string $invite  邀请人oid
     * @param string $userOid 用户oid
     * @param string $phone   用户手机
     * @return array
     */
    protected function checkDriverPassenger($invite,$userOid,$phone){
        $passenger=\Prj\Model\OldDriverPassenger::getRecords('*',['driveroid'=>$invite]);
        $countPassenger=count($passenger);
        $obj=\Prj\Model\OldDriverPassenger::getCopy(['driveroid'=>$invite,'passengeroid'=>$userOid]);
        $obj->load();
        if(!$obj->exists()){
            $obj->setField('createTime', date("Y-m-d H:i:s"));
            $obj->setField('passengerPhone', $phone);
            $ret=$obj->saveToDB();
            if($ret) {
                $this->log($userOid.'记录入库成为乘客上车');
                $countPassenger++;
                if($countPassenger==2){
                    $this->sendCouponAndMsg($invite,'Start');
                    $this->sendCouponAndMsg($userOid,'On');
                    $this->sendCouponAndMsg($passenger[0]['passengeroid'],'On');
                }
                else if($countPassenger>2){
                    $this->sendCouponAndMsg($invite,'Carry');
                    $this->sendCouponAndMsg($userOid,'On');
                    //给司机和$userOid66红包
                }
                return Result::get(RET_SUCC , '上车成功!');
            }else{
                $this->log($userOid.'记录入库成为乘客失败');
                return $this->resultError('记录入库成为乘客失败');
            }

        }else{
            $this->log($userOid.'已经成为乘客,检查是否可以作为司机');
            return $this->checkDriver($userOid,$phone);
        }

    }

    /**
     * 发红包和站内信
     * @param string $userOid 用户Oid
     * @param string $type 红包类型
     * @return boolean
     */
    protected function sendCouponAndMsg($userOid,$type){
        $data=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.'.$type);
        $productList=[
            ['productCode' => '4', 'productName' => '悦享盈'],
            ['productCode' => '5', 'productName' => '悦嘉盈'],
        ];
        $this->log($userOid .'开始发送'.$data['name']);
        $send = new \Lib\Services\SendCoupon;
        $send->setUserId($userOid)
             ->setDesc($data['description'])
             ->setDisableDate($data['disableDate'])
             ->setName($data['name'])
             ->setCouponType($data['couponType'])
             ->setProductList($productList)
             ->setInvestAmount($data['investAmount'])
             ->setAmount($data['totalAmount'])
             ->setReqOid();
        $ret = $send->sendCouponToUser();
        list($db,$obj) = \Prj\Model\OldDriverCoupon::getCopy(null)->dbAndTbName();
        $changed['reqOid']=$send->getReqOid();
        $changed['userOid']=$userOid;
        $changed['name']=$data['name'];
        $changed['amount']=$data['totalAmount'];
        $changed['status']='success';
        $changed['createTime']=date('Y-m-d H:i:s');
        if($ret){
            \Prj\EvtMsg\InnerMsg::getInstance('')->sendTo($userOid,$data['msgContent'],$data['msgTitle']);
            $db->addRecord($obj,$changed);
            $this->log($userOid .'发送'.$data['name'].'成功');
            return true;
        }else{
            $this->log($userOid .'发送'.$data['name'].'失败,尝试再发一次');
            $ret = $send->sendCouponToUser();
            if($ret){
                \Prj\EvtMsg\InnerMsg::getInstance('')->sendTo($userOid,$data['msgContent'],$data['msgTitle']);
                $db->addRecord($obj,$changed);
                $this->log($userOid .'发送'.$data['name'].'成功');
                return true;
            }
            $changed['status']='failed';
            $db->addRecord($obj,$changed);
            $this->log($userOid .'发送'.$data['name'].'失败');
            return false;
        }
    }

}