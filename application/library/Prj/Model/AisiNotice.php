<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-13 14:03
 */

namespace Prj\Model;

class AisiNotice extends _ModelBase
{
    public static function getCopy($idfa = '')
    {
        return parent::getCopy(['idfa' => $idfa]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_aisi_notice_0';
    }
}