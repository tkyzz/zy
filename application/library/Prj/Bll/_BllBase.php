<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;

/**
 * Description of User
 *
 * @author simon.wang
 */
use \Lib\Misc\Result;
use Prj\Loger;

class _BllBase {

    protected static $_instance;

    /**
     * static
     * @param string $id
     * @return static
     */
    public static function getInstance($id = ''){
        $class = get_called_class();
        $key = $class. "_" .$id;
        if(!isset(static::$_instance[$key]))static::$_instance[$key] = new static($id);
        return static::$_instance[$key];
    }

    /**
     * static
     * @param string $id
     * @return static
     */
    public static function fa($id = ''){
        //todo 不要使用这个方法
        $class = get_called_class();
        $key = $class. "_" .$id;
        if(!isset(static::$_instance[$key]))static::$_instance[$key] = new static($id);
        return static::$_instance[$key];
    }

    public function __construct()
    {
        Result::setLoger('\Prj\Loger');
        $this->init();
    }

    /**
     * 记录日志
     * @param $msg
     * @param string $tag
     */
    protected function log($msg , $tag = null){
        if($tag === null){
            \Prj\Loger::getInstance()->setTraceBegin(4);
            \Prj\Loger::out($msg);
        }else{
            \Prj\Loger::getInstance()->setTraceBeginOutVal(5);
            \Prj\Loger::outVal($tag , $msg);
        }
    }

    /**
     * Hand 输出错误信息 形如{code:99999 , message:'error'}
     * @param $msg
     * @param int $code
     * @param array $data
     * @return array
     */
    protected function resultError($msg , $code = RET_ERR , $data = []){
        if($msg == '参数错误')$msg .= '['. \Lib\Misc\Result::$errorParam .']';
        Result::setLogTraceBegin(6);
        $res = Result::get($code , $msg , $data);
        return $res;
    }

    /**
     * 输出正确信息 形如{code:10000 , message:'success' , data: array }
     * @param array $data
     * @param string $msg
     * @return array
     */
    protected function resultOK($data = [] ,$msg = 'success'){
        if(!is_array($data)){
            $msg = $data;
            $data = [];
        }
        if(empty($msg))$msg = 'success';
        return Result::get(RET_SUCC , $msg , $data);
    }

    /**
     * Hand 致命错误,预料之外的错误
     * @param $msg
     * @param int $code
     * @param array $data
     * @throws \Exception
     */
    protected function fatalErr($msg , $code = null , $data = []){
        $code = $code ?: 99999;
        $prefix = $code == 99999 ? '[致命错误]' : '';
        $msg = $prefix . $msg;
        \Lib\Misc\Result::$errorMsg = $msg;
        \Prj\Loger::getInstance()->setTraceBegin(4);
        if($data)\Prj\Loger::out($data);
        \Prj\Loger::out($msg , LOG_ERR);
        throw new \Exception( $msg , $code);
    }

    /**
     * 检查透传resultOK,resultError的信息
     * @param $res
     * @return bool
     */
    protected function checkRes($res){
        return Result::check($res);
    }

    protected function str2time(&$time){
        $time = strtotime($time) . '000';
    }

    protected function init(){}

    /**
     * Hand 获取格式化过的列表
     * @param array $params
     * @param null $callBack 数据处理方法
     * @return array
     */
    protected function _getList($params = [] , $callBack = null){
        $params['rows'] = isset($params['rows']) ? $params['rows'] : 1000;
        $params['page'] = isset($params['page']) ? $params['page'] : 1;
        if(!\Lib\Misc\Result::paramsCheck($params , ['rows','page','where','model'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $where = $params['where'];
        /** @var \Prj\Model\_ModelBase $className */
        $className = $params['model'];
        $content = $className::getRecords('' , $where , 'rsort createTime' , $params['rows'] , ($params['page'] - 1) * $params['rows']);
        $total = $className::getCount($where);
        $size = $params['rows'];
        $totalPages = ceil($total / $size);
        $res = $this->resultOK(compact('content' , 'total' , 'size' , 'totalPages'));
        if($callBack)$this->{$callBack}($res);
        return $res;
    }

    /**
     * Hand 防止用户操作太快
     * @param $id
     * @param $second
     * @return array
     */
    protected function hold($id , $second = 3){
        $key = 'php:hold:' . $id;
        $redis = \Prj\Redis\Base::getDB();
        $val = $redis->exec([
            ['INCR' , $key ]
        ]);
        \Prj\Loger::out("====== hold ==== $key ======== ". ($val - 0) ." ===============================");
        if($val == 1 || $val === false){
            $ret = $redis->exec([
                ['EXPIRE' , $key , $second ]
            ]);
            return $this->resultOK();
        }else{
            return $this->resultError('请求太快，请稍后重试');
        }
    }
}
