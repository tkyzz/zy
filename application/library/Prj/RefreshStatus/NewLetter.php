<?php
namespace Prj\RefreshStatus;

/**
 * 获取未读的站内信数量
 * @author simon.wang
 */
class NewLetter extends Basic{
    
    protected function getNodeData($uid)
    {
        if(empty($uid)){
            return 0;
        }else{
            return \Prj\Model\Letter::getNumOfUnread($uid);
        }
    }
}
