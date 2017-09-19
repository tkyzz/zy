<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-05 20:54
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

class InviteCode extends KVObj
{
    public static function getCopy($inviteCOde = '')
    {
        if (empty($inviteCOde)) {
            return parent::getCopy(null);
        } else {
            return parent::getCopy(['inviteCode' => $inviteCOde]);
        }
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_invite_code_0';
    }
}