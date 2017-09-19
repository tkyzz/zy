<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-28 15:05
 */

namespace Prj\Bll\Channel;

class Laizhuan extends Base
{
    public function callback($url)
    {
        return false;
    }

    public function notice($channelName, $idfa, $appid, $args)
    {
        $ret = parent::notice($channelName, $idfa, $appid, $args);

        return $ret;
    }
}