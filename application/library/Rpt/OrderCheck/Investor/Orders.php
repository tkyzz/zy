<?php
namespace Rpt\OrderCheck\Investor;

/**
 * Description of Orders
 *
 * @author wangning
 */
class Orders extends \Sooh2\DB\Cases\OrdersChk\Orders{
    /**
     * 
     * @param type $ignore
     * @return \Rpt\OrderCheck\Investor\Orders
     */
    public static function getCopy($ignore=null) {
        if($ignore===null){
            return parent::getCopy(null);
        }else{
            throw new \ErrorException('orders should not called by orderid');
        }
    }
    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'investor_orders_{i}';//表名的默认模板
    }   
}
