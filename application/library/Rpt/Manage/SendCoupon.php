<?php

namespace Rpt\Manage;

class SendCoupon extends _ModelBase
{
    const status_wait = 'WAIT';
    const status_pass = 'PASS';

    public static $statusMap = [
        self::status_wait => '待审核',
        self::status_pass => '已审核'
    ];

    protected function onInit()
    {
        $this->className = 'Manager';
        parent::onInit();
        $this->_tbName = 'tb_manage_sendCoupon';//表名的默认模板
    }
}


