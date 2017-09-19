<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-13 20:18
 */

namespace Prj\Tool;

class Misc extends \Prj\Bll\_BllBase
{
    protected $breaks = [];

    public function isTestEnv(){
        if(in_array($_SERVER['SERVER_ADDR'] , ['106.14.236.8','106.14.25.126'])){
            return true;
        }else{
            return false;
        }
    }

    public function isCliEnv(){
        if(isset($_SERVER['CVS_RSH']) && $_SERVER['CVS_RSH'] == 'ssh'){
            return true;
        }else{
            return false;
        }
    }

    public function setBreak($key){
        if(isset($this->breaks[$key]) && $this->breaks[$key] === true){
            return true;
        }else{
            return false;
        }
    }

    public function enableBreak($key){
        $this->breaks[$key] = true;
        return true;
    }
}