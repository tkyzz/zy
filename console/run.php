<?php
define('SOOH_ROUTE_VAR',false);//定义 router 变量名

define("TASK_STARTTIME_MS", microtime(true));
//初始化sooh2库需要的自动加载器、ini、日志设置等，所有的define，框架内不再使用，主要是index，autload，framework初始化时使用
define('APP_PATH',dirname(__DIR__)); //APP_PATH 是基于yaf的习惯了，如果使用其它框架，记得搜索替换相关文件查找部分
define('DEFAULT_RENDER_TYPE','json');//默认输入格式
define('ARGNAME_RENDER_TYPE','__VIEW__');//手动指定返回格式的参数名，如果是jsonp还需要通过 __VIEW__arg 指定回调函数名
define('ARGNAME_STATUSNOW','extendInfo');//额外获取状态的列表

include APP_PATH.'/conf/autoloadsooh2.php';//因内部有rawdata到常规参数格式的转换，所以需要在define后面引入
$ini = \Sooh2\Misc\Ini::getInstance(APP_PATH.'/conf');//读取配置文件的路径
$loger = \Sooh2\Misc\Loger::getInstance(7);//追踪类日志的记录范围
$ini->setRuntime('serverId', $ini->getIni('application.serverId'));





try{// 使用具体的框架开始执行任务, 里面根据分析参数的方式设置输出格式
    $viewIntercept = \Sooh2\Misc\ViewExt::getInstance();
    // 另外如果要使用  \Sooh2\Misc\Uri, 需要设置Uri的当前默认module，ctrl，act
    if(class_exists('\Yaf_Application',false)){
        include __DIR__.'/index_yaf.php'; //yaf框架
    }else{
        include __DIR__.'/index_likeyaf.php'; //遵循yaf命名的只有controller和view的超轻框架
    }

    
    $ret = $viewIntercept->renderInstead(true);
    if($ret === false){
        echo str_replace('TASK_STARTTIME_MS', sprintf('%.2f(ms)', (microtime(true)-TASK_STARTTIME_MS)*1000), $viewIntercept->outbuf());
    }else{
        echo str_replace('TASK_STARTTIME_MS', sprintf('%.2f(ms)', (microtime(true)-TASK_STARTTIME_MS)*1000), $ret);
    }
    
}catch (\ErrorException $e){
    $loger->app_warning($e->getMessage()."\n".$e->getTraceAsString());
    $viewIntercept->onError($e);
    //todo: 出错后的处理
}
\Sooh2\DB::free();
//\Sooh\Base\Ini::registerShutdown(null, null);
//\Sooh\Base\Tests\Bomb::onShutdown();