<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/15
 * Time: 10:06
 */
namespace Prj\Model\TD;

class ClientTransparent extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'User';
        parent::onInit();
        $this->_tbName = 'tb_td_client_transparent_0';
    }
}