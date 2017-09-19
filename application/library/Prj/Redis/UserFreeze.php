<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-09-07 20:07
 */

namespace Prj\Redis;

class UserFreeze extends Base
{
    public static function freeze($id)
    {
        self::contains($id) OR self::getDB()->exec([['set', self::fmtKey($id), time()]]);
        return true;
    }

    public static function unfreeze($id)
    {
        self::contains($id) AND self::getDB()->exec([['delete', self::fmtKey($id)]]);
        return true;
    }

    public static function contains($id)
    {
        if (self::getDB()->exec([['exists', self::fmtKey($id)]])) {
            return true;
        }
        return false;
    }

    protected static function fmtKey($id)
    {
        return "c:g:u:freeze:$id";
    }
}