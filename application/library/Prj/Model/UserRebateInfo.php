<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-06 11:20
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

class UserRebateInfo extends KVObj
{
    public static function getCopy($id = null)
    {
        if ($id === null) {
            return parent::getCopy(null);
        } elseif (is_string($id)) {
            return parent::getCopy(['id' => $id]);
        } else {
            return parent::getCopy($id);
        }
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_user_rebate_info';
    }
}