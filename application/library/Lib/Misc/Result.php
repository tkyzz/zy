<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/16
 * Time: 13:58
 */
namespace Lib\Misc;

define('RET_SUCC' , 10000);
define('RET_ERR' , 89999);

/**
 * 参数检查和结果处理
 * Class Result
 * @package Lib\Misc
 */
class Result
{
    /** @var  \Prj\Loger */
    protected static $loger = '\Prj\Loger';

    protected static $prefix;

    public static $errorParam;

    public static $errorMsg;

    public static function setLoger($loger){
        static::$loger = $loger;
    }

    public static function setPrefix($fix){
        static::$prefix = $fix;
    }
    /**
     * 验证参数是否存在，或者为空
     * @param $params
     * @param $needle
     * @return bool
     */
    public static function paramsCheck($params , $needle){
        foreach ($needle as $v){
            if (!array_key_exists($v,$params)||$params[$v]===''||$params[$v]===NULL)
            {
                //static::get(RET_PARAM_ERROR, '参数错误['.$v.']');
                static::$errorParam = $v;
                return false;
            }
        }
        return true;
    }

    /**
     * 信息的透传
     * @param $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function get($code, $msg='', $data = array())
    {
        $ret = array(
            'code' => $code,
            'message'  => $msg,
            'data' => $data,
        );
        if(self::$errorMsg){
            $ret['_errorMsg'] = self::$errorMsg;
            self::$errorMsg = '';
        }
        if($code !== RET_SUCC){
            $msg =  json_encode($ret,JSON_UNESCAPED_UNICODE);
            //如果报错,打出错误的位置,方便追踪
//            $traceInfo = debug_backtrace(3);
//            $path = $traceInfo[1]['file'] . ' ' .$traceInfo[1]['line'].' '.$traceInfo[2]['function'].'()';
            static::log($msg );
            $ret['message'] = $ret['message'] ? $ret['message'] : 'FAILED';
        }else{
            $ret['message'] = $ret['message'] ? $ret['message'] : 'SUCCESS';
        }
        return $ret;
    }

    /**
     * 检查透传结果的正确与否
     * @param $res
     * @return bool
     */
    public static function check($res){
        if(!isset($res['code'])||$res['code']!==RET_SUCC){
            return FALSE;
        }
        return TRUE;
    }

    protected static $trace_begin = 5;

    public static function setLogTraceBegin($num){
        self::$trace_begin = $num;
    }
    /**
     * 输出日志的方法
     * @param $msg
     * @param int $level
     */
    protected static function log($msg , $level = LOG_INFO){
        if(static::$loger || method_exists(static::$loger , 'out')){
            $loger = static::$loger;
            $loger::getInstance()->setTraceBegin(self::$trace_begin);
            $loger::out($msg , $level);
            self::$trace_begin = 5;
        }else{
            error_log($msg);
        }
    }
}