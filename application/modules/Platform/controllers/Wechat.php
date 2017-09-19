<?php

/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-17 10:18
 */
class WechatController extends \Prj\Framework\WechatCtrl
{
    public function testAction()
    {
        $this->initWechat();
        $server = $this->app->server;
        $server->setMessageHandler(function ($message) {
            return 'hello world！掌悦';
        });
        $resonse = $server->serve();
        $resonse->send();
    }

    public function authtokenAction()
    {
        $this->initWechat();
        $server = $this->app->server;
        $resonse = $server->serve();
        $resonse->send();
    }

    public function webauthAction()
    {
        $this->initWechat();
        $oauth = $this->app->oauth;
        $oauth->redirect()->send();
    }

    public function oauthcallbackAction()
    {
        $this->initWechat();
        $oauth = $this->app->oauth;
        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $openid = $user->getId();
        //TODO 上线后将有效期调整为30天
        setcookie('wechat_openid', $openid, time() + 86400 * 30, '/');
        $_COOKIE['wechat_openid'] = $openid;
        try {
            $ModelWechatUser = \Prj\Model\WechatUser::getCopy($openid);
            $ModelWechatUser->load();
            $userInfo = $user->getOriginal();
            unset($userInfo['openid']);
            unset($userInfo['nickname']);
            if (is_array($userInfo['privilege'])) {
                $userInfo['privilege'] = json_encode($userInfo['privilege']);
            }
            foreach ($userInfo as $k => $v) {
                $ModelWechatUser->setField($k, $v);
            }
            $ModelWechatUser->saveToDB();
        } catch (\Exception $e) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('保存wechat-openid-phone');
            \Sooh2\Misc\Loger::getInstance()->app_trace($e->getMessage());
            \Sooh2\Misc\Loger::getInstance()->app_trace($ModelWechatUser->dbWithTablename()->lastCmd());
        }


        if ($targetUrl = $this->_request->get('targetUrl')) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('targetUrl:' . $targetUrl);
            header('location:'. $targetUrl); // 跳转到 user/profile
            die();
        }
        return $this->assignCodeAndMessage('success');
    }
}