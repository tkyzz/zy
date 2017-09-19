<?php
require_once __DIR__.'/../myautoload.php';
require_once __DIR__.'/library/GuoHuai/User.php';

if($argc>=3){
    \Sooh2\Misc\Ini::getInstance(realpath(__DIR__.'/../conf'));
    $ctrl = ucfirst($argv[1]);
    include __DIR__.'/controllers/'.$ctrl.'Controller.php';
    $class = $ctrl.'Controller';
    $ctrl = new $class();
    $f = $argv[2].'Action';
    $ctrl->$f($argv[3]);
    //echo "TODO: settlement.account_order 里type=03 派息 暂时忽略\n";
    //echo "notice: 2017-03-31 10:43:40 之前的提现手续费 不检查，直接以mimosa为准\n";
    //echo "TODO: 暂时忽略冲销\n";
    //echo "新手标志位不一致   的判断先关闭了\n";
}