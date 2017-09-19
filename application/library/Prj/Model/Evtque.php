<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/14
 * Time: 15:21
 */
namespace Prj\Model;

class Evtque extends _ModelBase
{
    protected function onInit()
    {
        $this->className = 'EvtQue';
        parent::onInit();
        $this->field_locker=null;//  悲观锁用的字段名，默认使用'rowLock'，设置为null表明不需要悲观锁
        $this->_tbName = 'tb_evtque_{i}';//表名的默认模板
    }


}