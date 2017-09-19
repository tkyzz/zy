<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-20 15:55
 */

namespace Prj\Model;

class DeviceContractid extends _ModelBase
{
    public static function getCopy($deviceType = '', $deviceId = '', $contractId = '')
    {
        return parent::getCopy(['deviceType' => $deviceType, 'deviceId' => $deviceId, 'contractId' => $contractId]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_device_contractid_0';
    }
}