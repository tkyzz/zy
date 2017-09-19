<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-11 18:36
 */

namespace Prj\Migration;

class RepairUserInviteFinal extends Base
{
    public function run()
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy('');
        $dbORM = $ModelUserFinal->dbWithTablename();
        $this->getData($dbORM, 'uid,inviter');
    }

    public function migration($uid, $inviter)
    {
//        \Sooh2\Misc\Loger::getInstance()->app_trace(func_get_args());
//        \Sooh2\Misc\Loger::getInstance()->app_trace($uid);
//        \Sooh2\Misc\Loger::getInstance()->app_trace($inviter);
        if (!empty($uid) && !empty($inviter)) {
            //更新inviteFinal表
            \Prj\Model\InviteFinal::addNewRelation($inviter, $uid);
        } else {
            if (M_RECORD_EXISTS && M_RECORD_SCARCITY) {
                $this->breakNums++;
                $this->breakData['scarcity'][] = "$uid:$inviter";
            }
        }
    }
}