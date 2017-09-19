<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-28 14:08
 */

namespace Prj\Model;

class ChannelActivatingTmp extends _ModelBase
{
    public static function getCopy($appid, $contractId, $idfa)
    {
        return parent::getCopy(['appid' => $appid, 'contractId' => $contractId, 'idfa' => $idfa]);
    }
    
    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_channel_activating_tmp_0';
    }

    public static function getCopyByAppidIdfa($appid, $idfa)
    {
        return parent::getCopy(['appid' => $appid, 'idfa' => $idfa]);
    }
}