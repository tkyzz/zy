<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-09-15 14:12
 */

namespace Prj\Migration;

class FakeLogin extends Base
{
    public function run()
    {
        $this->refreshORM = true;
        $this->getData(null, 'phone', ['>createTime' => round(M_START_TIME * 1000), '[createTime' => round(M_END_TIME * 1000)]);
    }

    public function getORM()
    {
        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 'jz_tmp_register_info');
        return \Prj\Model\Flexible::getCopy('')->dbWithTablename();
    }

    public function migration($id)
    {
        $ModelFakerLogin = \Prj\Model\FakePhoneContract::getCopy($id);
        $ModelFakerLogin->load();
        if ($ModelFakerLogin->exists()) {
            if (M_RECORD_EXISTS) {
                $this->breakNums++;
                $this->breakData['exists'][] = $id;
            }
            return true;
        }
        $ModelFakerLogin->setField('contractId', $this->getRecordField('contractCode'));
        $ModelFakerLogin->setField('inviteCode', $this->getRecordField('sceneId'));
        $ModelFakerLogin->setField('createTime', date('Y-m-d H:i:s', round($this->getRecordField('createTime') / 1000)));
        $ModelFakerLogin->saveToDB();
    }
}