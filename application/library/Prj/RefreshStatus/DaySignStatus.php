<?php
namespace Prj\RefreshStatus;

/**
 * 日签到的状态信息
 *
 * @author simon.wang
 */
class DaySignStatus extends Basic{
    public function appendData($tool)
    {
        $tool->_callForAddStatusData('DaySignStatus', array('todaySigned'=>0,'numSignedThisLoop'=>1));
    }
}
