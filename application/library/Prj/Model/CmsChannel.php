<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 * 渠道表
 * Class CmsChannel
 * @package Prj\Model
 */
class CmsChannel extends _ModelBase
{
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 't_platform_channel';
    }

    public static function getChannel(){
        list($db,$tb)=self::getCopy('')->dbAndTbName();
        $rs=$db->getPair($tb,'oid','name');
        return $rs;
    }
}