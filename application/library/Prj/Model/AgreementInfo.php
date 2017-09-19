<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-14 16:44
 */

namespace Prj\Model;

class AgreementInfo extends _ModelBase
{
    public static function getCopy($id = '')
    {
        return parent::getCopy(['id' => $id]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_agreement_info';
    }

    public static function getCopyByAgrNo($maskcode = '')
    {
        return parent::getCopy(['agrNo' => $maskcode]);
    }
}