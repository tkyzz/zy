<?php
define('SOOH_ROUTE_VAR',false);//定义 router 变量名

//拦截估计是攻击扫描的行为
if($argc<2 && $_SERVER['HTTP_CONTENT_TYPE']!=='application/json'){//是否命令行格式或raw
    if(empty($_GET) && empty($_POST) ){
        list($k,$v) = explode('?', $_SERVER['REQUEST_URI']);
        $v = explode('/', $k);
        if(sizeof($v)==1){
            error_log('attacking??'.$_SERVER['REMOTE_ADDR']);
            exit;
        }
    }
//    elseif(isset($_REQUEST[SOOH_ROUTE_VAR])){             //如果是 SOOH_ROUTE_VAR 模式
//        if(explode('/', $_REQUEST[SOOH_ROUTE_VAR])==1){
//            error_log('attacking??'.$_SERVER['REMOTE_ADDR']);
//            exit;
//        }
//    }
}
define("TASK_STARTTIME_MS", microtime(true));
//初始化sooh2库需要的自动加载器、ini、日志设置等，所有的define，框架内不再使用，主要是index，autload，framework初始化时使用
define('APP_PATH',dirname(__DIR__)); //APP_PATH 是基于yaf的习惯了，如果使用其它框架，记得搜索替换相关文件查找部分
define('DEFAULT_RENDER_TYPE','json');//默认输入格式
define('ARGNAME_RENDER_TYPE','__VIEW__');//手动指定返回格式的参数名，如果是jsonp还需要通过 __VIEW__arg 指定回调函数名
define('ARGNAME_STATUSNOW','__STATUS__');//额外获取状态的列表

include APP_PATH.'/conf/autoloadsooh2.php';//因内部有rawdata到常规参数格式的转换，所以需要在define后面引入
$ini = \Sooh2\Misc\Ini::getInstance(APP_PATH.'/conf');//读取配置文件的路径
$loger = \Sooh2\Misc\Loger::getInstance(7);//追踪类日志的记录范围

list($requri,$reqarg) = explode('?',$_SERVER['REQUEST_URI']);
switch ($requri){
    case '':$_SERVER['REQUEST_URI']='/Oldver/xxx/yyy?'.$reqarg;break;
    //case '':$_SERVER['REQUEST_URI']='/Oldver/xxx/yyy?'.$reqarg;break;
    default: $loger->app_warning('收到未定义的旧版命令：'.$requri);die;
}



try{// 使用具体的框架开始执行任务, 里面根据分析参数的方式设置输出格式
    $viewlikeyaf = \Sooh2\Misc\ViewExt::getInstance(new \Prj\Framework\ApiView());
    $conf = $ini->getIni('application.product');

    \Sooh2\Misc\Loger::getInstance()->sys_warning('likeyaf 这里正确识别所有的路由和参数');

    

    if(isset($argv[1])){//命令行模式
        $req = \Sooh2\Yaf\Yaf_simplest::getRequest($argv[1]);
        $viewlikeyaf->initRenderType($req->get(ARGNAME_RENDER_TYPE,\Sooh2\Misc\ViewExt::type_json));
    }else{//浏览器模式
        $req = \Sooh2\Yaf\Yaf_simplest::getRequest(null);

        $viewlikeyaf->initRenderType($req->get(ARGNAME_RENDER_TYPE,DEFAULT_RENDER_TYPE),$req->get(ARGNAME_RENDER_TYPE.'_arg'));
    }
    parseRequestRaw($req);//定制的：rawdata 转换成常规的 请求参数

    $module = $req->getModuleName();
    $ctrl = $req->getControllerName();
    $act = $req->getActionName();

    \Sooh2\Misc\Uri::getInstance()->initMCA($module, $ctrl, $act)->init($conf['application.baseUri'],SOOH_ROUTE_VAR,'index.php');

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
    $viewlikeyaf->outbuf($view->render($viewTpl));
    
    $viewIntercept = \Sooh2\Misc\ViewExt::getInstance();
    $ret = $viewIntercept->renderInstead();
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