<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-18 09:58
 */

namespace Prj\Framework;

class WechatCtrl extends Ctrl
{
    /**
     * @var \EasyWeChat\Foundation\Application
     */
    protected $app;

    protected $options;
    protected $cache;

    public function initBySooh($request, $view)
    {
        parent::initBySooh($request, $view);
    }

    public function initWechat()
    {
        //引入composer
        \Sooh2\Misc\Loger::getInstance()->app_trace(111111111);
        require APP_PATH . '/vendor/autoload.php';
        \Sooh2\Misc\Loger::getInstance()->app_trace(2222222);

        $this->options = \Sooh2\Misc\Ini::getInstance()->getIni('Wechat');
        if ($callbackUrl = $this->_request->get('callbackUrl')) {
            $this->options['oauth']['callback'] = $callbackUrl;
        }
        $this->cache = \Prj\Redis\Wechat::getInstance();
        $this->app = new \EasyWeChat\Foundation\Application($this->options);
        $this->app->cache = $this->cache;
    }
}