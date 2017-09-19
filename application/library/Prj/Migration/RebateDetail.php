<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-31 14:22
 */

namespace Prj\Migration;
use Prj\Model\InviteRebateInfo;

/**
 * 迁移返利详情数据
 * Class RebateDetail
 * @package Prj\Migration
 * @author lingtima@gmail.com
 */
class RebateDetail extends Base
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

        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 'jz_user_rebate_detail');
        $dbORM = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
        $records = $dbORM->getRecords($dbORM->kvobjTable(), '*', ['referOid' => $id]);

        if ($records) {
            $records = $this->reBuildArr($records);
            foreach ($records as $tmp) {
                $this->tmpRecord = $tmp;

                //按照订单号唯一原则
                $tmpORM = \Prj\Model\InviteRebateInfo::getCopy('')->dbWithTablename();
                if ($tmpORM->getRecord($tmpORM->kvobjTable(), '*', ['orderNo' => $this->getTmpRecordField('orderOid')])) {
                    if (M_RECORD_EXISTS) {
                        $this->breakNums++;
                        $this->breakData['exists'][] = $this->getTmpRecordField('orderOid');
                    }
                    continue;
                }

                $retry = 1;
                while ($retry < 6) {
                    \Prj\Model\InviteRebateInfo::freeCopy(null);
                    $Model = \Prj\Model\InviteRebateInfo::getCopy(['id' => \Prj\Model\InviteRebateInfo::buildId($id)]);
                    $Model->load();
                    if ($Model->exists()) {
                        $retry++;
                        continue;
                    } else {
                        break;
                    }
                }
                if ($retry >= 6) {
                    //TODO 迁移失败，需要人工干预
                    \Sooh2\Misc\Loger::getInstance()->app_warning('迁移失败，需要人工干预, record:');
                    \Sooh2\Misc\Loger::getInstance()->app_warning($v);
                    $this->breakData['error'][] = $this->getTmpRecordField('orderOid');
                    continue;
                }

                $Model->setField('uid', $id);
                $Model->setField('formUid', $this->getTmpRecordField('userOid'));
                $Model->setField('formUserPhone', $this->getTmpRecordField('userMobile'));
                $Model->setField('formUserName', $this->getTmpRecordField('userName'));
                $Model->setField('amount', sprintf('%d', $this->getTmpRecordField('amount', 0) * 100));
                $Model->setField('orderNo', $this->getTmpRecordField('orderOid'));
                $Model->setField('status', $this->getTmpRecordField('status'));
                $Model->setField('createTime', date('Y-m-d H:i:s', round($this->getTmpRecordField('createTime') / 1000)));
                $Model->setField('updateTime', date('Y-m-d H:i:s', round($this->getTmpRecordField('updateTime') / 1000)));

                $Model->saveToDB();
            }
        }
    }

    /**
     * 合并相同订单的返利数据，仅仅合并金额
     * @param $arr
     * @return array
     * @author lingtima@gmail.com
     */
    protected function reBuildArr($arr)
    {
        $tmpArr = [];
        foreach ($arr as $v) {
            if (isset($tmpArr[$v['orderOid']])) {
                $tmpArr[$v['orderOid']]['amount'] += $v['amount'];
            } else {
                $tmpArr[$v['orderOid']] = $v;
            }
        }
        return $tmpArr;
    }
}