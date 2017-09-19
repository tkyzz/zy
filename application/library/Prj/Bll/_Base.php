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

class _Base {

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

    public function __construct()
    {
        Result::setLoger('\Prj\Loger');
    }
}
