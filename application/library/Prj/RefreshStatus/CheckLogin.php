<?php
/**
 * 检查登录状态
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/29
 * Time: 10:08
 */
namespace Prj\RefreshStatus;

class CheckLogin extends Basic
{
    public function getNodeData($uid = null)
    {
        return $uid ? 1 : 0;
    }

}