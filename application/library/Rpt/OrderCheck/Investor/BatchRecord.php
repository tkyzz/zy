<?php
namespace Rpt\OrderCheck\Investor;

/**
 * Description of BatchRecord
 *
 * @author wangning
 */
class BatchRecord extends \Sooh2\DB\Cases\OrdersChk\BatchRecord {
    /**
     * 
     * @param type $batchYmd
     * @return \Rpt\OrderCheck\Investor\BatchRecord
     */
    public static function getCopy($batchYmd) {
        if($batchYmd===null){
            return parent::getCopy(null);
        }else{
            return parent::getCopy(array('batchYmd'=>$batchYmd));
        }
    }
    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'investor_batchs_{i}';//表名的默认模板
    }   
}
