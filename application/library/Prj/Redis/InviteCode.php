<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-25 11:44
 */

namespace Prj\Redis;

class InviteCode extends Base
{
    public static function getNext()
    {
        $db = self::getDB();
        $key = self::fmtKey();

        if ($db->exec([['exists', $key]])) {
            return $db->exec([['incr', $key]]);
        } else {
            if ($sceneId = \Prj\Bll\Invite::getInstance()->getMaxInviteCode()) {
                $db->exec([['set', $key, $sceneId + 1]]);
                return $sceneId + 1;
            }
        }
        return \Prj\Bll\Invite::getInstance()->buildInviteCode();
    }

    protected static function fmtKey()
    {
        return 'c:g:u:sceneid:';
    }
}