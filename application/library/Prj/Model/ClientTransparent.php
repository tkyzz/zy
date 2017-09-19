<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-13 19:15
 */

namespace Prj\Model;

class ClientTransparent extends _ModelBase
{
    public static function getCopy($id = '')
    {
        return parent::getCopy(['id' => $id]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_client_transparent';
    }
}