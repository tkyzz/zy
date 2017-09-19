<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Rpt\Manage;

/**
 * Description of ManageMenu
 *
 * @author simon.wang
 */
class ManageMenu extends \Rpt\KVObjBase{
    protected function onInit()
    {
        $this->className = 'ManageMenu';
        parent::onInit();
        $this->field_locker=null;//  悲观锁用的字段名，默认使用'rowLock'，设置为null表明不需要悲观锁
        $this->_tbName = 'tb_manage_menu';//表名的默认模板

    }
}
