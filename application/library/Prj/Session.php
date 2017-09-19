<?php
namespace Prj;
/**
 * curl -l -H "Content-type: application/json" -X POST -d '{"data":{"dt":"123","sign":"c2ca76b0b43df1f844729d57ceae7de7","evt":"test","args":"args-1"}}' "http://106.14.236.8/platform/api/addevt"
 */

class Session
{
    protected static $_instance = null;
    /**
     * @return \Prj\Session
     */
    public static function getInstance()
    {
        if(self::$_instance ==null){
            self::$_instance = new Session;
        }
        return self::$_instance;
    }
    const sessname4SessServer='SESSION';
    protected function getSessIdInCookie()
    {
        if(isset($_COOKIE['GH-SESSION'])){
            $this->_sessionId = $_COOKIE['GH-SESSION'];
        }else{
            $this->_sessionId = $_COOKIE['SESSION'];
        }
        return $this->_sessionId;
    }
    protected function updSessionIdCookie($newVal,$secondsKeep=0)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace($newVal);
        if($newVal!==null){
            setcookie('SESSION', $newVal, time()+$secondsKeep, '/');
            setcookie('GH-SESSION', $newVal, time()+$secondsKeep, '/');
            $_COOKIE['SESSION'] = $newVal;
            $_COOKIE['GH-SESSION'] = $newVal;
        }else{
            setcookie('SESSION', 'expired', time(), '/');
            setcookie('GH-SESSION', 'expired', time(), '/');
            $_COOKIE['SESSION'] = 'expired';
            $_COOKIE['GH-SESSION'] = 'expired';
        }
    }
    protected function urlFor($action)
    {
        $ini = \Sooh2\Misc\Ini::getInstance();
        switch ($action){
            case 'logout':
                return $ini->getIni('application.serverip.ghuc').'/wfduc/client/user/logout';
            case 'login':
                return $ini->getIni('application.serverip.ghuc').'/wfduc/client/user/login';
            case 'check':
                return $ini->getIni('application.serverip.ghuc').'/wfduc/client/user/getuserinfo';
            case 'newLogin':
                return $ini->getIni('application.serverip.javasession') . '/login';
            case 'getSessionData':
                return $ini->getIni('application.serverip.javasession') . '/getSessionData';
            case 'newlogout':
                return $ini->getIni('application.serverip.javasession') . '/logout';
            case 'setAttr':
                return $ini->getIni('application.serverip.javasession') . '/setAttr';
        }
    }
    
    protected function argsForLogin($newUserId)
    {
        $u = \Prj\Model\User::getCopy($newUserId);//通过userid获取phone
        $u->load();
        if(!$u->exists()){
            \Sooh2\Misc\Loger::getInstance()->app_trace('try session-login by missing user:'.$newUserId);
            return false;
        }
        $phone = $u->getField('userAcc');
        $code = \Prj\Redis\Vcode::createVCode(\Sooh2\Util::remoteIP(), $phone); 
        return '{"userAcc":"'.$phone.'","userPwd":"","vericode": "'.$code.'","platform": "app" }';
    }

    protected function argsForNewLogin($uid, $secondsKeep, $platform, $args = [])
    {
        $ModelUser = \Prj\Model\User::getCopy($uid);
        $ModelUser->load();
        if (!$ModelUser->exists()) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('try get session-login by missing user:' . $uid);
            return false;
        }
        $arr = [
            'reqTime' => intval(microtime(true) * 1000),
            'platform' => 'php',
            'data' => [
                'uid' => $uid,
                'timeOut' => $secondsKeep,
                'terminal' => strtoupper($platform),
            ],
        ];

        if (!empty($args)) {
            foreach ($args as $k => $v) {
                $arr['data']['otherParams'][] = [
                    'key' => $k == 'phone' ? 'userPhone' : $k,
                    'value' => $v,
                ];
            }
        }

        return $arr;
    }

    protected function argsForGetSessionData($sessionId)
    {
        $arr = [
            'platform' => 'php',
            'reqTime' => intval(microtime(true) * 1000),
        ];

        return $arr;
    }

    protected function argsForNewLogout($sessionId, $uid, $platform = 'app')
    {
        $arr = [
            'platform' => 'php',
            'reqTime' => intval(microtime(true) * 1000),
            'data' => [
                'terminal' => $platform,
                'uid' => $uid,
            ],
        ];
        return $arr;
    }

    /**
     * 获取当前用户的uid
     */
    public function getUid()
    {
        $uid =  \Sooh2\Misc\Ini::getInstance()->getRuntime('userId');
        if($uid===null){
            $url = $this->urlFor('getSessionData');
            $curl = \Sooh2\Curl::factory(array(self::sessname4SessServer=>$this->getSessIdInCookie()));
            $strArgs = json_encode($this->argsForGetSessionData($this->getSessIdInCookie()));
            $ret = $curl->httpPost($url, $strArgs);
            \Prj\Loger::outVal('session' , $this->getSessIdInCookie());
            \Prj\Loger::outVal('ret' , $ret);
            \Sooh2\Misc\Loger::getInstance()->app_trace('getUidInSession('.$this->_sessionId.'):'.$ret);
            $r = json_decode($ret,true);
            if ($r['code'] == 10000) {
                foreach ($r['data'] as $k => $v) {
                    if ($k == 'UID') {
                        $uid = $v;
                        return $uid;
                    }
                }
            }

            return null;
        }
        return $uid;
    }

    /**
     * @param int $secondsKeep 本次登入的session保持多少秒
     * @return boolean 是否成功
     */
    public function login($newUserId,$secondsKeep=86400)
    {
        $url = $this->urlFor('login');
        $curl = \Sooh2\Curl::factory(array(self::sessname4SessServer=>$this->getSessIdInCookie()));
        
        //通过验证码完成一次登入
        $s = $curl->httpPost($url, $this->argsForLogin($newUserId) );
        \Sooh2\Misc\Loger::getInstance()->app_trace(11111111);
        \Sooh2\Misc\Loger::getInstance()->app_trace($s);
        $ret = json_decode($s,true);
        \Sooh2\Misc\Loger::getInstance()->app_trace($ret);
        if($ret['errorCode']!=0){//通过验证码登入失败
            return false;
        }else{//通过验证码登入成功
            \Sooh2\Misc\Ini::getInstance()->setRuntime('userId',$newUserId);
            \Sooh2\Misc\Loger::getInstance()->initMoreInfo('LogUser',$newUserId);
            $this->updSessionIdCookie($curl->cookies[self::sessname4SessServer],$secondsKeep);
            return true;
        }
    }

    public function newlogin($uid, $secondsKeep = 3600, $platform = 'app', $args = [])
    {
        $url = $this->urlFor('newLogin');
        \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
        $curl = \Sooh2\Curl::factory([self::sessname4SessServer => $this->getSessIdInCookie()]);

        $args = $this->argsForNewLogin($uid, $secondsKeep, $platform, $args);
        $strArgs = json_encode($args);
        $str = $curl->httpPost($url, $strArgs);
        \Sooh2\Misc\Loger::getInstance()->app_trace($str);
        $ret = json_decode($str, true);
        \Sooh2\Misc\Loger::getInstance()->app_trace($ret);
        if ($ret['code'] = 10000) {
            $this->updSessionIdCookie($curl->cookies[self::sessname4SessServer], $secondsKeep);
            \Prj\Redis\SessionId::set($uid, $curl->cookies[self::sessname4SessServer], $secondsKeep, $platform);
            return true;
        } else {
            return false;
        }
    }

    public function newlogout($uid, $platform = 'app', $sessionId = '')
    {
        $url = $this->urlFor('newlogout');
        \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
        $curl = \Sooh2\Curl::factory(array(self::sessname4SessServer => $sessionId ? : $this->getSessIdInCookie()));
        $args = $this->argsForNewLogout($this->getSessIdInCookie(), $uid, $platform);
        $strArgs = json_encode($args);
        \Sooh2\Misc\Loger::getInstance()->app_trace($strArgs);
        $s = $curl->httpPost($url, $strArgs);
        \Sooh2\Misc\Loger::getInstance()->app_trace($s);
        $ret = json_decode($s, true);
        if ($ret['code'] == 10000) {
            $this->updSessionIdCookie(null);
            return true;
        } else {
            return false;
        }
    }

    public function javaSetAttr($otherParams)
    {
        $url = $this->urlFor('setAttr');
        \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
        $curl = \Sooh2\Curl::factory(array(self::sessname4SessServer=>$this->getSessIdInCookie()));

        $funcGetArgs = function () use ($otherParams) {
            $arr =  [
                'reqTime' => intval(microtime(true) * 1000),
                'platform' => 'php',
            ];
            foreach ($otherParams as $k => $v) {
                $arr['data']['otherParams'][] = [
                    'key' => $k,
                    'value' => $v,
                ];
            }
            return $arr;
        };
        $strArgs = json_encode($funcGetArgs());
        \Sooh2\Misc\Loger::getInstance()->app_trace($strArgs);
        $s = $curl->httpPost($url, $strArgs);
        \Sooh2\Misc\Loger::getInstance()->app_trace($s);
        $ret = json_decode($s,true);
        if ($ret['code'] == 10000) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean 是否成功
     */
    public function logout()
    {
        $url = $this->urlFor('logout');
        $curl = \Sooh2\Curl::factory(array(self::sessname4SessServer=>$this->getSessIdInCookie()));
        $s = $curl->httpPost($url, '{}');
        $ret = json_decode($s,true);
        if($ret['errorCode']!=0){
            return false;
        }else{
            \Sooh2\Misc\Ini::getInstance()->setRuntime('userId','');
            $this->updSessionIdCookie(null);
            return true;
        }
    }

}

