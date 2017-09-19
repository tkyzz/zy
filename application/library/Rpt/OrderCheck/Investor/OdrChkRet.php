<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Rpt\OrderCheck\Investor;

/**
 * Description of OdrChkRet
 *
 * @author wangning
 */
class OdrChkRet extends \Sooh2\DB\Cases\OrdersChk\UserOrdersRet{
    protected function _log($str)
    {
        echo "QSC：".$str."\n";
    }    
    public function setDBOfOnline($db)
    {
        $this->_db = $db;
    }
    /**
     * 
     * @var \Sooh2\DB\Interfaces\DB 
     */
    protected $_db;
    public function chkUserOrders($dbOfOrders,$batchYmd)
    {
        $bak = $this->_balance;
        $this->_log($this->_uid."'s lastOrderDt=". $this->_dtAfter.' '.date('m-d H:i:s',$this->_dtAfter));
        $this->_log($this->_uid."'s old balance=". json_encode($this->_balance));
        $ret = parent::chkUserOrders($dbOfOrders,$batchYmd);
        $this->_log($this->_uid."'s new balance=". json_encode($this->_balance));
        $this->_log($this->_uid."'s new-lastOrderDt=". $this->_newLastOrderTimestamp.' '.date('m-d H:i:s',$this->_newLastOrderTimestamp));
        $online = $this->_db->getRecord('payment.t_investor_asset_total', 'availableBalance,frozenBalance', array('userId'=>$this->_uid));
        if($this->_balance['balance']!=round($online['availableBalance']*100)){
            $this->_errorCommon[]='可用余额不一致（线上数据库'.sprintf('%.2f',$online['availableBalance'])." 计算结果=".sprintf('%.2f',$this->_balance['balance']/100)."={$bak['balance']}+".($this->_balance['balance']-$bak['balance'])."）";
        }
        if($this->_balance['frose']!=round($online['frozenBalance']*100)){
            $this->_errorCommon[]="冻结金额不一致（线上数据库".sprintf('%.2f',$online['frozenBalance'])." 计算结果=".sprintf('%.2f',$this->_balance['frose']/100)."={$bak['frose']}+".($this->_balance['frose']-$bak['frose'])."）";
        }
        return $ret;
    }
    protected function getAccMirrorByUid()
    {
        $accMirror = AccountMirror::getCopy($this->_uid);
        $accMirror->load();
        return $accMirror;
    }
    protected function getAccLastokByUid()
    {
        $accMirror = AccountLastOk::getCopy($this->_uid);
        $accMirror->load();
        return $accMirror;
    }
    protected function chkUserOrders_balanceChg($rs)
    {
        $this->_log('order-summary='.json_encode($rs));
        //echo "QSC：".$this->_uid."'s orders summary=". var_export($rs,true)."\n";
        $change = array();

        foreach($rs as $r){
            //var_dump($r);
            if($r['dt']>$this->_newLastOrderTimestamp){
                $this->_newLastOrderTimestamp = $r['dt'];
            }
            if($r['orderStatus']== \Sooh2\DB\Cases\OrdersChk\OrderStatus::frose){//进入冻结状态的话
//                if($r['payAmount']>0){
//                    $change['balance']-=$r['payAmount'];
//                    $change['frose']+=$r['payAmount'];
//                }else{
                    $change['balance']-=$r['payAmount'];
                    $change['frose']+=$r['orderAmount'];
                    //超级户里要贴抵扣券
//                }
            }elseif($r['prestatus']== \Sooh2\DB\Cases\OrdersChk\OrderStatus::frose){//离开冻结状态
                if($r['orderStatus']== \Sooh2\DB\Cases\OrdersChk\OrderStatus::success){//钱花掉了
                    if($r['payAmount']>0){
                        $change['frose']-=$r['payAmount'];
                    }else{
                        $change['frose']-=$r['orderAmount'];
                    }
                    switch(strtolower($r['orderType'])){
                        case 'buyc':$change['holdC']+=$r['orderAmount'];break;//购买活期（确认）
                        case 'buyt':$change['holdT']-=$r['orderAmount'];break;//购买定期（确认）
                    }
                }else{//钱要退回
                    if($r['payAmount']>0){
                        $change['balance']+=$r['payAmount'];
                        $change['frose']-=$r['payAmount'];
                    }else{
                        $change['balance']+=$r['orderAmount'];
                        $change['frose']-=$r['orderAmount'];
                    }
                }
            }else{//不经过冻结状态(或已经经过冻结状态)的情况
                switch(strtolower($r['orderType'])){
                    case 'buyc':$change['balance']-=$r['payAmount'];$change['holdC']+=$r['orderAmount'];break;//购买（确认）活期
                    case 'buyt':$change['balance']-=$r['payAmount'];break;//购买（确认）定期
                    case 'unbuy':$change['balance']+=$r['orderAmount']-$r['feeAmount'];$change['holdC']-=$r['orderAmount'];break;//赎回
                    case 'rebuy':$change['holdC']+=$r['orderAmount']-$r['feeAmount'];break;//活期派息复投
                    case 'interest':$change['balance']+=$r['orderAmount'];break;//定期回款
                    case 'withdraw':$change['balance']-=$r['orderAmount'];break;//提现
                    case 'recharge':$change['balance']+=$r['orderAmount'];break;//充值
                }
            }

        }
        $this->_log('change='.json_encode($change));
        return $change;
    }
}
