<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-09-06 19:42
 */

namespace Prj\Tool;

/**
 * 万能类
 * Class Universal
 * @package Prj\Tool
 * @author lingtima@gmail.com
 */
class Universal extends Base
{
    /**
     * @var array 方法容器保存注入的类，用于替换本不存在但是要调用的方法
     */
    protected $arrFunc = [];

    protected $arrData = [];

    /**
     * 注入类方法
     * @param string $funcname 函数名
     * @param \Closure $closure 函数体，匿名函数
     * @author lingtima@gmail.com
     */
    public function bind($funcname, $closure)
    {
        $this->arrFunc[$funcname] = $closure;
    }

    public function __call($name, $arguments)
    {
        //从方法容器中调用
        if (isset($this->arrFunc[$name])) {
            call_user_func_array($this->arrFunc[$name], $arguments);
        }
    }

    public function __set($name, $value)
    {
        $this->arrData[$name] = $value;
    }

    public function __get($name)
    {
        if ($name == 'arrData') {
            return $this->$name;
        }

        return $this->arrData[$name];
    }

    public function __isset($name)
    {
        if (isset($this->arrData[$name])) {
            return true;
        }
        return false;
    }

    public function __unset($name)
    {
        if (isset($this->arrData[$name])) {
            unset($this->arrData[$name]);
        }
        return true;
    }
}