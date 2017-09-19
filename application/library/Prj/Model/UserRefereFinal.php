<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-10 15:37
 */

namespace Prj\Model;

class UserRefereFinal extends _ModelBase
{
    public static function getCopy($id = '')
    {
        return parent::getCopy(['id' => $id]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_user_refere_final';
    }
}