<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/8/25
 * Time: 11:47
 */

namespace Prj\Model\Manager;
use Prj\Model\_ModelBase;

class BasicChannel extends _ModelBase{
    public function onInit()
    {
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = "tb_contract_info";
    }

    public static function basicChannelList(){
        $sql = 'SELECT * FROM '.self::getTbname().' GROUP BY channelId';
        return parent::query($sql);
    }
}