<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-05 10:30
 */

namespace Prj\Model;

class PlatformBankcard extends _ModelBase
{
    public static function getCopy($oid = '')
    {
        return parent::getCopy($oid);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 't_platform_bankcard';
    }
}