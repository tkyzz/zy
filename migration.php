<?php
//error_reporting(E_ERROR);
ini_set('memory_limit', '10240M');
set_time_limit(0);
ini_set('error_log', '/var/log/php_lyq.log');
echo 444;

// 定义项目路径
define('APP_PATH', __DIR__ . '/'); //APP_PATH 是基于yaf的习惯了，如果使用其它框架，记得搜索替换相关文件查找部分
define('SOOH_ROUTE_VAR',false);//定义 router 变量名
define("TASK_STARTTIME_MS", microtime(true));
define('DEFAULT_RENDER_TYPE','json');//默认输入格式
define('ARGNAME_RENDER_TYPE','__VIEW__');//手动指定返回格式的参数名，如果是jsonp还需要通过 __VIEW__arg 指定回调函数名
define('ARGNAME_STATUSNOW','__STATUS__');//额外获取状态的列表
echo 2222;
include APP_PATH.'/conf/autoloadsooh2.php';//因内部有rawdata到常规参数格式的转换，所以需要在define后面引入
$ini = \Sooh2\Misc\Ini::getInstance(APP_PATH.'/conf');//读取配置文件的路径
$loger = \Sooh2\Misc\Loger::getInstance(7);//追踪类日志的记录范围
$ini->setRuntime('serverId', $ini->getIni('application.serverId'));

define('M_START_TIME', strtotime('2016-01-01 00:00:00'));
define('M_END_TIME', strtotime('2017-08-01 00:00:00'));
define('M_OLDDBCONF_NAME', 'LYQOldData');

\Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 't_money_investor_baseaccount');
$ORM = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
$ret = $ORM->getRecord($ORM->kvobjTable(), '*', ['userOid' => 'ff8080815a8451f4015a87c420b20001']);

var_dump($ret);