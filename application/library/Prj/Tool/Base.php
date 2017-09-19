<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-10 15:11
 */

namespace Prj\Tool;

class Base
{
    protected static $instance;

    /**
     * 获取后期静态绑定实例类
     * @param array $params 构造函数参数
     * @param bool $shared 是否共享
     * @return static::class
     * @author lingtima@gmail.com
     */
    public static function getInstance($params = [], $shared = true)
    {
        $c = static::class;
        if ($shared) {
            if (!isset(self::$instance[$c])) {
                self::$instance[$c] = new $c($params);
            }
            return self::$instance[$c];
        }
        return new $c($params);
    }
}