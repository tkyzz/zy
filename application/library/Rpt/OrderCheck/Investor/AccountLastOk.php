<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Rpt\OrderCheck\Investor;

/**
 * Description of AccountLastOk
 *
 * @author wangning
 */
class AccountLastOk extends \Sooh2\DB\Cases\OrdersChk\AccountLastOk{
    /**
     * 
     * @param type $uid
     * @return \Rpt\OrderCheck\Investor\AccountLastOk
     */
    public static function getCopy($uid) {
        if($uid===null){
            return parent::getCopy(null);
        }else{
            return parent::getCopy(array('uid'=>$uid));
        }
    }
    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'invester_accsuccess_{i}';//表名的默认模板
    }
}
