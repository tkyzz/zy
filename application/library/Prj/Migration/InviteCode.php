<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-25 18:00
 */

namespace Prj\Migration;

/**
 * 修复邀请码与二维码
 * @package Prj\Migration
 * @author lingtima@gmail.com
 */
class InviteCode extends Base
{
    public function run()
    {
        //这里直接return，不需要了
        return 1;

        $this->refreshORM = true;
//        $ModelUserFinal = \Prj\Model\UserFinal::getCopy('');
//        $dbORM = $ModelUserFinal->dbWithTablename();
        $this->getData(null, 'uid', 1, 1, 500);
    }

    public function getORM()
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy('');
        $dbORM = $ModelUserFinal->dbWithTablename();
        return $dbORM;
    }

    public function migration($userId)
    {
        usleep(50);
        $ModelUser = \Prj\Model\User::getCopy($userId);
        $ModelUser->load();
        if ($ModelUser->exists()) {
            $ModelUserFinal = \Prj\Model\UserFinal::getCopy($userId);
            $ModelUserFinal->load();
            if ($ModelUserFinal->exists()) {
                try {
                    $inviteCode = $ModelUser->getField('sceneId');
                } catch (\Exception $e) {
                    \Sooh2\Misc\Loger::getInstance()->app_trace('!!!! inviteCode is empty, userId:' . $userId);
//                    $inviteCode = \Prj\Redis\InviteCode::getNext();
//                    $ModelUser->setField('sceneId', $inviteCode);
//                    $ModelUser->saveToDB();
                    return 1;
                }
//
//                $ModelUserFinal->setField('inviteCode', $inviteCode);

//                \Sooh2\Misc\Loger::getInstance()->app_trace('-----begin check inviteQrcode----');
                if (!\Prj\Bll\Invite::getInstance()->checkQrcode($userId)) {
                    $url = \Prj\Bll\Invite::getInstance()->buildJumpUrl($userId);
                    $name = $inviteCode;
                    try {
                        $ret = \Prj\Bll\Invite::getInstance()->buildQrcode($name, $url);
                        $ModelUserFinal->setField('inviteQrcode', $ret);
                        $ModelUserFinal->saveToDB();
                        return true;
                    } catch (\Exception $e) {
                        \Sooh2\Misc\Loger::getInstance()->app_warning('build Qrcode Error,info:');
                        \Sooh2\Misc\Loger::getInstance()->app_trace($e->getMessage());
                        return 1;
                    }
                }

                $ModelUserFinal->saveToDB();
            }
        }

        return false;
    }
}