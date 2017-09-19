<?php
//define("APP_PATH",  dirname(__DIR__)); // app 跟目录，入口文件里里应该已经指定
//define('SOOH_ROUTE_VAR','__');//定义 router 变量名, 入口文件里应该已经指定
//命令行格式执行：  php5 console\run.php  "request_uri=/test/hi/k/yes"
$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");

$dispatcher = $app->getDispatcher();

if(is_string(SOOH_ROUTE_VAR) && json_decode(SOOH_ROUTE_VAR,true)!==false){//路由是超级变量模式
    $router = $dispatcher->getRouter();
    $_route = new \Yaf_Route_Supervar(SOOH_ROUTE_VAR);
    $router->addRoute("byVar", $_route);
}
// new Yaf_Request_Simple("CLI", "Index", "Controller", "Hello", array("para" => 2));  
$dispatcher->setRequest(new \Yaf_Request_Simple());

class SoohPlugin extends Yaf_Plugin_Abstract {
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        \Sooh2\Misc\ReqExt::beforeDispatch($request);
    }
}
$dispatcher->registerPlugin(new SoohPlugin());

$dispatcher->setView( new \Sooh2\Yaf\ViewInstead( null ) );//这个派生的view 使用了 \Sooh2\Misc\ViewExt

$dispatcher->returnResponse(TRUE);
$response = $app->run();

\Sooh2\Misc\ViewExt::getInstance()->outbuf($response->getBody());

