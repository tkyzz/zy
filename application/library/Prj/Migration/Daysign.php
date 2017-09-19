<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-10 17:58
 */

namespace Prj\Migration;

/**
 * 签到数据迁移
 * @package Prj\Migration
 * @author lingtima@gmail.com
 */
class Daysign extends Base
{
    //TODO 同时迁移user表中checkinAmount和checkinNum字段

    /**
     * 在t_user_conpon中签到红包对应的所有name
     * 以此作为条件查询签到红包
     * @var array
     */
    static $conponName = [
        '【活动】签到红包',
        '签到大红包',
        '签到惊喜红包',
        '签到红包',
    ];

    public function run()
    {
//        \Sooh2\Misc\Loger::getInstance()->app_trace('---run---');
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy('');
        $dbORM = $ModelUserFinal->dbWithTablename();

        $this->refreshORM = true;
        $this->getData($dbORM, 'uid');
    }

    public function getORM()
    {
//        \Prj\Model\Flexible::reset('LYQOldData', 'jz_user_rebate_info');
//        return \Prj\Model\Flexible::getCopy('')->dbWithTablename();
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy('');
        return $ModelUserFinal->dbWithTablename();
    }

    /**
     * 启动迁移
     * @param string $userId 用户ID
     * @author lingtima@gmail.com
     */
    public function migration($userId)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('开始迁移：user：' . $userId);
        if ($userConponList = $this->getUserConpon($userId)) {
            $_userFinalRet = $this->updUserFinalConpon($userId, $userConponList['totalAmount'], $userConponList['totalNum']);

            $newCheckinList = $this->getNewCheckinList($userId);
            if (!empty($newCheckinList['list'])) {
                $this->newComposeR($userId, $userConponList['list'], $newCheckinList);
            }
        }
    }

    /**
     * 更新用户tb_user_final表中的最终统计信息
     * @param string $userId 用户ID
     * @param int $checkinAmount 新增签到金额
     * @param int $checkinNum 新增签到次数
     * @return bool
     * @author lingtima@gmail.com
     */
    public function updUserFinalConpon($userId, $checkinAmount, $checkinNum)
    {
        $ModelUser = \Prj\Model\User::getCopy($userId);
        $ModelUser->load();
        if (!$ModelUser->exists()) {
            return false;
        }
        $ModelUser->setField('checkinAmount', $checkinAmount);
        $ModelUser->setField('checkinNum', $checkinNum);
        $ret = $ModelUser->saveToDB();
        return $ret;
    }

    /**
     * 获取用户所有的签到明细记录
     * @param string $userId 用户ID
     * @return array|bool
     * @author lingtima@gmail.com
     */
    public function getUserConpon($userId)
    {
//        $ModelUserConpon = \Prj\Model\UserCoupon::getCopy('');
//        $ORMDb = $ModelUserConpon->dbWithTablename();
        \Prj\Model\Flexible::reset('LYQOldData', 't_user_coupon');
        $ORMDb = \Prj\Model\Flexible::getCopy('')->dbWithTablename();

        $list = $ORMDb->getRecords($ORMDb->kvobjTable(), '*', ['userId' => $userId, 'name' => self::$conponName], 'sort leadTime');
        if (empty($list)) {
            return false;
        } else {
            $data = ['totalAmount' => 0, 'totalNum' => 0];
            foreach ($list as $k => $v) {
                $data['list'][$v['oid']] = [
                    'amount' => $v['amount'] * 100,
                    'leadTime' => strtotime($v['leadTime']),
                ];
                $data['totalAmount'] += $v['amount'] * 100;
                $data['totalNum']++;
            }
            return $data;
        }
    }

    /**
     * 获取新签到明细
     * @param $userId
     * @return array|bool
     * @author lingtima@gmail.com
     */
    public function getNewCheckinList($userId)
    {
        $ModelCheckin = \Prj\Model\CheckIn::getCopy('');
        $BorkerDb = $ModelCheckin->dbWithTablename();
        $list = $BorkerDb->getRecords($BorkerDb->kvobjTable(), 'userId,ymd,date', ['userId' => $userId], 'sort date');
        if (empty($list)) {
            return false;
        } else {
            return $list;
        }
    }

    public function newComposeR($userId, $oldList, $newList = [])
    {
        $tmpList = [];
        if (!empty($newList)) {
            //转换结构
            array_map(function($v) use (&$tmpList) {
                $tmpList[$v['ymd']] = $v;
            }, $newList);
        }

        //合并结构
        $ret = [];
        foreach ($oldList as $k => $v) {
            $ymd = date('Ymd', strtotime($v['leadTime']));
            if (isset($tmpList[$ymd])) {
                $ret[$k] = array_merge($v, ['migration' => [
                    'ymd' => $ymd,
                    'total' => $tmpList[$ymd]['total'],
                    'number' => $tmpList[$ymd]['number'],
                ]]);
            } else {
                $ret[$k] = array_merge($v, ['migration' => [
                    'ymd' => $ymd,
                    'total' => 0,
                    'number' => 0,
                ]]);
            }
        }

        foreach ($ret as $k => $v) {
            $ModelCheckIn = \Prj\Model\CheckIn::getCopy($userId, date('Ymd', $v['leadTime']));
            $ModelCheckIn->load();
            if ($ModelCheckIn->exists()) {
                //TODO
                $ModelCheckIn->setField('amount', $v['amount']);
                $ModelCheckIn->setField('couponId', $k);
            } else {
                $ModelCheckIn->setField('total', $v['migration']['total']);
                $ModelCheckIn->setField('number', $v['migration']['number']);
                $ModelCheckIn->setField('date', $v['leadTime']);
                $ModelCheckIn->setField('amount', $v['amount']);
                $ModelCheckIn->setField('couponId', $k);
            }
            $ret = $ModelCheckIn->saveToDB();
        }
    }

    /**
     * 将数据整合到tb_checkin表中
     * @param $userId
     * @param $oldConpon
     * @param array $newCheckin
     * @return bool
     * @author lingtima@gmail.com
     */
    public function composeCheckinRecord($userId, $oldConpon, $newCheckin = [])
    {
//        \Sooh2\Misc\Loger::getInstance()->app_trace('oldConpon');
//        \Sooh2\Misc\Loger::getInstance()->app_trace(count($oldConpon));
//        \Sooh2\Misc\Loger::getInstance()->app_trace($oldConpon);
//        \Sooh2\Misc\Loger::getInstance()->app_trace('newCheckin');
//        \Sooh2\Misc\Loger::getInstance()->app_trace(count($newCheckin));
//        \Sooh2\Misc\Loger::getInstance()->app_trace($newCheckin);
        empty($newCheckin) && $newCheckin = [];

        $countOldCoupon = count($oldConpon);
//        if ($countOldCoupon == count($newCheckin)) {
//            return true;
//        }

        $laveCount = $countOldCoupon - count($newCheckin);
        $laveConpon = [];//旧表中过渡时期的签到券，与newCheckIn数组长度相同

        while (count($oldConpon) > 0 && count($oldConpon) > $laveCount) {
            end($oldConpon);
            $laveConpon[key($oldConpon)] = array_pop($oldConpon);
        }

//        $countLaveConpon = count($laveConpon);
        $laveConpon = array_reverse($laveConpon, true);
//        \Sooh2\Misc\Loger::getInstance()->app_trace('laveConpon');
//        \Sooh2\Misc\Loger::getInstance()->app_trace(count($laveConpon));
//        \Sooh2\Misc\Loger::getInstance()->app_trace($laveConpon);

        //将旧数据写入DB
        \Sooh2\Misc\Loger::getInstance()->app_trace('将旧数据写入DB');
        $arrPointer = 1;
        foreach ($oldConpon as $k => $v) {
            $ModelCheckIn = \Prj\Model\CheckIn::getCopy($userId, date('Ymd', $v['leadTime']));
            $ModelCheckIn->load();
            if ($ModelCheckIn->exists()) {
                \Sooh2\Misc\Loger::getInstance()->app_trace('tb_checkin这条记录已经存在了，这是不对的，pkey：');
                \Sooh2\Misc\Loger::getInstance()->app_trace($ModelCheckIn->pkey());
            } else {
                $ModelCheckIn->setField('total', $arrPointer);
                $ModelCheckIn->setField('number', ($arrPointer - 1) % 7 + 1);
                $ModelCheckIn->setField('date', $v['leadTime']);
                $ModelCheckIn->setField('amount', $v['amount']);
                $ModelCheckIn->setField('couponId', $k);
                $ret = $ModelCheckIn->saveToDB();
                \Sooh2\Misc\Loger::getInstance()->app_trace('save oldConpon-pkey:' . json_encode($ModelCheckIn->pkey()) . ' result:' . $ret);
            }
            $arrPointer++;
        }

        //将过渡时期数据写入DB
        \Sooh2\Misc\Loger::getInstance()->app_trace('将过渡时期数据写入DB');
        if (empty($newCheckin)) {
            return true;
        }

        $lavePointer = 0;
        foreach ($laveConpon as $k => $v) {
            $ModelCheckIn = \Prj\Model\CheckIn::getCopy($userId, $newCheckin[$lavePointer]['ymd']);
            $ModelCheckIn->load();
            if ($ModelCheckIn->exists()) {
                $ModelCheckIn->setField('total', $laveCount + $lavePointer + 1);
//                $ModelCheckIn->setField('number', ($laveCount + $lavePointer + 1 - 1) % 7 + 1);
                $ModelCheckIn->setField('amount', $v['amount']);
                $ModelCheckIn->setField('conponId', $k);
                $ret = $ModelCheckIn->saveToDB();
                \Sooh2\Misc\Loger::getInstance()->app_trace('save updCheckin-pkey:' . json_encode($ModelCheckIn->pkey()) . ' result:' . $ret);
            } else {
                \Sooh2\Misc\Loger::getInstance()->app_trace('tb_checkin这条记录不存在，这是不对的，pkey：');
                \Sooh2\Misc\Loger::getInstance()->app_trace($ModelCheckIn->pkey());
            }
            $lavePointer++;
        }

        return true;
    }
}