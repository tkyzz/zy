<?php
define('SOOH_ROUTE_VAR','__');//定义 router 变量名
//todo: 按新的格式重写
//拦截估计是攻击扫描的行为
if($argc<2){//是否命令行格式
    if(empty($_GET) && empty($_POST)){
        error_log('attacking??'.$_SERVER['REMOTE_ADDR']);
        exit;
    }elseif(isset($_REQUEST[SOOH_ROUTE_VAR])){             //如果是 SOOH_ROUTE_VAR 模式
        if(explode('/', $_REQUEST[SOOH_ROUTE_VAR])==1){
            error_log('attacking??'.$_SERVER['REMOTE_ADDR']);
            exit;
        }
    }
}

//初始化sooh2库需要的自动加载器、ini、日志设置
define('APP_PATH',dirname(__DIR__)); //APP_PATH 是基于yaf的习惯了，如果使用其它框架，记得搜索替换相关文件查找部分
define('DEFAULT_RENDER_TYPE','www');
define('ARGNAME_RENDER_TYPE','__VIEW__');//如果是jsonp还需要通过 __VIEW__arg 指定回调函数名
include APP_PATH.'/conf/autoloadsooh2.php';
$ini = \Sooh2\Misc\Ini::getInstance(APP_PATH.'/conf');//读取配置文件的路径
$loger = \Sooh2\Misc\Loger::getInstance(7);//追踪类日志的记录范围




try{// 使用具体的框架开始执行任务, 里面根据分析参数的方式设置输出格式
    
    // 另外如果要使用  \Sooh2\Misc\Uri, 需要设置Uri的当前默认module，ctrl，act
    if(class_exists('\Yaf_Application',false)){
        include __DIR__.'/../console/index_yaf.php'; //yaf框架
    }else{
        include __DIR__.'/../console/index_likeyaf.php'; //遵循yaf命名的只有controller和view的超轻框架
    }
    
    if(!empty(\Sooh2\Misc\ViewExt::$outputBuf)){
        echo \Sooh2\Misc\ViewExt::$outputBuf;
    }else{
        echo \Sooh2\Misc\ViewExt::renderInstead();//如果拦截了框架默认输出，改由这里输出
    }
}catch (\ErrorException $e){
    $loger->app_warning($e->getMessage()."\n".$e->getTraceAsString());
    \Sooh2\Misc\ViewExt::onError(\Sooh2\Util::toJsonSimple(array('code'=>$e->getCode(),'err'=>$e->getMessage())), '/errorFound.html');
    //todo: 出错后的处理
}

//\Sooh\Base\Ini::registerShutdown(null, null);
//\Sooh\Base\Tests\Bomb::onShutdown();
