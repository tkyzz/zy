<?php
namespace Prj\RefreshStatus;

/**
 * 服务器时间
 *
 * @author simon.wang
 */
class ServerTime  extends Basic{
    
    protected function getNodeData($uid)
    {
        return time() . '000';
    }
}
