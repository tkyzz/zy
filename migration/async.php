<?php
include (__DIR__ . '/autoload.php');

$lockFile = MIGRATION_PATH . '/lock/' . $argv[1] . '.asynclock';
if (!file_exists($lockFile)) {
    file_put_contents($lockFile, date('Y-m-d H:i:s'));

    $class = "\\Prj\\Migration\\$argv[1]";
    $obj = $class::getInstance();
    \Sooh2\Misc\Loger::getInstance()->app_trace('==========' . $argv[1] . '===run');
//    sleep(7);
    $obj->run();
    \Sooh2\Misc\Loger::getInstance()->app_trace('==========' . $argv[1] . '===end');
    @unlink($lockFile);
}