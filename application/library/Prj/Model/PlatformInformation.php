<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-06 18:34
 */

namespace Prj\Model;

class PlatformInformation extends \Prj\Model\_ModelBase
{
    public static function getCopy($oid = null)
    {
        if ($oid == null) {
            return parent::getCopy(null);
        } else {
            return parent::getCopy(['oid' => $oid]);
        }
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 't_platform_information';
    }
}