<?php
namespace Prj\RefreshStatus;
/* 
 * 获取状态的任务类的基类
 * \Sooh2\Misc\ViewExt::getInstance()->appendStatusTask('UserBasicInfo') 可以在controller里指定追加一个状态任务
 */

abstract class basic{

    abstract protected function getNodeData($uid);
    protected function getNodeName()
    {
        return array_pop(explode('\\', get_called_class()));
    }
    /**
     * 
     * @param type $viewExt
     */
    public function appendData($viewExt)
    {
        $viewExt->_callForAddStatusData($this->getNodeName(), $this->getNodeData($this->getUidInSession()));
    }
    /**
     * 获取当前用户的id
     * @return type
     */
    protected function getUidInSession()
    {
        return \Prj\Session::getInstance()->getUid();
    }
}
