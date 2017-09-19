<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/9/11
 * Time: 11:07
 */
namespace Prj\Model;
class SystemPushConfig extends _ModelBase
{
    public function onInit()
    {
        $this->className = "ManageActivitySchemeConfig";
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = "jz_system_getui_config";
    }

    public static function getIds(){
        $data = self::getRecords(null , [] , 'sort id');
        $tmp = [];
        foreach ($data as $v){
            $tmp[] = $v['id'];
        }
        return $tmp;
    }

    public static function getIdsStr(){
        return implode(',' , self::getIds());
    }
}