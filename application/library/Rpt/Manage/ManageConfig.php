<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-15 16:26
 */

namespace Rpt\Manage;

use Rpt\KVObjBase;

class ManageConfig extends KVObjBase
{
    public static function getCopy($identifies)
    {
        return parent::getCopy(['identifies' => $identifies]);

    }

    public static function getByBASE64($base64str)
    {
        $pkey = json_decode(hex2bin($base64str),true);
        return parent::getCopy(['identifies' => $pkey]);
    }

    protected function onInit()
    {
        //$this->className = 'UserDsk';
        parent::onInit();
        $this->field_locker = 'rowLock';//  悲观锁用的字段名，默认使用'rowLock'，设置为null表明不需要悲观锁
        $this->_tbName = 'tb_manage_config';//表名的默认模板
    }

    /**
     * 根据配置名称获取配置
     * @param string $name 配置名称
     * @return mixed
     * @author lingtima@gmail.com
     */
    public static function getOneByName($name)
    {
        $db = parent::getCopy(null)->dbWithTablename();
        $ret = $db->getRecord($db->kvobjTable(), '*', ['name' => $name]);
        return $ret;
    }

    /**
     * 获取签到的相关配置
     * @return array
     * @author lingtima@gmail.com
     */
    public static function getConfig()
    {
        $data = [
            static::getOneByName('签到-代金券类型'),
            static::getOneByName('签到-代金券名称'),
            static::getOneByName('JAVA接口后台登录账号'),
            static::getOneByName('JAVA接口后台登录密码'),
        ];

        return $data;
    }

    public static function getListByCategory($category)
    {
        $db = parent::getCopy('')->dbWithTablename();
        $ret = $db->getRecords($db->kvobjTable(), '*', ['category' => $category]);
        return $ret;
    }

    public static function parseValueByType($type, $v)
    {
        switch($type) {
            case 'int':
                $ret = (int)$v;
                break;
            default :
                $ret = $v;
        }

        return $ret;
    }
}