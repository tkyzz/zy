<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/16
 * Time: 9:36
 */
namespace Prj\Model\ZyBusiness;

class ProductRate extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'ZyBusiness';
        parent::onInit();
        $this->_tbName = 'tpf_product_rate';
    }
}