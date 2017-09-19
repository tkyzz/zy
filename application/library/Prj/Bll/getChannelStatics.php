<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/24
 * Time: 15:57
 */
namespace Prj\Bll;
use Prj\Loger;
use Prj\Model\ChannelFinal;
use Prj\Model\ContractInfo;
use Prj\Model\Manager\SpreadChannel;
use Prj\Model\ZyBusiness\TradOrder;

class getChannelStatics extends _BllBase
{
    public function crond(){
        \Prj\Loger::$prefix = '[Crond-getChannelStatics]';
        $hour = date('H');
        if($hour == 1){
            $this->getChannelStaticsInfo();
        }
    }

    protected function getChannelStaticsInfo(){
        Loger::$prefix = "[getChannelStatics]";
        $today = date("Ymd");

        $contractTbName= \Prj\Model\ContractInfo::getTbname();
        $userTbName = \Prj\Model\User::getTbname();
        $sql = "select * from ".$contractTbName." a where `status`=0 and EXISTS (select 1 from ".$userTbName." b where b.channelid=REPLACE(a.contractCode,a.contractYmd,DATE_FORMAT(NOW(),'%Y%m%d')))";
        $contractInfo = \Prj\Model\ContractInfo::query($sql);

        $currentProductInfo = \Prj\Model\ZyBusiness\ProductInfo::getRecords("productId",['productType'=>"CURRENT"]);
        $currentProductId = array_column($currentProductInfo,"productId");
        $regularProductInfo = \Prj\Model\ZyBusiness\ProductInfo::getRecords("productId",['productType'=>"REGULAR"]);
        $regularProductId = array_column($regularProductInfo,"productId");
        try {

            $tradeOrderTable = \Prj\Model\ZyBusiness\TradOrder::getTbname();
            foreach ($contractInfo as $k => $v) {
                $data = [];
                $contractId = substr_replace($v['contractCode'], $today, 4, 8);
                $regInfo = \Prj\Model\User::getRecords("oid", ['channelid' => $contractId]);
                if(empty($regInfo)) continue;
                $data['newRegNum'] = count($regInfo);  //注册人数
                $oidArr = array_column($regInfo, 'oid');
                $data['newBindNum'] = \Prj\Model\Payment\BankBind::getCount(['userId' => $oidArr, 'type' => "BIND", 'DATE_FORMAT(createTime,"%Y%m%d")' => $today]);    //新增绑卡人数

                $investInfo = \Prj\Model\ZyBusiness\TradOrder::getRecord("sum(orderAmount) as orderAmount,count(distinct(userId)) as num", ['userId' => $oidArr, 'orderStatus' => "CONFIRMED", 'orderType' => "INVEST", 'DATE_FORMAT(createTime,"%Y%m%d")' => $today]); //投资人数
                $data['newBoughtAmount'] = $investInfo['orderAmount'] * 100;   //新增投资金额
                $data['newBoughtNum'] = $investInfo['num'];    //投资人数
                $data['boughtAmount'] = (\Prj\Model\ZyBusiness\TradOrder::getRecord("sum(orderAmount) as amount", ['userId' => $oidArr, 'orderType' => "INVEST", "orderStatus" => "CONFIRMED"])['amount']) * 100;  //投资金额
                $rechargeMoney = \Prj\Model\Payment\BankOrder::getRecord("sum(orderAmount) as rechargeMoney", ['userId' => $oidArr, 'orderType' => "RECHARGE", "orderStatus" => "SUCCESS"])['orderAmount'];  //充值金额
                $withDrawMoney = \Prj\Model\Payment\BankOrder::getRecord("sum(orderAmount) as rechargeMoney", ['userId' => $oidArr, 'orderType' => "WITHDRAW", "orderStatus" => "SUCCESS"])['orderAmount'];  //提现金额

                $sql2 = "select sum(orderAmount) as orderAmount from " . $tradeOrderTable . " where orderType='INVEST' and orderStatus='CONFIRMED' GROUP BY userId HAVING MIN(DATE_FORMAT(createTime,'%Y%m%d'))=".$today;
                $regularInvestInfo = \Prj\Model\ZyBusiness\TradOrder::getRecord("count(distinct userId) as count,sum(orderAmount) as amount",['productId' => $regularProductId, 'orderType' => "INVEST", "orderStatus" => "CONFIRMED"]);
                $currentInvestInfo = \Prj\Model\ZyBusiness\TradOrder::getRecord("count(distinct userId) as count,sum(orderAmount) as amount",['productId' => $currentProductId, 'orderType' => "INVEST", "orderStatus" => "CONFIRMED"]);
                $data['stock'] = ($rechargeMoney - $withDrawMoney) * 100;   //存量
                $traderAmount = \Prj\Model\ZyBusiness\TradOrder::query($sql2);
                $data['newBoughtAmount'] = array_sum($traderAmount) * 100;
                $data['newBoughtNum'] = count($traderAmount);
                $data['pro1BoughtNum'] = $regularInvestInfo['count'];
                $data['pro1BoughtAmount'] = $regularInvestInfo['amount']*100;
                $data['pro2BoughtNum'] = $currentInvestInfo['count'];
                $data['pro2BoughtAmount'] = $currentInvestInfo['amount']*100;
                $data['ymd'] = $today;
                $data['week'] = date('w');
                $data['channelId'] = $contractId;
                $data['agreementId'] = substr($contractId,13);
                $data['spreadId'] = substr($contractId,12,1);
                $count = \Prj\Model\ChannelFinal::getCount(['ymd'=>$today,'channelId'=>$contractId]);
                if(!$count){
                    $ret = \Prj\Model\ChannelFinal::saveOne($data);
                    if($ret === true){
                        Loger::out("getChannelStatics--插入".$v['contractCode']."--失败,ret".json_encode($data));
                    }else{
                        Loger::out("getChannelStatics--插入".$v['contractCode']."--成功,ret".json_encode($data));
                    }

                }else{
                    $ret = \Prj\Model\ChannelFinal::updateOne($data,['ymd'=>$today,'channelId'=>$contractId]);
                    if($ret === true){
                        Loger::out("getChannelStatics--更新".$v['contractCode']."--失败,ret".json_encode($data));
                    }else{
                        Loger::out("getChannelStatics--更新".$v['contractCode']."--成功,ret".json_encode($data));
                    }

                }

            }


            Loger::out("转存成功!");

        }catch (\Exception $ex){
            Loger::out("获取contractInfo失败,".$ex->getMessage());
        }

    }



}