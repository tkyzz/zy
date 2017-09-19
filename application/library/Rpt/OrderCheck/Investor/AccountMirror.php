<?php
namespace Rpt\OrderCheck\Investor;

/**
 * Description of AccountMirror
 *
 * @author wangning
 */
class AccountMirror extends \Sooh2\DB\Cases\OrdersChk\AccountMirror{
    /**
     * 
     * @param type $uid
     * @return \Rpt\OrderCheck\Investor\AccountMirror
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
        $this->_tbName = 'investor_accmirror_{i}';//表名的默认模板
    }
}
