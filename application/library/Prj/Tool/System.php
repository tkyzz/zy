<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/1
 * Time: 14:41
 */
namespace Prj\Tool;

class System extends \Prj\Bll\_BllBase
{
    /**
     * Hand 获取系统类型 掌悦还是国槐
     * @param $type
     * @return bool
     */
    function checkType($type){
        $name = \Sooh2\Misc\Ini::getInstance()->getIni('Removal.Main.system');
        if(!in_array($name , ['gh' , 'zy']))$this->fatalErr('Removal.Main.system 配置异常');
        if($type == $name){
            return true;
        }else{
            return false;
        }
    }

    static function isGh(){
        return self::getInstance()->checkType('gh');
    }

    static function isZy(){
        return self::getInstance()->checkType('zy');
    }
}