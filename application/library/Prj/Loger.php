<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/15
 * Time: 14:31
 */
namespace Prj;

class Loger extends \Sooh2\Misc\Loger
{
    /**
     * Hand 输出消息,可以定义错误等级
     * @param $value
     * @param int $level
     * @return null|Loger|\Sooh2\Misc\Loger
     */
    public static function out($value , $level = LOG_INFO)
    {
        if (is_object($value)) {
            $msg = 'obj# '.var_export($value , true);
        }else if(is_array($value)){
            $msg = 'array# '.json_encode($value , 256);
        }else{
            $value = str_replace(array("\r\n", "\r", "\n"), " ", $value);
            $msg = $value;
        }

        $loger = static::getInstance();
        $loger->traceLevel(4);
        $loger->env = parent::getInstance()->env;

        $loger->init($level);

        switch ($level){
            case LOG_INFO : $loger->app_trace($msg);break;
            case LOG_ERR : $loger->app_warning($msg);break;
            default : $loger->app_trace($msg);break;
        }
        $loger->resetTraceBegin();
        return $loger;
    }

    /**
     * Hand 输出带标记的信息
     * @param $key
     * @param $value
     * @return null|Loger|\Sooh2\Misc\Loger
     */
    public static function outVal($key , $value){
        $loger = self::getInstance();
        $loger->setTraceBegin($loger->trace_begin_arr['outVal']);
        if (is_object($value)) {
            $msg = var_export($value , true);
        }else if(is_array($value)){
            $msg = json_encode($value , 256);
        }else{
            $msg = $value;
        }
        return self::out($key.' >>>>>>>>>>>>>>>>>> '.$msg);
    }

    /**
     * Hand 打标记
     * @param $key
     * @param null $value
     */
    public static function setKv($key , $value = null){
        self::$mark[$key] = $value;
        $loger = self::getInstance();
        $loger->init();
    }

//======================================================================================================================

    protected static $_instance;

    public $trace_begin = 3; //traceInfo 起始位置

    public $trace_begin_arr = [
        'outVal' => 4, //outVal 方法的 traceInfo 起始位置
    ];

    protected function init($level = LOG_INFO){
        $str = '['. $this->_processId .']' . self::getMarkStr();
        $prefix1 = '['.$this->getLevelTag($level).'_]' . $str;
        $prefix2 = '['.$this->getLevelTag($level).']' . $str;
        \Sooh2\Misc\Loger::getInstance()->prefixIntro = $prefix2;
        $this->prefixIntro = $prefix1;
    }

    /**
     * Hand 复位$trace_begin
     * @return $this
     */
    public function resetTraceBegin(){
        $this->trace_begin = 3;
        $this->trace_begin_arr['outVal'] = 4;
        return $this;
    }

    /**
     * Hand 设置$trace_begin
     * @param $num
     * @return $this
     */
    public function setTraceBegin($num){
        $this->trace_begin = $num;
        return $this;
    }

    /**
     * Hand 设置outval的traceInfo位置
     * @param $num
     * @return $this
     */
    public function setTraceBeginOutVal($num){
        $this->trace_begin_arr['outVal'] = $num;
        return $this;
    }

    public static function getInstance($newInstance_or_traceLevel=null)
    {
        if($newInstance_or_traceLevel!=null){
            if(is_int($newInstance_or_traceLevel)){
                self::$_instance = new Loger();
                self::$_instance->traceLevel($newInstance_or_traceLevel);
            }else{
//                error_log('create loger by new instance');
//                $err = new \ErrorException();
//                error_log($err->getTraceAsString());
                self::$_instance = $newInstance_or_traceLevel;
            }
        }else{
            if(empty(self::$_instance)){
                self::$_instance = new Loger();
                self::$_instance->traceLevel(0);
            }
        }
        return self::$_instance;
    }

    protected static $mark = []; //打标记

    public static function setUid($userId){
        self::setKv('uid' , $userId);
    }

    public static function setOrderId($orderId){
        self::setKv('orderId' , $orderId);
    }

    public static function setPhone($phone){
        self::setKv('phone' , $phone);
    }

    public static function free($arr = []){
       if($arr){
           foreach ($arr as $v){
               unset(self::$mark[$v]);
           }
       }else{
       foreach (self::$mark as $k => $v){
           if($v !== null)unset(self::$mark[$k]);
       }
    }
    }

    protected static function getMarkStr(){
        $tmp = '';
        ksort(self::$mark);
        foreach (self::$mark as $k => $v){
            $tmp .= '[';
            if($v === null){
                $tmp .= $k;
            }else{
                $tmp .= $k .':' .$v;
            }

            $tmp .= ']';
        }
        return $tmp;
    }

    protected function getLevelTag($level){
        switch ($level){
            case LOG_INFO : return 'INFO';break;
            case LOG_WARNING : return 'INFO_WARNING';break;
            case LOG_ERR : return 'INFO_ERR';break;
            default : return 'INFO';
        }
    }

    public function __construct(){
        $this->_processId = \Lib\Misc\StringH::randStr(8);
    }

    public static function setTag($str = ''){} //todo 已废弃
    public static $prefix; //前缀 todo 已废弃
    public static function addPrefix($str){} //todo 已废弃
    public static function reset(){} //todo 已废弃
}