<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/24
 * Time: 17:52
 */

namespace Prj\Model;

class Product extends _ModelBase
{
    public function onInit()
    {
        $this->className = "ProductList";
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = "tb_gam_product";
    }
}