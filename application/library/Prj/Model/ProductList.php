<?php


namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * @package Prj\Model
 */
class ProductList extends _ModelBase
{
    protected function onInit(){
        $this->className = 'ProductList';
        parent::onInit();
        $this->_tbName = 'jz_product_list_online';
    }


}