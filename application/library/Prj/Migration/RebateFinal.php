<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-31 17:22
 */

namespace Prj\Migration;

class RebateFinal extends Base
{
    public function run()
    {
        $this->refreshORM = true;
        $this->getData(null, 'uid');
    }

    public function getORM()
    {
        return $ModelUserFinal = \Prj\Model\UserFinal::getCopy('')->dbWithTablename();
    }

    public function migration($uid)
    {
        if (empty($this->getRecordField('rebateNum')) && empty($this->getRecordField('waitRebateNum')) && empty($this->getRecordField('rebateAmount')) && empty($this->getRecordField('waitRebateAmount'))) {
            \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 'jz_user_refere_final');
            $tmpORM = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
            $record = $tmpORM->getRecord($tmpORM->kvobjTable(), '*', ['userOid' => $uid]);

            if ($record) {
                $this->tmpRecord = $record;
                $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
                $ModelUserFinal->load();
                $ModelUserFinal->setField('rebateAmount', round($this->getTmpRecordField('totalAmount') * 100));
                $ModelUserFinal->setField('waitRebateAmount', round($this->getTmpRecordField('totalWaitRebate') * 100));
                //todo? 是否添加总邀请人数

                //统计已返与待返次数
                \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 'jz_user_rebate_detail');
                $detailORM = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
                $records = $detailORM->getRecords($detailORM->kvobjTable(), '*', ['referOid' => $uid]);

                if ($records) {
                    $tmpRebateNum = $tmpWaitRebateNum = 0;
                    foreach ($records as $tmpV) {
                        $this->tmpRecord = $tmpV;
                        if ($this->getTmpRecordField('status')) {
                            $tmpRebateNum++;
                        } else {
                            $tmpWaitRebateNum++;
                        }
                    }
                    $ModelUserFinal->setField('rebateNum', $tmpRebateNum);
                    $ModelUserFinal->setField('waitRebateNum', $tmpWaitRebateNum);
                }
                $ModelUserFinal->saveToDB();
            }
        } else {
            if (M_RECORD_EXISTS) {
                $this->breakNums++;
                $this->breakData['exists'][] = $uid;
            }
            return 1;
        }
    }
}