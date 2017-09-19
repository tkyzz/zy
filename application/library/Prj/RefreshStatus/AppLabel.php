<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/22
 * Time: 10:08
 */
namespace Prj\RefreshStatus;

class AppLabel extends Basic
{
    public function getNodeData($uid)
    {
        $data = \Prj\Model\DataTmp::getRecords("`key`,`value`,ret as remark",['type'=>'app']);
        $tmp = [];
        foreach ($data as $v){
            $tmp[$v['key']] = $v;
        }
        return $tmp;
    }
}