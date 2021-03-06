<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-29 14:12
 */

namespace Prj\Model;

class UserFinal extends \Prj\Model\_ModelBase
{
    public static function getCopy($pkey = null)
    {
        return parent::getCopy($pkey === null ? null : ['uid' => $pkey]);
    }

    protected function onInit()
    {
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = 'tb_user_final_0';
    }

//    public function getField($k)
//    {
//        if ($k == 'inviteCode') {
//            $inviteCode = parent::getField($k);
//            if ($inviteCode) {
//                return $inviteCode;
//            }
//            //生成新的InviteCode
//            $pkey = parent::pkey();
//            if ($inviteCode = \Prj\Bll\Invite::getInstance()->writeInviteCode($pkey['uid'])) {
//                $this->createInviteCode($inviteCode);
//                return $inviteCode;
//            } else {
//                return false;
//            }
//
//        } else {
//            return parent::getField($k);
//        }
//    }

    public static function createInviteCode($uid, $inviteCode)
    {
        $db = self::getCopy($uid);
        $db->load();
        if ($db->exists()) {
            $db->setField('inviteCode', $inviteCode);
            $db->saveToDB();
            return true;
        } else {
            \Prj\Loger::out('func:::UserFinalModel->createInviteCode not found user, uid:' . $uid);
            return false;
        }
    }

