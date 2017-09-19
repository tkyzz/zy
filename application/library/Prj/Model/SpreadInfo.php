<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-14 16:43
 */

namespace Prj\Model;

class SpreadInfo extends _ModelBase
{
    public static function getCopy($id = '')
    {
        return parent::getCopy(['id' => $id]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_spread_info';
    }

    public static function getCopyBySpreadNo($spreadCode)
    {
        return parent::getCopy(['spreadNo' => $spreadCode]);
    }
}