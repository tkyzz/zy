<?php
namespace Prj\Framework;
//用到的东西很少，所以兼容着yaf直接写了个类，不用一级一级派生

class OldApiCtrl extends Ctrl
{
    protected function initPageFromRequest()
    {
        parent::initPageFromRequest();
        $this->_view = new innerView;
        $jsonstr = file_get_contents('php://input');
        $arr = json_decode($jsonstr,true);
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                $this->_request->setParam($k, $v);
            }
        }
        $this->dealwithSessionId();
    }
    protected function renderWithCodeMsg($errorCode=0,$errMsg='')
    {
        $this->_view->assign('errorCode', $errorCode);
        $this->_view->assign('errorMessage', $errMsg);
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        header('Content-type: application/json');
        echo \Sooh2\Util::toJsonSimple($this->_view->_tpl_vars);
    }
    protected function getUidInSession($userOid = null) {
        $sesss= array();
        if(!empty($_COOKIE['GH-SESSION'])){//优先这个id
            $sesss[] = $_COOKIE['GH-SESSION'];
        }
        if(!empty($_COOKIE['SESSION'])){//次级这个id
            $tmp = explode(';', str_replace(',',';',$_COOKIE['SESSION']));//忘记这里多个的情况是，还是；了
            foreach($tmp as $k){
                $k = trim($k);
                if(!empty($k)){
                    if($k!=$_COOKIE['GH-SESSION']){
                        $sesss[] = $k;
                    }
                }
            }
        }
        foreach($sesss as $sessid){
            $curl = \Sooh2\Curl::factory(array('SESSION'=> $sessid));
            $ini = \Sooh2\Misc\Ini::getInstance();
            $url = 'http://'.$ini->getIni('application.serverip.ghuc').'/wfduc/client/user/getuserinfo';
            $ret = $curl->httpPost($url, '{}');
            $r = json_decode($ret,true);
            if(is_array($r) && !empty($r['userOid'])){
                \Sooh2\Misc\Ini::getInstance()->setRuntime('userId', $this->_uid=$r['userOid']);
                return $r['userOid'];
            }
        }
        return '';
    }
///////////////////////////////////////////////////////////////////////////////////
    /**
     * 国槐系统的session
     */
    protected function dealwithSessionId()
    {
        if(!empty($_COOKIE['GH-SESSION'])){//优先这个id
            $this->_arrSessionId[] = $_COOKIE['GH-SESSION'];
        }
        if(!empty($_COOKIE['SESSION'])){//次级这个id
            $tmp = explode(';', str_replace(',',';',$_COOKIE['SESSION']));//忘记这里多个的情况是，还是；了
            foreach($tmp as $k){
                $k = trim($k);
                if(!empty($k)){
                    if($k!=$_COOKIE['GH-SESSION']){
                        $this->_arrSessionId[] = $k;
                    }
                }
            }
        }
        \Sooh2\Misc\Loger::getInstance()->app_trace('session-in-cookie find:'. implode(', ', $this->_arrSessionId));
//        
//        $curl = \Sooh2\Curl::factory(array('SESSION'=> $this->_arrSessionId));
//        $ini = \Sooh2\Misc\Ini::getInstance();
//        $url = 'http://'.$ini->getIni('application.serverip.session').$ini->getIni('Urls.getUidBySession');
//        $ret = $curl->httpPost($url, '{}');
//        \Sooh2\Misc\Loger::getInstance()->app_trace('getUidInSession('.$this->_arrSessionId.'):'.$ret);
//        $r = json_decode($ret,true);
//        if(is_array($r)){
//            \Sooh2\Misc\Ini::getInstance()->setRuntime('userId', $this->_uid=$r['userOid']);
//        }
    }
    protected $_arrSessionId=array();
    protected function rpcGHUC($cmd,$args){
        //\Sooh2\Misc\Loger::getInstance()->app_trace("tryed session order:". implode(', ', $this->_arrSessionId));
        if(empty($this->_arrSessionId)){
            $this->_arrSessionId[]='';
        }
        foreach($this->_arrSessionId as $sessid){
            $sessid = trim($sessid);
            if(empty($sessid)){
                $curl = \Sooh2\Curl::factory(array());
            }else{
                $curl = \Sooh2\Curl::factory(array('SESSION'=> $sessid));
            }
            
            $ini = \Sooh2\Misc\Ini::getInstance();
            $url = 'http://'.$ini->getIni('application.serverip.jzucapp').'/'.trim($cmd,'/');

            $ret = $curl->httpPost($url, $args);

            $chk = json_decode($ret,true);
//            \Sooh2\Misc\Loger::getInstance()->app_trace(">>>>rpcGHUC send : ".$cmd."?".$args.' with session '.$sessid. ' response is '.$ret);
            if(!is_array($chk)){
                continue;
            }
            if(!empty($chk['errorMessage']) && strpos($chk['errorMessage'],'登录')){
                continue;
            }
//            \Sooh2\Misc\Loger::getInstance()->app_trace("trace[$sessid] $cmd ".var_export(strpos($ret, '"errorCode":0,'),true)." ".$ret);
//            if(false === strpos($ret, '"errorCode":0,')){//如果session有错，尝试下一个
//                continue;
//            }
            $newCookies = $curl->cookies;
//            \Sooh2\Misc\Loger::getInstance()->app_trace("session from: ". $sessid.' TO '.json_encode($newCookies));
            foreach($newCookies as $k=>$v){
 //               if($k!='GH_SESSION'){
                    if($_COOKIE[$k]!=$v){
                        \Sooh2\Misc\Loger::getInstance()->app_trace("rpcGHUC setcookie $k $v");
                        setcookie($k, $v,time()+86400*3, '/');
                    }
 //               }
            }
//            \Sooh2\Misc\Loger::getInstance()->app_trace($cmd.'('.$this->_arrSessionId.'):'.$ret);
            $this->_arrSessionId = array($sessid);
            return $ret;
        }
        return $ret;
    }
}


class innerView
{
    public $_tpl_vars=array();
    public function assign( $name , $value = NULL ){if($value===null){unset($this->_tpl_vars[$name]);} else{$this->_tpl_vars[$name]=$value;}return $this;}

}