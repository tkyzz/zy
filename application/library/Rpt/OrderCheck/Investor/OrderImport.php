<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Rpt\OrderCheck\Investor;

/**
 * 导入相关
 *
 * @author wangning
 */
class OrderImport extends \Sooh2\DB\Cases\OrdersChk\Task{
    protected $_prdts=null;

    /**
     *
     * @var \Sooh2\DB\Interfaces\DB;
     */
    protected $_dbSlave;    
    protected function _log($str)
    {
        echo "QSC：".$str."\n";
    }
    protected function getProductName($prdtId)
    {
        if(empty($prdtId)){
            return '【无id】';
        }
        if(!isset($this->_prdts[$prdtId])){
            $this->_prdts[$prdtId]=$this->_dbSlave->getRecord('business.tpf_product_info', 'productName,productType',array('productId'=>$prdtId));//productNo
        }
        return $this->_prdts[$prdtId]['productName'];
    }
    protected function isCurrencyProduct($prdtId)
    {
        if(empty($prdtId)){
            return false;
        }
        if(!isset($this->_prdts[$prdtId])){
            $this->_prdts[$prdtId]=$this->_dbSlave->getRecord('business.tpf_product_info', 'productName,productType',array('productId'=>$prdtId));//productNo
        }
        return $this->_prdts[$prdtId]['productType']=='CURRENT';
    }

