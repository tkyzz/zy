<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-13 14:20
 */

namespace Prj\Model;

use \Prj\Tool\TimeTool;
use Sooh2\DB\KVObj;

class CheckIn extends KVObj
{
    protected static $_dbAndTbName;

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_checkin_0';
    }

    /**
     * @param string $userId
     * @param string $ymd
     * @return KVObj
     * @author lingtima@gmail.com
     */
    public static function getCopy($userId, $ymd = '')
    {
        if (empty($ymd)) {
            $ymd = date('Ymd', time());
        }
        return parent::getCopy(['userId' => $userId, 'ymd' => $ymd]);
    }

    /**
     * 添加签到记录
     * @param string $userId 用户ID
     * @param string $ymd 签到日期：年月日
     * @param array $reward 奖励内容
     * @param int $nowNums 当前签到次数（第几次）
     * @param string $couponId 券ID
     * @return bool
     * @author lingtima@gmail.com
     */
    public static function add($userId, $ymd, $reward, $nowNums, $couponId)
    {
        $ModelCheckIn = self::getCopy($userId, $ymd);
        $ModelCheckIn->load();
        if ($ModelCheckIn->exists()) {
            return false;
        }

        $lastOne = self::getLastOneByUserId($userId);
        if (!empty($lastOne)) {
            $totalNumber = $lastOne['total'] + 1;
        } else {
            $totalNumber = 1;
        }

        $ModelCheckIn->setField('total', $totalNumber);
        $ModelCheckIn->setField('bonus', json_encode($reward));
        $ModelCheckIn->setField('date', time());
        $ModelCheckIn->setField('number', $nowNums);
        $ModelCheckIn->setField('amount', $reward['amount']);
        $ModelCheckIn->setField('couponId', $couponId);
        $ret = $ModelCheckIn->saveToDB();

        return $ret;
    }

    public static function getLastOneByUserId($userId)
    {
        $db = static::getCopy('')->dbWithTablename();
        return $db->getRecord($db->kvobjTable() , '*' , ['userId' => $userId], 'rsort ymd');
    }
}