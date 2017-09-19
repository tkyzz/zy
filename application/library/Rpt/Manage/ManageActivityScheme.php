<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-19 10:58
 */

namespace Rpt\Manage;

use Rpt\KVObjBase;

class ManageActivityScheme extends KVObjBase
{
    protected $tplMap = [
         'Signin' => '签到',
         'NewbieReward' => '新手引导',
         'ActivityIcon' => '活动图标',
         'System' => '系统配置',
         'Other' => '其它配置',
        'Invite' => '邀请配置',
    ];

    public function getMap(){
        return $this->tplMap;
    }

    public static function getCopy($pkey = null)
    {
        if ($pkey === null) {
            return parent::getCopy(null);
        } else {
            return parent::getCopy(['id' => $pkey]);
        }
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_manage_activity_scheme';
    }

    public static function getActiveListByActivityName($name)
    {
        $db = static::getCopy(null)->dbWithTablename();
        $where = [
            'activity_name' => $name,
            'status' => 'on',
            '[start_time' => date('Y-m-d H:i:s', time()),
            '>end_time' => date('Y-m-d H:i:s', time()),
        ];
        $ret = $db->getRecords($db->kvobjTable(), '*', $where, 'rsort start_time');

        return $ret;
    }
}