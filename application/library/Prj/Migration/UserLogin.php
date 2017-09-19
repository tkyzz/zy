<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-30 14:39
 */

namespace Prj\Migration;

class UserLogin extends Base
{
    public function run()
    {
//        $ModelUser = \Prj\Model\User::getCopy('');
//        $dbORM = $ModelUser->dbWithTablename();
//        \Prj\Model\Flexible::reset('LYQOldData', 'tb_user_0');
//        $dbORM = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
        $this->refreshORM = true;
        $this->getData(null, 'userOid', ['>createTime' => date('Y-m-d H:i:s', M_START_TIME), '[createTime' => date('Y-m-d H:i:s', M_END_TIME)]);
    }

    public function getORM()
    {
        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 't_money_investor_baseaccount');
        return \Prj\Model\Flexible::getCopy('')->dbWithTablename();
    }

    public function migration($id)
    {
        $phone = $this->record['phoneNum'];
        $ModelUserLogin = \Prj\Model\UserLogin::getCopy($phone);
        $ModelUserLogin->load();
        if (!$ModelUserLogin->exists()) {
            $ModelUserLogin->setField('uid', $id);
            $ModelUserLogin->setField('createTime', $this->getRecordField('createTime'));
            $ModelUserLogin->setField('createYmd', date('Y-m-d', strtotime($this->getRecordField('createTime'))));
            $ret = $ModelUserLogin->saveToDB();
        } else {
            if (M_RECORD_EXISTS) {
                $this->breakNums++;
                $this->breakData['exists'][] = $id;
            }
        }
    }
}