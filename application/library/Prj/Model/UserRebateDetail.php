<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-06 11:21
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

class UserRebateDetail extends \Prj\Model\_ModelBase
{
    public static function getCopy($id = null)
    {
        if ($id === null) {
            return parent::getCopy(null);
        } else {
            return parent::getCopy(['id' => $id]);
        }
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_user_rebate_detail';
    }
}