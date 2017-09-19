<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/23
 * Time: 13:50
 */
namespace Prj\Bll;
class ShengCaiWang extends \Prj\Bll\_BllBase
{
    public function getChannelInfo($code){
        $params = ['code'=>$code];
        $channelInfo = \Prj\Model\Manager\SpreadChannel::getRecord("*",$params);
        return $channelInfo;
    }


    public function getUserInfo($params){
        $userInfo = \Prj\Model\User::getRecords("oid",$params,'rsort createTime');
        return $userInfo;
    }


    public function getInvestInfo($fr,$startTime,$endTime){
        $channeInfo = $this->getChannelInfo($fr);
        $params = [
            '*channelid'    =>  $channeInfo['no']."%"
        ];
        $userInfo = $this->getUserInfo($params);
        $userInfo = array_column($userInfo,'oid');
        if(empty($userInfo)) return [];
        $investParams = [
            'userId'    =>  $userInfo,
            ']confirmedTime'    =>  date("Y-m-d H:i:s",strtotime($startTime)),
            '[confirmedTime'    =>  date("Y-m-d H:i:s",strtotime($endTime)),
            'orderType' =>  "INVEST",
            "orderStatus"   =>  "CONFIRMED"
        ];
        $list = \Prj\Model\ZyBusiness\TradOrder::getRecords("*",$investParams,'rsort confirmedTime');
        $data = [];
        foreach ($list as $k=>$v){
            $data[$k] = [
                'rbuid' =>  $v['userId'],
                'pid'   =>  $v['productId'],
                'time'  =>  $v['confirmedTime'],
                'money' =>  floatval($v['payAmount'])
            ];
        }
        return $data;

    }




    public function getShengCaiWangList($params){
        $data = [];
        $productUrl = \Sooh2\Misc\Ini::getInstance()->getIni("Urls.shencaiwangProductUrl");
        $list = ProductInfo::getRecords("*",$params);
        foreach($list as $k => $v){
            $data[$k] = [
                'pid'   =>  $v['productId'],
                'pname' =>  $v['productName'],
                'state' =>  ($v['productStatus']=='RAISING'||$v['productStatus'] == 'STARTUP')?1:-1,
                'desc'  =>  "",
                'from'  =>  "zylc",
                'rate'  =>  $v['baseRate']."%",
                'ratebyyear_section'    =>  $v['rewardRate']."%",
                'cycle' =>  $v['raisePeriodDays'],
                'p_sum' =>  $v['raiseAmount'],
                'url'   =>  $productUrl.$v['productId'],
                'url_h5'    =>  '',
                'start_time'    =>  date('Y-m-d',strtotime($v['actualRaisOnTime'])),
                'end_time'  =>  $v['expectDurationEndDate'],
                'paytype'   =>  (!empty($v['payBackType'])&&($v['payBackType']=='PAYONECE'))?"到期还本付息":"",
                'minmoney'  =>  $v['minSingleInvestAmount'],
                'guarantee' =>  "",
                "amountleft"    =>  sprintf("%.2f",($v['raiseAmount']-$v['actualRaiseAmount'])),
                'interest_time' =>  "满标计息"
            ];

        }
        return empty($list)?[]:$data;
    }
}