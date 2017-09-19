<?php
define('SOOH_ROUTE_VAR',false);//定义 router 变量名
error_log("\n\n\n\n".'------------------------------------------enter.php=>request: '.$_SERVER['REQUEST_URI']." >>[". json_encode($_COOKIE)."]>> ". file_get_contents('php://input'));

define("TASK_STARTTIME_MS", microtime(true));

define('APP_PATH',dirname(__DIR__)); //APP_PATH 是基于yaf的习惯了，如果使用其它框架，记得搜索替换相关文件查找部分

define('ARGNAME_RENDER_TYPE','__VIEW__');//手动指定返回格式的参数名，如果是jsonp还需要通过 __VIEW__arg 指定回调函数名
define('ARGNAME_STATUSNOW','extendInfo');//额外获取状态的列表

include APP_PATH.'/conf/autoloadsooh2.php';//因内部有rawdata到常规参数格式的转换，所以需要在define后面引入
$ini = \Sooh2\Misc\Ini::getInstance(APP_PATH.'/conf');//读取配置文件的路径
$loger = \Sooh2\Misc\Loger::getInstance(7);//追踪类日志的记录范围
$ini->setRuntime('serverId', $ini->getIni('application.serverId'));

if($argv[2]){//命令行模式
    define('DEFAULT_RENDER_TYPE','cmd');//默认以命令行格式输出
}else{//默认接口模式工作吧
    define('DEFAULT_RENDER_TYPE','json');//默认以json格式输出
}

define('FORCE_MCA','/default/paycallback/forward');

try{// 使用具体的框架开始执行任务, 里面根据分析参数的方式设置输出格式
    $viewIntercept = \Sooh2\Misc\ViewExt::getInstance(new \Prj\Framework\ApiView());
    if(method_exists($loger, 'initRuntimeInfo')){//loger 记录当前的session和serverid
        if(isset($_COOKIE['GH-SESSION'])){
            $loger->initRuntimeInfo($_COOKIE['GH-SESSION'], $ini->getIni('application.serverId'));
        }elseif(isset($_COOKIE['SESSION'])){
            $loger->initRuntimeInfo($_COOKIE['SESSION'], $ini->getIni('application.serverId'));
        }else{
            $loger->initRuntimeInfo('missing', $ini->getIni('application.serverId'));
        }
    }
    // 根据情况选择框架
    if(class_exists('\Yaf_Application',false)){
        include APP_PATH.'/console/index_yaf.php'; //yaf框架
    }else{
        include APP_PATH.'/console/index_likeyaf.php'; //遵循yaf命名的只有controller和view的超轻框架
    }
    
    $ret = $viewIntercept->renderInstead();
    if($ret === false){
        echo str_replace('TASK_STARTTIME_MS', sprintf('%.2f(ms)', (microtime(true)-TASK_STARTTIME_MS)*1000), $viewIntercept->outbuf());
    }else{
        $str = str_replace('TASK_STARTTIME_MS', sprintf('%.2f(ms)', (microtime(true)-TASK_STARTTIME_MS)*1000), $ret);
        $str = str_replace(':[]' , ':null' , $str);
        echo $str;
    }
}catch (\ErrorException $e){
    $loger->app_warning($e->getMessage()."\n".$e->getTraceAsString());
    $viewIntercept->onError($e);
    //todo: 出错后的处理
}
\Sooh2\DB::free();
//\Sooh\Base\Ini::registerShutdown(null, null);
//\Sooh\Base\Tests\Bomb::onShutdown();