<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 16:09
 */

namespace Prj\Model\ZyBusiness;

class ProductInfo extends \Prj\Model\_ModelBase
{
    public static $type_ding = 'REGULAR'; //定期
    public static $type_huo = 'CURRENT'; //活期

    protected function onInit(){
        $this->className = 'ZyBusiness';
        parent::onInit();
        $this->_tbName = 'tpf_product_info';
    }

}