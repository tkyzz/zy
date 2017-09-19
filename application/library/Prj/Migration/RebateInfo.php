<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-31 17:22
 */

namespace Prj\Migration;

/**
 * 迁移[用户-返利人]汇总表
 * Class RebateInfo
 * @package Prj\Migration
 * @author lingtima@gmail.com
 */
class RebateInfo extends Base
{
    public function run()
    {
        $this->refreshORM = true;
        $this->getData(null, 'uid');
    }

    public function getORM()
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy('');
        $dbORM = $ModelUserFinal->dbWithTablename();
        return $dbORM;
    }

    public function migration($id)
    {
        $v = $this->record;

        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 'jz_user_rebate_info');
        $dbORM = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
        $records = $dbORM->getRecords($dbORM->kvobjTable(), '*', ['referOid' => $id]);

        if ($records) {
            foreach ($records as $tmp) {
                $this->tmpRecord = $tmp;
                $tmpUid = $this->getTmpRecordField('userOid');
                $tmpFromUid = $this->getTmpRecordField('referOid');

                $ModelInviteFinal = \Prj\Model\InviteFinal::getCopy($tmpFromUid, $tmpUid);
                $ModelInviteFinal->load();
                if ($ModelInviteFinal->exists()) {
                    if (M_RECORD_EXISTS) {
                        $this->breakNums++;
                        $this->breakData['exists'][] = $tmpFromUid . ':' . $tmpUid;
                    }
                    continue;
                } else {
                    $ModelInviteFinal->setField('fromUserRegTime', date('Y-m-d H:i:s', strtotime($this->getRecordField('ymdReg') . $this->getRecordField('hisReg'))));
                    $ModelInviteFinal->setField('formUserPhone', $this->getTmpRecordField('userMobile'));
                    $ModelInviteFinal->setField('formUserName', $this->getRecordField('nickname'));

                    \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 'jz_user_rebate_detail');
                    $tmpDbORM = \Prj\Model\Flexible::getCopy('')->dbWithTablename();

                    $ModelInviteFinal->setField('rebateNum', $tmpDbORM->getRecordCount($tmpDbORM->kvobjTable(), ['referOid' => $tmpFromUid, 'userOid' => $tmpUid, 'status' => 1]));
                    $ModelInviteFinal->setField('rebateWaitNum', $tmpDbORM->getRecordCount($tmpDbORM->kvobjTable(), ['referOid' => $tmpFromUid, 'userOid' => $tmpUid, 'status' => 0]));
                    $tmpLastRecord = $tmpDbORM->getRecord($tmpDbORM->kvobjTable(), 'status,createTime', ['referOid' => $tmpFromUid, 'userOid' => $tmpUid], 'rsort id');
                    if ($tmpLastRecord) {
                        $ModelInviteFinal->setField('lastStatus', $tmpLastRecord['status']);
                        $ModelInviteFinal->setField('lastRebateTime', date('Y-m-d H:i:s', substr($tmpLastRecord['createTime'], 0, -3)));
                    }

                    $ModelInviteFinal->setField('rebateAmount', sprintf('%d', $this->getTmpRecordField('totalAmount') * 100));
                    $ModelInviteFinal->setField('rebateWaitAmount', sprintf('%d', $this->getTmpRecordField('totalWaitAmount') * 100));
                    if (isset($v['lastRebateTime']) && !empty($v['lastRebateTime'])) {
                        $ModelInviteFinal->setField('lastRebateTime', date('Y-m-d H:i:s', substr($this->getTmpRecordField('lastRebateTime'), 0, -3)));
                    }

                    $ModelInviteFinal->setField('lastAmount', sprintf('%d', $this->getTmpRecordField('lastRebate') * 100));
                    $ModelInviteFinal->setField('createTime', date('Y-m-d H:i:s', substr($this->getTmpRecordField('createTime'), 0, -3)));
                    $ModelInviteFinal->setField('updateTime', date('Y-m-d H:i:s', substr($this->getTmpRecordField('updateTime'), 0, -3)));
                    $ModelInviteFinal->saveToDB();
                }
                unset($tmpUid, $tmpFromUid);
            }
        } else {
            // maybe todo
        }
    }
}