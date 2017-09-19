<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/29
 * Time: 13:13
 */

namespace Prj\Tool;

class Debug extends \Prj\Bll\_BllBase
{
    protected static $_testEnv; //ssh 模式 手动设置测试环境
    protected static $data;
    protected static $_forcePro = false; //强制设为生产服
    protected static $_forceProDisable = false; //强制设为生产服的方法无效

    const TURN_ON = true; //是否允许开启测试环境
    const TEST_IPS = ['106.14.236.8' , '106.14.25.126'];

    /**
     * Hand 强制设为生产环境
     */
    public static function forcePro(){
        \Prj\Loger::out('【强制设置为生产环境】');
        self::$_forcePro = true;
    }

    /**
     * Hand 关闭强设生产环境
     */
    public static function forceProDisable(){
        \Prj\Loger::out('【强制设置为测试环境】');
        self::$_forceProDisable = true;
    }

    /**
     * 是否允许开启测试环境 必要条件 TURN_ON=true 并且 conf目录存在 _open_test_model.ini 文件
     * @return bool
     */
    protected static function canRunTestEnv(){
        if(!self::haveFile())return false;
        if(!self::TURN_ON) return false;
        return true;
    }

    public static function setData($data = []){
        self::$data = $data;
        return true;
    }

    public static function getData($key = ''){
        return $key ? self::$data[$key] : self::$data;
    }
    /**
     * 检查当前环境是否是测试环境
     * @return bool
     */
    public static function isTestEnv(){
        if(!self::canRunTestEnv())return false;
        if(self::$_forcePro && !self::$_forceProDisable){
            \Prj\Loger::out('【强制开启了生产环境】');
            return false;
        }
        if(self::$_testEnv !== null){
            if(self::$_testEnv)\Prj\Loger::out('测试环境的特殊处理!');
            return self::$_testEnv;
        }else{
            $serverIp = self::getServerAddr();
            if(
                (!empty($serverIp) && in_array($serverIp , self::TEST_IPS )) ||
                ( self::isCliTestEnv() )
            ){
                $info = debug_backtrace(2);
                $detail = $info[0]['file'].' '.$info[0]['line'];
                \Prj\Loger::out('DEBUG TEST ENV #'.$detail);
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * Hand 获取服务器ＩＰ
     * @return null
     */
    protected static function getServerAddr(){
        if($_SERVER['SERVER_ADDR'])return $_SERVER['SERVER_ADDR'];
        $tmp = explode(' ' , $_SERVER['SSH_CONNECTION']);
        return $tmp[2] ?: null;
    }

    /**
     * 设置当前环境为测试环境
     */
    public static function setTestEnv(){
        if(isset($_SERVER['CVS_RSH']) && $_SERVER['CVS_RSH'] == 'ssh'){
            self::$_testEnv = true;
        }
    }

    /**
     * 是否是来自测试服的脚本
     * @return bool
     */
    protected static function isCliTestEnv(){
        if(!empty($_SERVER['SSH_CONNECTION'])){
            foreach (self::TEST_IPS as $v){
                if(strpos($_SERVER['SSH_CONNECTION'] , $v) !== false){
                    // \Prj\Loger::out('当前为手动脚本环境!');
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 设置当前环境为生产环境
     */
    public static function setProEnv(){
        self::$_testEnv = false;
    }

    /**
     * 是否存在 DEBUG.ini 文件
     * @return bool
     */
    protected static function haveFile(){
        try{
            \Sooh2\Misc\Ini::getInstance()->getIni('_open_test_model');
            return true;
        }catch (\Exception $e){
            return false;
        }
    }
}