<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-18 13:58
 */

namespace Prj\Model;

class ChannelNotice extends _ModelBase
{
    public static function getCopy($id = '')
    {
        return parent::getCopy(['id' => $id]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_channel_notice_0';
    }
//
//    public static function getCopyByChannelAndIdfa($channelName, $idfa)
//    {
//        return parent::getCopy(['channelName' => $channelName, 'idfa' => $idfa]);
//    }

    public static function getCopyByAppidAndIdfa($appid, $idfa)
    {
        return parent::getCopy(['appid' => $appid, 'idfa' => $idfa]);
    }

    public static function getCopyByChannelAppidIdfa($channelName, $appid, $idfa)
    {
        return parent::getCopy(['channelName' => $channelName, 'appid' => $appid, 'idfa' => $idfa]);
    }
}