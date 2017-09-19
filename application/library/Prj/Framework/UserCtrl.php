<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-04 19:06
 */

namespace Prj\Framework;

class UserCtrl extends Ctrl
{
    public $userId;
    public function initBySooh($request, $view)
    {
        parent::initBySooh($request, $view);
        $this->userId = \Prj\Session::getInstance()->getUid();
        if (!$this->userId) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('error_needs_login');
            throw new \ErrorException('未登录或登录信息已经过期!', 10001);
        }
    }
}