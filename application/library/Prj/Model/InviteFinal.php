<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-04 15:42
 */

namespace Prj\Model;

class InviteFinal extends _ModelBase
{
    public static function getCopy($uid = '', $formUid = '')
    {
        return parent::getCopy(['uid' => $uid, 'formUid' => $formUid]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_invite_final_0';
    }

    /**
     * 新增待返
     * @param string $uid 用户ID
     * @param string $formUid 来源用户ID
     * @param int $amount 金额
     * @param int $status 状态：0待返，1已返
     * @return bool
     * @author lingtima@gmail.com
     */
    public static function updateWaitRebate($uid, $formUid, $amount, $status = 0)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('更新用户的统计返利表,uid:' . $uid);
        $Model = static::getCopy($uid, $formUid);
        $Model->load();
        if (!$Model->exists()) {
            $ModelUserFinal = \Prj\Model\UserFinal::getCopy($formUid);
            $ModelUserFinal->load();
            if ($ModelUserFinal->exists()) {
                $Model->setField('formUserPhone', $ModelUserFinal->getField('phone'));
                if ($ModelUserFinal->getField('nickname')) {
                    $Model->setField('formUserName', \Prj\Tool\Common::getInstance()->getNameByRealnameAndGender($ModelUserFinal->getField('nickname'), $ModelUserFinal->getField('gender')));
                }
                $Model->setField('fromUserRegTime', date('Y-m-d H:i:s', strtotime($ModelUserFinal->getField('ymdReg') . $ModelUserFinal->getField('hisReg'))));
            }
            $Model->setField('createTime', date('Y-m-d H:i:s'));
            $Model->setField('updateTime', date('Y-m-d H:i:s'));
        }

        if ($status == 0) {
            $Model->setField('lastRebateTime', date('Y-m-d H:i:s'));
//            $Model->setField('lastStatus', $status);
            $Model->setField('lastAmount', $amount);
        }
        $Model->incField('rebateWaitNum', 1);
        $Model->incField('rebateWaitAmount', $amount);
        $Model->setField('updateTime', date('Y-m-d H:i:s'));
        $ret = $Model->saveToDB();
        return $ret;
    }

    /**
     * 增加了已返
     * @param $uid
     * @param $formUid
     * @param $amount
     * @return bool
     * @author lingtima@gmail.com
     */
    public static function updateRebate($uid, $formUid, $amount)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('更新用户的统计返利表,uid:' . $uid);
        $Model = static::getCopy($uid, $formUid);
        $Model->load();
        if (!$Model->exists()) {
            $ModelUserFinal = \Prj\Model\UserFinal::getCopy($formUid);
            $ModelUserFinal->load();
            if ($ModelUserFinal->exists()) {
                $Model->setField('formUserPhone', $ModelUserFinal->getField('phone'));
                if ($ModelUserFinal->getField('nickname')) {
                    $Model->setField('formUserName', \Prj\Tool\Common::getInstance()->getNameByRealnameAndGender($ModelUserFinal->getField('nickname'), $ModelUserFinal->getField('gender')));
                }
                $Model->setField('fromUserRegTime', date('Y-m-d H:i:s', strtotime($ModelUserFinal->getField('ymdReg') . $ModelUserFinal->getField('hisReg'))));
            }
            $Model->setField('createTime', date('Y-m-d H:i:s'));
            $Model->setField('updateTime', date('Y-m-d H:i:s'));
        }

//        if ($status == 0) {
//            $Model->setField('lastRebateTime', date('Y-m-d H:i:s'));
//            $Model->setField('lastStatus', $status);
//            $Model->setField('lastAmount', $amount);
//        }
        $Model->incField('rebateNum', 1);
        $Model->incField('rebateAmount', $amount);
        $Model->setField('updateTime', date('Y-m-d H:i:s'));
        $ret = $Model->saveToDB();
        return $ret;
    }

    /**
     * 添加返利关系
     * @param string $uid 接收返利的用户
     * @param string $formUid 贡献返利的用户
     * @return bool
     * @author lingtima@gmail.com
     */
    public static function addNewRelation($uid, $formUid)
    {
//        \Sooh2\Misc\Loger::getInstance()->app_trace('新增邀请关系,uid:' . $uid);
        $Model = static::getCopy($uid, $formUid);
        $Model->load();
        if (!$Model->exists()) {
            $ModelUserFinal = \Prj\Model\UserFinal::getCopy($formUid);
            $ModelUserFinal->load();
            if ($ModelUserFinal->exists()) {
                $Model->setField('formUserPhone', $ModelUserFinal->getField('phone'));
                try {
                    $nickname = $ModelUserFinal->getField('nickname');
                } catch (\Exception $e) {
                    $nickname = '';
                }
//                try {
//                    $gender = $ModelUserFinal->getField('gender');
//                } catch (\Exception $e) {
//                    $gender = 0;
//                }
                //TODO 这里要详细处理下！
                $Model->setField('formUserName', $nickname);
                $Model->setField('fromUserRegTime', date('Y-m-d H:i:s', strtotime($ModelUserFinal->getField('ymdReg') . $ModelUserFinal->getField('hisReg'))));
            }
            $Model->setField('createTime', date('Y-m-d H:i:s'));
            $Model->setField('updateTime', date('Y-m-d H:i:s'));
            $ret = $Model->saveToDB();
        } else {
            $ret = false;
        }
        return $ret;
    }
}