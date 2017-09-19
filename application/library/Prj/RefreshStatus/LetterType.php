<?php
namespace Prj\RefreshStatus;

/**
 * 获取未读的站内信数量
 * @author simon.wang
 */
class LetterType extends Basic{
    
    protected function getNodeData($uid)
    {
        $data = \Prj\Model\PlatformMailType::getRecords('distinct(typeCode),typeName',null);
        return $data;
        $data =array();
//        foreach(\Prj\Model\Letter::$types as $id=>$r){
//            $data[]=array('typeCode'=>$id,'typeName'=>$r['name']);
//        }
//        return $data;
    }
}
