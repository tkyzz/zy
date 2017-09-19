<?php


namespace Prj\RefreshStatus;

/**
 * 获取用户信息
 *
 * @author simon.wang
 */
class PlatFormData extends Basic
{

    protected function getNodeData($uid)
    {
        $res = \Prj\Bll\PlatformStatistics::getInstance()->getPlatformData();
        return $res;
    }
}
