<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/27
 * Time: 9:47
 */

namespace Prj\Tmp;

class randData
{
    /**
     * @var \Yaf_View_Simple
     */
    protected static $_view;
    /**
     * @param \Yaf_View_Simple $view
     * @return bool
     */
    public static function out($view){
        self::$_view = $view;
        $num = mt_rand(0 , 9);

        if($num >= 0 && $num <= 2){
            //30%报未登录
            self::$_view->assign('data',null);
            self::assignCodeAndMessage('[DEBUG]未授权,登录失效' , 10001);
        }else if($num >= 3 && $num <= 5){
            //30%报失败
            self::$_view->assign('data',null);
            self::assignCodeAndMessage('[DEBUG]系统错误' , 99999);
        }else{
            self::assignCodeAndMessage('success');
        }
        return true;
    }

    protected static function assignCodeAndMessage($msg = '' , $code = 10000){
        self::$_view->assign('code',$code);
        self::$_view->assign('message',$msg);
        self::$_view->assign('serverMsg',"");
        self::$_view->assign('resTime',"TASK_STARTTIME_MS");
    }
}