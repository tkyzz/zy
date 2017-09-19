<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-10 16:29
 */

namespace Prj\Model;

class UcUserFinal extends _ModelBase
{
    public static function getCopy($userId = '')
    {
        return parent::getCopy(['userId' => $userId]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_user_final';
    }
}