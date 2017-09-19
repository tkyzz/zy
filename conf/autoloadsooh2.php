<?php
if(!function_exists('autoload_sooh2')){
    function autoload_sooh2($class){
        $tmp = explode('\\', $class);
        if(sizeof($tmp)==1){
            return false;
        }
        $cmp = array_shift($tmp);
        if($cmp===''){
            $cmp = array_shift($tmp);
        }
        if($cmp=='Sooh2'){
            include APP_PATH.'/Sooh2/'.implode('/', $tmp).'.php';
            return true;
        }else{
            return false;
        }
    }
    spl_autoload_register('autoload_sooh2');
}

if(!function_exists('autoload_locallib')){
    function autoload_locallib($class){
        $tmp = explode('\\', $class);
        if(sizeof($tmp)==1){
            return false;
        }
        $cmp = array_shift($tmp);
        if($cmp===''){
            $cmp = array_shift($tmp);
        }
        //error_log(">>>>>>>>>>>>>>$cmp>>>>>>".implode('/',$tmp));
        switch($cmp){
            case 'Lib':$f = APP_PATH.'/application/library/Lib/'.implode('/', $tmp).'.php';break;
            case 'Prj':$f = APP_PATH.'/application/library/Prj/'.implode('/', $tmp).'.php';break; 
            case 'Rpt':$f = APP_PATH.'/application/library/Rpt/'.implode('/', $tmp).'.php';break; 
        }
        //error_log(">>>>>autoload:>>>>>>>>>$cmp>>>>>>".$f);
        if(is_file($f)){
            include $f;
            return true;
        }else{
            return false;
        }
    }
    spl_autoload_register('autoload_locallib');
}

