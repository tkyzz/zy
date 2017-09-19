<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-17 15:40
 */

namespace Prj\Model;

class ChannelInfo extends _ModelBase
{
    public static function getCopy($id = '')
    {
        return parent::getCopy(['id' => $id]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_channel_info';
    }

    public static function getByCopartnerSource($channelSource)
    {
        return parent::getCopy(array('channelSource'=>$channelSource));
    }
}