    /**
     * 冲提： RECHARGE充值; WITHDRAW提现
     * @param type $dtFrom
     */
    protected function getOrderFromPayment_recharge_withdraw($dtFrom,$uid=null)
    {
        $tb0 = 'payment.t_bank_order';
        $where = array(']updateTime'=>date('Y-m-d H:i:s',$dtFrom));
        if($uid!==null){
            $where['userId']=$uid;
        }
        $num = $this->_dbSlave->getRecordCount($tb0, $where);
        $this->_log(__FUNCTION__.' trace record-count='.$num.' by '.$this->_dbSlave->lastCmd());
        if($num==0){
            return;
        }
        $pager = new \Sooh2\DB\Pager(100);
        $pager->init($num, -1);
        for($i=1;$i<=$pager->page_count;$i++){
            $pager->init($num, $i);
            $rs = $this->_dbSlave->getRecords($tb0, 'userId,orderNo,orderType,orderStatus,orderAmount,fee,updateTime',$where,null,$pager->page_size,$pager->rsFrom());
            foreach($rs as $r){
                switch($r['orderStatus']){
                    case 'INIT'://已申请
                    case 'APPLIED'://已申请
                        if($r['orderType']=='RECHARGE'){
                            $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::prepare;
                        }else{
                            $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::frose;
                        }
                        break;
                    case 'PROCESSING'://处理中
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::prepare;
                        break;
                    case 'REJECTED':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::refused;
                        break;
                    case 'FAILED':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::failed;
                        break;
                    case 'SUCCESS':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::success;
                        break;
                    default :
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::unknown;
                        break;
                }
                $intro = $r['orderType']=='RECHARGE'?"充值":"提现";
                $amount = round($r['orderAmount']*100);
                $this->_orders->ensureOrderA($r['userId'],$r['orderNo'], $r['orderType'], strtotime($r['updateTime']), $orderStatus,$intro, $amount, $amount, round($r['fee']*100));
                $this->_orders->ensureOrderB($r['userId'],$r['orderNo'], $r['orderType'], strtotime($r['updateTime']), $orderStatus,$intro, $amount, $amount, round($r['fee']*100));
            }
        }
    }
    /**
     * 申赎： buy 申购  unbuy 赎回
     * @param type $dtFrom
     */
    protected function getOrderFromPayment_buy($dtFrom,$uid=null)
    {
        $tb0 = 'payment.t_trade_order';
        $where = array(']updateTime'=>date('Y-m-d H:i:s',$dtFrom));
        if($uid!==null){
            $where['userId']=$uid;
        }
        $num = $this->_dbSlave->getRecordCount($tb0, $where);
        $this->_log(__FUNCTION__.' trace record-count='.$num.' by '.$this->_dbSlave->lastCmd());
        if($num==0){
            return;
        }
        $pager = new \Sooh2\DB\Pager(100);
        $pager->init($num, -1);
        for($i=1;$i<=$pager->page_count;$i++){
            $pager->init($num, $i);
            $rs = $this->_dbSlave->getRecords($tb0, 'userId,orderNo,orderType,status,productId,orderAmount as payAmount,voucher,fee,voucherType,updateTime',$where,null,$pager->page_size,$pager->rsFrom());
            foreach($rs as $r){
                switch($r['status']){
                    case 'INIT'://已申请
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::prepare;
                        break;
                    case 'APPLY'://处理中
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::frose;
                        break;
                    case 'REVOKE':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::refused;
                        break;
                    case 'FAILED':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::failed;
                        break;
                    case 'SUCCESS':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::success;
                        break;
                    default :
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::unknown;
                        break;
                }
                if($r['orderType']=='APPLY'){
                    if($this->isCurrencyProduct($r['productId'])){
                        $orderType="buyc";
                    }else{
                        $orderType="buyt";
                    }
                    $intro = '申购'.$this->getProductName($r['productId']);
                    if($r['voucherType']=='RATECOUPON'){//申购时，加息券不参与计算
                        $r['voucher']=0;
                    }
                    $r['orderAmount']=$r['payAmount']+$r['voucher'];
                }else{
                    $orderType="unbuy";
                    $intro = '赎回'.$this->getProductName($r['productId']);
                    $r['orderAmount']=$r['payAmount']+$r['fee'];

                }
                
                $this->_orders->ensureOrderA($r['userId'],$r['orderNo'], $orderType, strtotime($r['updateTime']), $orderStatus,$intro, round($r['orderAmount']*100), round($r['payAmount']*100), round($r['fee']*100));
            }
        }
    }
    /**
     * 利息 interest
     * @param type $dtFrom
     */
    protected function getOrderFromPayment_interest($dtFrom,$uid=null)
    {
        $tb0 = 'payment.t_dividend_order';
        $where = array(']updateTime'=>date('Y-m-d H:i:s',$dtFrom));
        if($uid!==null){
            $where['userId']=$uid;
        }
        $num = $this->_dbSlave->getRecordCount($tb0, $where);
        $this->_log(__FUNCTION__.' trace record-count='.$num.' by '.$this->_dbSlave->lastCmd());
        if($num==0){
            return;
        }
        $pager = new \Sooh2\DB\Pager(100);
        $pager->init($num, -1);
        for($i=1;$i<=$pager->page_count;$i++){
            $pager->init($num, $i);
            $rs = $this->_dbSlave->getRecords($tb0, 'userId,orderNo,orderType,status,productId,updateTime,'
                    .'(capital+baseInterest+rewardInterest+raisePeriodInterest+setupInterest+addInterest) as orderAmount',$where,null,$pager->page_size,$pager->rsFrom());
            foreach($rs as $r){
                switch($r['status']){
                    case 'FAILED':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::failed;
                        break;
                    case 'SUCCESS':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::success;
                        break;
                    default :
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::unknown;
                        break;
                }
                if($this->isCurrencyProduct($r['productId'])){
                    $orderType="rebuy";
                    $intro = '活期派息'.$this->getProductName($r['productId']);
                }else{
                    $orderType="interest";
                    $intro = '定期回款'.$this->getProductName($r['productId']);
                }
                $amount = round($r['orderAmount']*100);

                $this->_orders->ensureOrderA($r['userId'],$r['orderNo'], $orderType, strtotime($r['updateTime']), $orderStatus,$intro, $amount, $amount,0);
            }
        }
    }
    /**
     * 其他 
     * @param type $dtFrom
     */
    protected function getOrderFromPayment_extra($dtFrom,$uid=null)
    {
        $tb0 = 'payment.t_redenvelope_order';
        $where = array(']updateTime'=>date('Y-m-d H:i:s',$dtFrom));
        if($uid!==null){
            $where['userId']=$uid;
        }
        $num = $this->_dbSlave->getRecordCount($tb0, $where);
        $this->_log(__FUNCTION__.' trace record-count='.$num.' by '.$this->_dbSlave->lastCmd());
        if($num==0){
            return;
        }
        $pager = new \Sooh2\DB\Pager(100);
        $pager->init($num, -1);
        for($i=1;$i<=$pager->page_count;$i++){
            $pager->init($num, $i);
            $rs = $this->_dbSlave->getRecords($tb0, 'userId,ucId,status,orderAmount,updateTime',$where,null,$pager->page_size,$pager->rsFrom());
            foreach($rs as $r){
                switch($r['status']){
                    case 'FAILED':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::failed;
                        break;
                    case 'SUCCESS':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::success;
                        break;
                    default :
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::unknown;
                        break;
                }
                $orderType="RECHARGE";
                $intro = '红包';

                $this->_orders->ensureOrderA($r['userId'],'RED#'.$r['ucId'], $orderType, strtotime($r['updateTime']), $orderStatus,$intro, round($r['orderAmount']*100), 0,0);
            }
        }
    }
    
