<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-08 11:31
 */

namespace Prj\Redis;

/**
 * Class SessionId
 * redis中保存用户的sessionId
 * @package Prj\Redis
 * @author lingtima@gmail.com
 */
class SessionId extends Base
{
    const maxExpire = 86400 * 30;//最大有效期，单位秒

    public static function get($uid)
    {
        $key = self::fmtKey($uid);
        $db = self::getDB();
        if ($db->exec([['exists', $key]])) {
            return self::updateContent($db->exec([['get', $key]]));
        } else {
            return false;
        }
    }

    public static function set($uid, $sessionId, $expire = 3600, $platform = 'APP')
    {
        $key = self::fmtKey($uid);
        $db = self::getDB();
        if ($db->exec([['exists', $key]])) {
            $arrContent = self::updateContent($db->exec([['get', $key]]));
            $arrContent = array_merge($arrContent, self::buildContent($sessionId, $platform, $expire));
            $db->exec([['set', $key, json_encode($arrContent)]]);
            $db->exec([['setTimeout', $key, self::maxExpire]]);
            return true;
        } else {
            $db->exec([['set', $key, json_encode(self::buildContent($sessionId, $platform, $expire))]]);
            $db->exec([['setTimeout', $key, self::maxExpire]]);
            return true;
        }
    }

    public static function del($uid)
    {
        $key = self::fmtKey($uid);
        $db = self::getDB();
        return $db->exec([['delete', $key]]);
    }

    protected static function buildContent($sessionId, $platform, $expire)
    {
        return [$sessionId => ['expire' => time() + $expire, 'platform' => $platform]];
    }

    protected static function updateContent($content = [], $new = false)
    {
        $data = [];
        if ($content = json_decode($content, true)) {
            foreach ($content as $k => $v) {
                if ($v['expire'] > time()) {
                    $data[$k] = $v;
                }
            }
            return $data;
        }
        return [];
    }

    protected static function fmtKey($uid)
    {
        return 'php:session:uid:' . $uid;
    }
}