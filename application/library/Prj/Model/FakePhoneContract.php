<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-26 14:33
 */

namespace Prj\Model;

class FakePhoneContract extends _ModelBase
{
    public static function getCopy($phone = '')
    {
        return parent::getCopy(['phone' => $phone]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_fake_phone_contract_0';
    }
}