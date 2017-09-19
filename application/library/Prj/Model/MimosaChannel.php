<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * 渠道表
 * Class MimosaChannel
 * @package Prj\Model
 */
class MimosaChannel extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_money_platform_channel';
    }

    public static function getChannel(){
        list($db,$tb)=self::getCopy('')->dbAndTbName();
        $rs=$db->getPair($tb,'oid','channelName');
        return $rs;
    }
}