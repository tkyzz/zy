<?php
namespace Rpt\Session;
/**
 * Description of Session
 *
 * @author simon.wang
 */
class Broker {
    //应该只有注册和登入时带userid调用
    public static function sessionStart($userId='0')
    {
        error_log('session start with cookie:'.$_COOKIE['ManagerSessId']);
        if(!isset($_COOKIE['ManagerSessId'])){
            self::createSessionData($userId);
        }else{
            self::$_sessData = \Rpt\Session\Data::getCopy($_COOKIE['ManagerSessId']);
            self::$_sessData->load();
            if(!self::$_sessData->exists()){
                self::createSessionData($userId);
            }elseif(!empty($userId)){//如果需要重置当前用户的id（应该只有注册和登入时带userid调用）
                self::$_sessData->setField('userId', $userId);
                self::$_sessData->saveToDB();
            }else{
                if(time()-self::$_sessData->getField('dtUpdate')>900){//距离上次操作超过15分钟了，更新一下最后活跃时间
                    self::$_sessData->saveToDB();
                }
            }
        }
        error_log('session data loaded: '.\Sooh2\Util::toJsonSimple(self::$_sessData->dump()));
    }
    //应该只有注册和登入时带userid调用
    public static function createSessionData($userId){
        self::$_sessData = \Rpt\Session\Data::createNew($userId);
        if(self::$_sessData===null){

            \Sooh2\Misc\Loger::getInstance()->app_warning('create manager session failed，disk full??too many sessions?');
            throw new \ErrorException('createSessionFailed');
        }else{
            setcookie('ManagerSessId', current(self::$_sessData->pkey()),time()+86400*14,'/');
        }
    }
    /**
     *
     * @var \Rpt\Session\Data 
     */
    protected static $_sessData=null;
    public static function getManagerId()
    {
        if(self::$_sessData===null){
            self::sessionStart();
        }
        if(self::$_sessData && self::$_sessData->exists()){
            return self::$_sessData->getField('userId');
        }else{
            return null;
        }
    }
    public static function setManagerId($userId)
    {
        if(self::$_sessData===null){
            self::sessionStart($userId);
        }
        if(self::$_sessData->getField('userId')!=$userId){
            self::$_sessData->setField('userId',$userId);
            self::$_sessData->saveToDB();
        }
    }
    public static function logout()
    {
        if(self::$_sessData===null){
            self::sessionStart();
        }
        $u = self::$_sessData->getField('userId');
        if(!empty($u)){
            self::$_sessData->setField('userId','0');
            self::$_sessData->saveToDB();
        }
    }
    public static function getRights()
    {
        $userId = self::getManagerId();
        return '*';
    }
}
