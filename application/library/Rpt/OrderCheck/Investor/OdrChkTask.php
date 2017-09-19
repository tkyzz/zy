<?php
namespace Rpt\OrderCheck\Investor;

/**
 * Description of OrdersCheck
 *
 * @author wangning
 */
class OdrChkTask extends OrderImport{

    protected function _logStatus()
    {
        $batchRecord = \Sooh2\DB\Cases\OrdersChk\BatchRecord::getCopy($this->batchYmd);
        $batchRecord->load();
        $lastConfirmedBatch = \Sooh2\DB\Cases\OrdersChk\BatchRecord::getLastConfirmedBatch();
        $lastConfirmedBatch->load();
        $this->_log('最后确认批次的状态：'.json_encode($lastConfirmedBatch->dump()));
        $this->_log('当前批次的状态：'.json_encode($batchRecord->dump()));
    }
    /**
     * 开发调试时使用的：删库，重建表，返回需要指定查询的用户
     * @return type
     */
    public function debugDatabase()
    {
//        $db = \Sooh2\DB::getDB(\Sooh2\Misc\Ini::getInstance()->getIni('DB.slave'));
//        $db->exec(array('drop database if exists db_orderscheck','create database db_orderscheck'));
//        AccountMirror::install();
//        AccountLastOk::install();
//        BatchRecord::install();
//        Orders::install();
        return array();
    }


    public function step_prepare($ymd = null) {
        $this->_odrChkRet =  new OdrChkRet;
        $this->_orders = Orders::getCopy();
        $this->_accMirror = AccountMirror::getCopy(null);
        $this->_accConfirmed = AccountLastOk::getCopy(null);
        parent::step_prepare($ymd);
        $this->_dbSlave = \Sooh2\DB::getDB(\Sooh2\Misc\Ini::getInstance()->getIni('DB.slave'));
        $this->_odrChkRet->setDBOfOnline($this->_dbSlave);
    }
    protected function step_prepare_batch($batchYmd)
    {
        $this->_batchCur = BatchRecord::getCopy($batchYmd);
        $this->_batchCur->load();
        $this->_batchLastConfirmed = BatchRecord::getLastConfirmedBatch();
    }
    /**
     * 遍历所有的订单完成批次入库
     */
    public function step_importOrders()
    {
        $dtFrom = $this->dtBatchFrom;
        $this->getOrderFromBussiness_buy($dtFrom);
        $this->getOrderFromBussiness_extra($dtFrom);
        $this->getOrderFromBussiness_interest($dtFrom);
        $this->getOrderFromPayment_buy($dtFrom);
        $this->getOrderFromPayment_extra($dtFrom);
        $this->getOrderFromPayment_interest($dtFrom);
        $this->getOrderFromPayment_recharge_withdraw($dtFrom);
       
    }
    /**
     * 找出发现错误的用户的订单,重新比对一次
     * （原则上从30开始，当天可以跑无数次）
     */
    public function step_recheckUsers($us)
    {
        $this->_log( "################## 对过一轮后，发现".sizeof($us).'个用户有错误（错误用户会再查一轮）,换成一个测试用户');
        $super = array_search('SUPER95b4e8ce701008593e7e015', $us);
        if($super!==false){
            unset($us[$super]);
        }
        $dtFrom = $this->dtBatchFrom;
        $this->getOrderFromBussiness_buy($dtFrom,$us);
        $this->getOrderFromBussiness_extra($dtFrom,$us);
        $this->getOrderFromBussiness_interest($dtFrom,$us);
        $this->getOrderFromPayment_buy($dtFrom,$us);
        $this->getOrderFromPayment_extra($dtFrom,$us);
        $this->getOrderFromPayment_interest($dtFrom,$us);
        $this->getOrderFromPayment_recharge_withdraw($dtFrom,$us);
        
        foreach($us as $uid){
            $this->_orders->recheck($uid,$this->_odrChkRet);
        }
    }

    
}
