<?php
//define("APP_PATH",  dirname(__DIR__)); // app 跟目录，入口文件里里应该已经指定
//define('SOOH_ROUTE_VAR','__');//定义 router 变量名, 入口文件里应该已经指定
//命令行格式执行(兼容)：  php5 console\run.php  "request_uri=/test/hi/k/yes"
//命令行格式执行(兼容)：  php5 console\run.php  "__=/test/hi&k=yes"



if(isset($argv[1])){//命令行模式
    $req = \Sooh2\Yaf\Yaf_simplest::getRequest($argv[1]);
}else{//浏览器模式
    $req = \Sooh2\Yaf\Yaf_simplest::getRequest(null);
}
\Sooh2\Misc\ReqExt::beforeDispatch($req);

$module = $req->getModuleName();
$ctrl = $req->getControllerName();
$act = $req->getActionName();

if ($module=='default'){//likeyaf 的默认module 是 default
    include APP_PATH.'/application/controllers/'.ucfirst($ctrl).'.php';
    $viewTpl = APP_PATH.'/application/views/'.$ctrl.'/'.$act.'.phtml';
}else{
    include APP_PATH.'/application/modules/'.ucfirst($module).'/controllers/'.ucfirst($ctrl).'.php';
    $viewTpl = APP_PATH.'/application/modules/'.ucfirst($module).'/views/'.$ctrl.'/'.$act.'.phtml';
}

$ctrl.='Controller';
$act.='Action';
$o = new $ctrl;
$o->initBySooh($req, $view = new \Sooh2\Yaf\ViewInstead());
$o->$act();
\Sooh2\Misc\ViewExt::getInstance()->outbuf($view->render($viewTpl));