<?php
include (__DIR__ . '/autoload.php');

if (isset($argv[1])) {
    migration($argv[1]);
} else {
    migration();
}

//var_dump(checkFileLock('asynclock'));

function migration($taskName = '')
{
    \Sooh2\Misc\Loger::getInstance()->app_trace('==============================BEGIN MIGRATION==============================');

    //万能容器，注入要执行的任务
    $container = \Prj\Tool\Universal::getInstance([], false);

    if ($taskName) {
        $container->$taskName = $taskName;
    } else {
        $container->userLogin = 'UserLogin';//字符串为同步
        $container->fakeLogin = 'FakeLogin';
        $container->userFinal = 'UserFinal';//数组为异步
        $container->repairUserInviteFinal = 'RepairUserInviteFinal';
        $container->rebateDetail = 'RebateDetail';
        $container->rebateInfo = 'RebateInfo';
        $container->rebateFinal = 'RebateFinal';
        $container->fillUserFinal = 'FillUserFinal';//填充UserFinal剩余字段
        $container->userCheckin = 'UserCheckin';//统计签到信息
    }

    foreach ($container->arrData as $k => $task) {
        \Sooh2\Misc\Loger::getInstance()->app_trace('=====准备执行Task：' . $k);
        if (is_array($task)) {
            async($task[0], 'run');
        } else {
            sync($task);
        }
        sleep(1);
    }

    \Sooh2\Misc\Loger::getInstance()->app_trace('==============================END MIGRATION==============================');
    \Sooh2\Misc\Loger::getInstance()->app_trace('==============================可能有未完成的异步任务正在执行==============================');
}

function sync($className, $funcName = 'run')
{
    while (true) {
        if (checkFileLock('.asynclock')) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('=====锁定中');
            sleep(30);
//            sleep(3);
            continue;
        } else {
            $lockFile = MIGRATION_PATH . '/lock/' . $className . '.synclock';
            file_put_contents($lockFile, date('Y-m-d H:i:s'));

            $class = "\\Prj\\Migration\\$className";
            $obj = $class::getInstance();
            \Sooh2\Misc\Loger::getInstance()->app_trace('==========' . $className . '===run');
            $obj->run();
//            sleep(10);
            \Sooh2\Misc\Loger::getInstance()->app_trace('==========' . $className . '===end');
            @unlink($lockFile);
            return true;
        }
    }
    return false;
}

function async($className, $funcName = 'run')
{
    while (true) {
        if (checkFileLock('.synclock')) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('=====锁定中');
            sleep(30);
//            sleep(3);
            continue;
        } else {
            $command = '/usr/bin/php ' . MIGRATION_PATH . '/async.php ' . $className . ' ' . $funcName . ' >/dev/null 2>&1 &';
            pclose(popen($command, 'r'));
            return true;
        }
    }
    return false;
}

function checkFileLock($lockFlag = 'lock')
{
    $filePath = MIGRATION_PATH . '/lock/';
    $fso = opendir($filePath);
    $boolFlag = false;
    while($file = readdir($fso)) {
        if ($file != '.' && $file != '..' && $file != 'hold.ini') {
            if (substr($file, -(strlen($lockFlag))) == $lockFlag) {
                $boolFlag = true;
                break;
            }
        }
    }
    closedir($fso);
    return $boolFlag;
}