<?php
namespace Prj\RefreshStatus;
use Prj\Loger;

/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/2
 * Time: 14:14
 */
class BankMaintain extends Basic
{

    protected function getNodeData($uid)
    {
        $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path') . '/bankMaintain.json';
        $content = file_get_contents($file);
        $arr = json_decode($content,true);

        return $arr;
    }
}