    /**
     *  buy 申购  unbuy 赎回
     * @param type $dtFrom
     */
    protected function getOrderFromBussiness_buy($dtFrom,$uid=null)
    {
        $tb0 = 'business.tpf_investor_trade_order';
        $where = array(']updateTime'=>date('Y-m-d H:i:s',$dtFrom));
        if($uid!==null){
            $where['userId']=$uid;
        }
        $num = $this->_dbSlave->getRecordCount($tb0, $where);
        $this->_log(__FUNCTION__.' trace record-count='.$num.' by '.$this->_dbSlave->lastCmd());
        if($num==0){
            return ;
        }
        $pager = new \Sooh2\DB\Pager(100);
        $pager->init($num, -1);
        for($i=1;$i<=$pager->page_count;$i++){
            $pager->init($num, $i);
            $rs = $this->_dbSlave->getRecords($tb0, 'userId,orderNo,orderType,productId,orderStatus,holdStatus,orderAmount,payAmount,fee,updateTime',$where,null,$pager->page_size,$pager->rsFrom());
            foreach($rs as $r){
                if($r['holdStatus']=='TOCONFIRM'){
                    if($r['orderStatus']=='CONFIRMED'){
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::frose;
                    }else{
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::failed;
                    }
                }else{
                    
                    switch($r['orderStatus']){
                        case 'SUBMITTED':
                            $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::prepare;
                            break;
                        case 'REFUSED':
                            $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::refused;
                            break;
                        case 'CONFIRMED':
                            $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::success;
                            break;
                        default :
                            $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::unknown;
                            break;
                    }
                }
                if($r['orderType']=='INVEST'){
                    if($this->isCurrencyProduct($r['productId'])){
                        $orderType="buyc";
                    }else{
                        $orderType="buyt";
                    }
                    $intro = '申购'.$this->getProductName($r['productId']);
                }elseif($r['orderType']=='REDEEM'){//CASH
                    $orderType="unbuy";
                    $intro = '赎回'.$this->getProductName($r['productId']);
                }elseif($r['orderType']=='CASH'){//CASH
                    $orderType="interest";
                    $intro = '回款'.$this->getProductName($r['productId']);
                }
                \Sooh2\Misc\Loger::getInstance()->app_trace("trace-order:".\Sooh2\Util::toJsonSimple($r));
                $this->_orders->ensureOrderB($r['userId'],$r['orderNo'], $orderType, strtotime($r['updateTime']), $orderStatus,$intro, round($r['orderAmount']*100), round($r['payAmount']*100),round($r['fee']*100));
            }
        }
    }
    
    /**
     * 定期 在订单表里已经处理了，这里只处理活期
     * @param type $dtFrom
     */
    protected function getOrderFromBussiness_interest($dtFrom,$uid=null)
    {
        $tb0 = 'business.tpf_user_income_record';
        $where = array(']updateTime'=>date('Y-m-d H:i:s',$dtFrom),'incomeType'=>'CURRENT');
        if($uid!==null){
            $where['userId']=$uid;
        }
        $num = $this->_dbSlave->getRecordCount($tb0, $where);
        $this->_log(__FUNCTION__.' trace record-count='.$num.' by '.$this->_dbSlave->lastCmd());
        if($num==0){
            return;
        }
        $pager = new \Sooh2\DB\Pager(100);
        $pager->init($num, -1);
        for($i=1;$i<=$pager->page_count;$i++){
            $pager->init($num, $i);
            $rs = $this->_dbSlave->getRecords($tb0, 'userId,orderNo,productId,payStatus,payAmount,fee,updateTime',$where,null,$pager->page_size,$pager->rsFrom());
            foreach($rs as $r){
                switch($r['payStatus']){
                    case 'FAIL':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::failed;
                        break;
                    case 'SUCCESS':
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::success;
                        break;
                    default :
                        $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::unknown;
                        break;
                }
                $orderType="rebuy";
                $intro = '活期派息'.$this->getProductName($r['productId']);
                $amount = round($r['payAmount']*100);
                $this->_orders->ensureOrderB($r['userId'],$r['orderNo'], $orderType, strtotime($r['updateTime']), $orderStatus,$intro, $amount, $amount,round($r['fee']*100));
            }
        }
    }
    /**
     * 红包
     * @param type $dtFrom
     */
    protected function getOrderFromBussiness_extra($dtFrom,$uid=null)
    {
        $tb0 = 'business.tpf_user_coupon';
        $where = array(']updateTime'=>date('Y-m-d H:i:s',$dtFrom),'couponType'=>'REDPACKETS','couponStatus'=>'USED');
        if($uid!==null){
            $where['userId']=$uid;
        }
        $num = $this->_dbSlave->getRecordCount($tb0, $where);
        $this->_log(__FUNCTION__.' trace record-count='.$num.' by '.$this->_dbSlave->lastCmd());
        if($num==0){
            return;
        }
        $pager = new \Sooh2\DB\Pager(100);
        $pager->init($num, -1);
        for($i=1;$i<=$pager->page_count;$i++){
            $pager->init($num, $i);
            $rs = $this->_dbSlave->getRecords($tb0, 'userId,ucId,name,couponStatus,couponAmount,updateTime',$where,null,$pager->page_size,$pager->rsFrom());
            foreach($rs as $r){
                $orderStatus = \Sooh2\DB\Cases\OrdersChk\OrderStatus::success;
                $orderType="RECHARGE";
                $intro = '红包';

                $this->_orders->ensureOrderB($r['userId'],'RED#'.$r['ucId'], $orderType, strtotime($r['updateTime']), $orderStatus,$intro, round($r['couponAmount']*100), 0,0);
            }
        }
    }
}
