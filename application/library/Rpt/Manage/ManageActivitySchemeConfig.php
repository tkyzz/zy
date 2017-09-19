<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-19 10:58
 */

namespace Rpt\Manage;

class ManageActivitySchemeConfig extends \Rpt\Manage\_ModelBase
{
    public static function getCopy($pkey)
    {
        if ($pkey === null) {
            return parent::getCopy(null);
        }else if(is_array($pkey)){
            return parent::getCopy($pkey);
        }else {
            return parent::getCopy(['id' => $pkey]);
        }
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_manage_activity_scheme_config';
    }

    public static function getListByBASE64($base64str)
    {
        $pkey = json_decode(hex2bin($base64str),true);
        $db = static::getCopy('')->dbWithTablename();
        return $db->getRecords($db->kvobjTable(), '*', ['sid' => $pkey]);
    }

    public static function getBySidAndFlag($sid, $flag)
    {
        $db = static::getCopy('')->dbWithTablename();
        return $db->getRecord($db->kvobjTable(), '*', ['sid' => $sid, 'flag' => $flag]) ? true : false;
    }

    public static function getValueBySidAndFlag($sid, $flag)
    {
        $db = static::getCopy(null)->dbWithTablename();
        $ret = $db->getRecord($db->kvobjTable(), 'value', ['sid' => $sid, 'flag' => $flag]);
        return $ret['value'];
    }
}