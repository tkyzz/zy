<?php

namespace Prj\RefreshStatus;

/**
 * 服务端认为客户端的ip
 *
 * @author simon.wang
 */
class ClientIP extends Basic{
    
    protected function getNodeData($uid)
    {
        return \Sooh2\Util::remoteIP();
    }
}
