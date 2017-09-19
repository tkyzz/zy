<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-18 10:06
 */

namespace Prj\Bll;

class Wechat extends _BllBase
{
    /**
     * 判断是否为微信浏览器
     * @param bool|false $enforcement 是否强制为微信浏览器
     * @return bool
     */
    public function checkWechatBrowser($enforcement = false)
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('=========是微信浏览器');
            return true;
        }

        if ($enforcement) {
            exit('请在微信中打开');
        }
        return false;
    }
}