    /**
     * 新增待返
     * @param string $uid 用户ID
     * @param int $amount 金额
     * @return bool|static
     * @author lingtima@gmail.com
     */
    public static function updateWaitRebate($uid, $amount)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('更新用户的最终返利统计信息' . $uid);
        $Model = static::getCopy($uid);
        $Model->load();
        if ($Model->exists()) {
            $Model->incField('waitRebateNum', 1);
            $Model->incField('waitRebateAmount', $amount);
            $ret = $Model->saveToDB();
            return $Model;
        } else {
            return false;
        }
    }

    /**
     * 新增已返
     * @param string $uid 用户ID
     * @param int $amount 金额
     * @return bool|static
     * @author lingtima@gmail.com
     */
    public static function updateRebate($uid, $amount)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('更新用户的最终返利统计信息' . $uid);
        $Model = static::getCopy($uid);
        $Model->load();
        if ($Model->exists()) {
            $Model->incField('RebateNum', 1);
            $Model->incField('RebateAmount', $amount);
            $ret = $Model->saveToDB();
            return $Model;
        } else {
            return false;
        }
    }

    public static function createMew($uid, $loginName, $args = [], $mineInviteCode = '')
    {
        //写入tb_user_final表
        $ModelUserFinal = self::getCopy($uid);
        $ModelUserFinal->load();
        \Sooh2\Misc\Loger::getInstance()->app_trace($ModelUserFinal->dbWithTablename()->lastCmd());
        if ($ModelUserFinal->exists()) {
            \Sooh2\Misc\Loger::getInstance()->app_warning('create user to tb_user_final failed, because uid duplicate. uid:' . $uid);
            return false;
        }
        $time = time();
        $ModelUserFinal->setField('phone', $loginName);
        $ModelUserFinal->setField('nickname', $loginName);
        $ModelUserFinal->setField('ymdReg', date('Ymd', $time));
        $ModelUserFinal->setField('hisReg', date('His', $time));
        $ModelUserFinal->setField('dtLast', $time);
        $ModelUserFinal->setField('inviteCode', $mineInviteCode);

        isset($args['platform']) AND !empty($args['platform']) AND $ModelUserFinal->setField('platform', $args['platform']);
        isset($args['contractId']) AND !empty($args['contractId']) AND $ModelUserFinal->setField('contractId', $args['contractId']);
        isset($args['contractData']) AND !empty($args['contractData']) AND $ModelUserFinal->setField('contractData', $args['contractData']);
        isset($args['tdId']) AND !empty($args['tdId']) AND $ModelUserFinal->setField('tdId', $args['tdId']);
        isset($args['otherArgs']) AND !empty($args['otherArgs']) AND $ModelUserFinal->setField('otherArgs', $args['otherArgs']);

        if (isset($args['inviteCode']) && !empty($args['inviteCode'])) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('填写了邀请码');
            if ($upInviteTree = \Prj\Bll\Invite::getInstance()->getUpInviteTreeByCode($args['inviteCode'])) {
                \Sooh2\Misc\Loger::getInstance()->app_trace('邀请码有效，开始记录邀请关系');
                $ModelUserFinal->setField('inviter', $upInviteTree['uid']);
                $ModelUserFinal->setField('fatherInviter', $upInviteTree['inviter'] ?: $upInviteTree['uid']);
                $ModelUserFinal->setField('rootInviter', $upInviteTree['rootInviter'] ?: $upInviteTree['uid']);

                //更新inviteFinal表
                \Prj\Model\InviteFinal::addNewRelation($upInviteTree['uid'], $uid);
            }
        }

        $ret = $ModelUserFinal->saveToDB();
        \Sooh2\Misc\Loger::getInstance()->app_trace($ModelUserFinal->dbWithTablename()->lastCmd());
        \Sooh2\Misc\Loger::getInstance()->app_trace($ret);
        return $ModelUserFinal;
    }

    /**
     * 保存渠道关系与邀请关系
     * @param string $uid 用户ID
     * @param string $platform 平台
     * @param string $contractId 渠道ID
     * @param string $contractData 保留字ID
     * @param string $tdId tdid
     * @param string $otherArgs otherArgs
     * @param string $inviteCode 邀请码
     * @return bool|\Sooh2\DB\KVObj
     * @author lingtima@gmail.com
     */
    public function createContract($uid, $platform = '', $contractId = '', $contractData = '', $tdId = '', $otherArgs = '', $inviteCode = '')
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load(true);
        if (!$ModelUserFinal->exists()) {
            \Prj\Loger::out('use cant found in DB>table>tb_user_count. uid:' . $uid);
            return false;
        }

        empty($platform) OR $ModelUserFinal->setField('platform', $platform);
        empty($contractId) OR $ModelUserFinal->setField('contractId', $contractId);
        empty($contractData) OR $ModelUserFinal->setField('contractData', $contractData);
        empty($tdId) OR $ModelUserFinal->setField('tdId', $tdId);
        empty($otherArgs) OR $ModelUserFinal->setField('otherArgs', $otherArgs);
        if (!empty($inviteCode)) {
            if ($upInviteTree = Invite::getInstance()->getUpInviteTreeByCode($inviteCode)) {
                $ModelUserFinal->setField('inviter', $upInviteTree['uid']);
                $ModelUserFinal->setField('fatherInviter', $upInviteTree['inviter'] ?: $upInviteTree['uid']);
                $ModelUserFinal->setField('rootInviter', $upInviteTree['rootInviter'] ?: $upInviteTree['uid']);

                //更新inviteFinal表
                \Prj\Model\InviteFinal::addNewRelation($upInviteTree['uid'], $uid);
            }
        } else {
            //生成自己的邀请码
            $BllInvite = \Prj\Bll\Invite::getInstance();
            $inviteCode = $BllInvite->writeInviteCode($uid);
        }
        $ModelUserFinal->saveToDB();
        return $ModelUserFinal;
    }

    //获取手机号
    public static function getUserPhone($arr){

        if( count($arr) >1 ){
            $data = self::getRecords('phone',['uid'=>$arr]);
//            print_r($arr);
//            print_r($data);
        }else{
            $data = self::getRecord('phone',['uid'=>$arr]);
        }
        if( count($data) > 1 ){
            foreach($data as $k=>$v){
                $res[] = $v['phone'];
            }
        }else{
            $res[] = $data['phone'];
        }
        return implode(',',$res);

    }

}