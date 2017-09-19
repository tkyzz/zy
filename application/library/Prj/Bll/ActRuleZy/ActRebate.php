<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/3
 * Time: 20:05
 */
namespace Prj\Bll\ActRuleZy;

class ActRebate extends \Prj\Bll\_BllBase
{
    protected function getActCode(){
        return \Prj\Model\Activity::rebate_code;
    }

    
}