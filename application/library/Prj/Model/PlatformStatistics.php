<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/10
 * Time: 18:00
 */

namespace Prj\Model;

class PlatformStatistics extends \Prj\Model\_ModelBase
{
    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_platform_statistics';
    }

    /**
     * Hand 根据日期获取首页数据
     * @param $ymd
     * @return array|null
     */
    public static function getDataByYmd($ymd){
        return self::getOne(['ymd' => $ymd]);
    }
}