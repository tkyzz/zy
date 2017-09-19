<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-21 18:52
 */

namespace Prj\Bll;

use Rpt\Manage\ManageActivityScheme;
use Rpt\Manage\ManageActivitySchemeConfig;

class ActivityConfig extends _BllBase
{
    public static $activitySchemeConfig;

    /**
     * 获取配置项的值
     * @param string $activityName 配置分组名
     * @param string $flag 具体标志名
     * @param string $item 取值项
     * @return bool|mixed
     * @author lingtima@gmail.com
     */
    public function getConfig($activityName, $flag, $item = 'value')
    {
        //优化-首次调用即加载全部并缓存
        if ($ret = $this->getFromCache($activityName, $flag, $item)) {
            return $ret;
        }

        $activeScheme = $this->getActiveScheme($activityName);
        if (empty($activeScheme)) {
            return false;
        }

        $sid = $activeScheme['id'];
//        $dbBroker = ManageActivitySchemeConfig::getCopy(null)->dbWithTablename();
        $arrConfig = ManageActivitySchemeConfig::getRecords('*', ['sid' => $sid]);
        if ($arrConfig) {
            $this->setToCache($activityName, $arrConfig);
            return $this->getFromCache($activityName, $flag, $item);
        } else {
            return false;
        }

//        return ManageActivitySchemeConfig::getValueBySidAndFlag($sid, $flag);
    }

    public function getActiveScheme($activityName)
    {
        $list = ManageActivityScheme::getActiveListByActivityName($activityName);
        if ($list) {
            if (count($list) == 1) {
                return $list[0];
            }

            $lastestStartTime = 0;//最近的开始时间
            $lastestStartKey = 0;
            foreach ($list as $k => $v) {
                $timeToNum = strtotime($v['start_time']);
                if ($timeToNum > $lastestStartTime) {
                    $lastestStartTime = $timeToNum;
                    $lastestStartKey = $k;
                }
            }
            return $list[$lastestStartKey];
        } else {
            return [];
        }
    }

    protected function getFromCache($activityName, $flag, $item = 'value')
    {
        if (isset(self::$activitySchemeConfig[$activityName])) {
            if ($item == '_array') {
                return self::$activitySchemeConfig[$activityName];
            }
            foreach (self::$activitySchemeConfig[$activityName] as $k => $v) {
                if ($v['flag'] == $flag) {
                    return $item == '_lines' ? $v : (isset($v[$item]) ? $v[$item] : false);
                }
            }
            return false;
        }
        return false;
    }

    protected function setToCache($activityName, $value)
    {
        self::$activitySchemeConfig[$activityName] = $value;
    }
}