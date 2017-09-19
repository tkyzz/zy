<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/10
 * Time: 18:07
 */

namespace Prj\Bll;

class PlatformStatistics extends \Prj\Bll\_BllBase
{
    public function getPlatformData(){
        \Prj\Loger::addPrefix('getPlatformData');
        $ymd = date('Ymd');
        $info = \Prj\Model\PlatformStatistics::getDataByYmd($ymd);
        if(empty($info)){
            \Prj\Loger::out('未查询到今日数据,查询最近一条数据' , LOG_WARNING);
            $info = \Prj\Model\PlatformStatistics::getRecord(null , null , 'rsort ymd');
            if(empty($info))return $this->resultError('未查询到相关数据');
            \Prj\Loger::out('使用了 '.$info['ymd'].' 数据');
        }
        $info['registerNumUnit'] = '人';
        $info['repayedAmountUnit'] = '元';
        $info['repayedOrderNumUnit'] = '笔';
        $info['repayedProductNumUnit'] = '笔';
        $info['totalTradeAmountUnit'] = '万元';
        $info['totalTradeAmount'] = ceil($info['totalTradeAmount']);
        unset($info['id']);
        unset($info['ymd']);
        unset($info['updateTime']);
        unset($info['createTime']);

        return $this->resultOK($info);
    }


    public function getPlatformStatistics(){
        \Prj\Loger::addPrefix('getPlatformData');
        $ymd = date('Y-m-d');
        $info = \Prj\Model\ZyBusiness\PlatformStatistics::getRecord("investAmount as totalTradeAmount,payBackNum+redeemNum as repayedOrderNum",['statisticsDate'=>$ymd],'rsort createTime');
        if(empty($info)){
            \Prj\Loger::out('未查询到今日数据,查询最近一条数据' , LOG_WARNING);
            $info = \Prj\Model\ZyBusiness\PlatformStatistics::getRecord("investAmount as totalTradeAmount,payBackNum+redeemNum as repayedOrderNum" , null , 'rsort createTime');
            if(empty($info))return $this->resultError('未查询到相关数据');
            \Prj\Loger::out('使用了 '.$info['ymd'].' 数据');
        }
        $info['registerNumUnit'] = '人';
        $info['repayedAmountUnit'] = '元';
        $info['repayedOrderNumUnit'] = '笔';
        $info['repayedProductNumUnit'] = '笔';
        $info['totalTradeAmountUnit'] = '万元';
        $info['totalTradeAmount'] = sprintf("%.2f",$info['totalTradeAmount']/10000);
        return $this->resultOK($info);
    }
}