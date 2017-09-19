<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/26
 * Time: 14:07
 */
namespace Prj\Bll;

use Prj\EvtMsg\Sender;
use Prj\Loger;
use Prj\Model\Mimosa\Label;
use Prj\Model\ZyBusiness\ProductLabel;
use Sooh2\BJUI\Ini;

class SendProductMsg extends _BllBase
{
    public function crond(){
        \Prj\Loger::$prefix = '[sendProductMsg]';
//        if(!\Prj\Tool\Debug::isTestEnv()){
//            Loger::out("此为测试环境");
//            return;
//        }

        $now = date("H");
        $data = $this->getJsonInfoAction();
        if(empty($data)) $data = self::defaultData();
        if(intval($now)<$data['beginHour']&&intval($now)>$data['endHour']){
            Loger::out("发送警报短信的时间为：".$data['beginHour']."点到".$data['endHour']."点");
            return;
        }
        \Prj\Loger::out('crond start...');
        $this->sendMsg($data);
    }


    public function sendMsg($data){
        $productLabel = ProductLabel::getRecords("distinct(labelId)",null);
        $productLabel = array_column($productLabel,'labelId');
        $labelInfo = \Prj\Model\ZyBusiness\SystemLabel::getRecords("labelId,labelNo,labelName",['isUsed'=>'1',"labelType"=>"general","labelId"=>$productLabel]);

//        $phoneInfo = \Sooh2\Misc\Ini::getInstance()->getIni("receiveMsger.alarm");
        $phoneArr = $data['phone'];
        $userInfo = array();
        foreach($phoneArr as $w => $phone){
            $uid = $this->getUserOid($phone);
            if(!empty($uid)) $userInfo[] = $uid;
        }
        foreach($labelInfo as $k => $v){
            $productId = \Prj\Model\ZyBusiness\ProductLabel::getRecords("distinct(productId)",['labelId'=>$v['labelId']]);
            $productId = array_column($productId,'productId');
            //定期
            $ret1 = \Prj\Model\ZyBusiness\ProductInfo::getRecords("(SUM(actualRaiseAmount)/SUM(raiseAmount))>".$data['RegularLimitPercent']." as ret,productType,productId,sum(actualRaiseAmount) as actualRaiseAmount,SUM(raiseAmount) as raiseAmount",['productId'=>$productId,'productStatus'=>['DOING_RAISING','RAISING'],"productType"=>"REGULAR"],'groupby productType');
            //活期
            $ret2 = \Prj\Model\ZyBusiness\ProductInfo::getRecord("(raiseAmount-actualRaiseAmount)<".$data['currentLimitAmount']." as ret,productName,actualRaiseAmount,raiseAmount",['productId'=>$productId,'productStatus'=>['DURATIONING'],"productType"=>"CURRENT"]);
//            $ret = \Prj\Model\ZyBusiness\ProductInfo::getRecords("(SUM(actualRaiseAmount)/SUM(raiseAmount))>0.2 as ret,productType,productId,sum(actualRaiseAmount) as actualRaiseAmount,SUM(raiseAmount) as raiseAmount",['productId'=>$productId,'productStatus'=>['DURATIONING','RAISING']],'groupby productType');

            if($ret2['ret']){
                $msg2 = "警报！警报！警报！当前在售*活期-".$ret2['productName']."总可售份额不足".$data['currentLimitAmount']."，请及时补充库存";
                Loger::out("[Trace#sendProductMsg]".$productId.$msg2);
                Sender::getInstance()->sendCustomMsg("警报产品份额不足短信",$msg2,$userInfo,array('smsnotice'));
            }

            foreach ($ret1 as $r=>$t){
                if($t['ret']){
                    $msg = "警报！警报！警报！当前在售*定期".$v['labelName']."*产品总可售份额不足".$data['RegularLimitPercent'].",请及时上架新产品。";
                    Sender::getInstance()->sendCustomMsg("警报产品份额不足短信",$msg,$userInfo,array('smsnotice'));
                }
            }


        }
//        foreach($labelInfo as $k => $v){
//            $ret = \Prj\Model\Product::getRecord("(sum(remainMoney)/sum(totalRaiseAmount))<0.8 as ret,remainMoney,totalRaiseAmount",['*labels'=>"%,".$v['labelCode'].",%"]);
//
//            if($ret['ret'] == 1){
//                $msg = "警报！警报！警报！当前在售*定期-".$v['labelName']."*产品总可售份额不足(".number_format($ret['remainMoney']/$ret['totalRaiseAmount'])."),请及时上架新产品。";
//
//                Sender::getInstance()->sendCustomMsg("警报产品份额不足短信",$msg,$userInfo,array('smsnotice'));
//            }
//        }
    }


    public function getUserOid($phoneInfo){
        $user = \Prj\Model\User::getCopyByPhone($phoneInfo);
        $user->load();
        if($user->exists()){
            return $user->getField('oid');
        }else{
            return null;
        }
    }
    protected static function defaultData(){
        return [
            'RegularLimitPercent'=>0.2,
            'currentLimitAmount'    =>  50000,
            'beginHour'             =>  7,
            'endHour'               =>  24,
            'phone'                 =>  [
                '13167288208','13585735798','13764806240','13918768896','18621749310','18758365549'
            ]
        ];
    }


    protected function getJsonInfoAction()
    {
        $RAL_PATH = \Sooh2\Misc\Ini::getInstance()->getIni("application.htmlwriter.path");
        $file = $RAL_PATH . "/productMsg.json";
        if(!file_exists($file)){
            touch($file);
            @chmod($file,0777);
        }
        if ($data = file_get_contents($file)) {
            $data = json_decode($data, true);
        } else {
            $data = [];
        }
        return $data;
    }

}