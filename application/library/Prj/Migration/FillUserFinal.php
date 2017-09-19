<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-09-11 17:46
 */

namespace Prj\Migration;

class FillUserFinal extends Base
{
    public function run()
    {
        $this->refreshORM = true;
        $this->getData(null, 'uid');
    }

    public function getORM()
    {
        return \Prj\Model\UserFinal::getCopy('')->dbWithTablename();
    }

    public function migration($uid)
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();

        if (empty($this->getRecordField('contractId')) && empty($this->getRecordField('platform'))) {
            $ModelUser = \Prj\Model\User::getCopy($uid);
            $ModelUser->load();
            if (!$ModelUser->exists()) {
                \Sooh2\Misc\Loger::getInstance()->app_trace('[' . get_called_class() . '] cant found user model by uid:' . $uid);
            } else {
                try {
                    //fill contractId
                    $ModelUserFinal->setField('contractId', $ModelUser->getField('channelid'));
                } catch (\Exception $e) {
                    //null
                }
                try {
                    //fill platform
                    $ModelUserFinal->setField('platform', $ModelUser->getField('source'));
                } catch (\Exception $e) {
                    //null
                }
            }
        }


        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 'jz_client_transparent');
        $DBClientTransparent = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
        $TDRet = $DBClientTransparent->getRecord($DBClientTransparent->kvobjTable(), '*', ['userId' => $uid]);
        if ($TDRet) {
            if (!empty($TDRet['content'])) {
                $TDContent = json_decode($TDRet['content'], true);
                if (isset($TDContent['TDID'])) {
                    //fill tdId
                    $ModelUserFinal->setField('tdId', $TDContent['TDID']);
                }
                // fill contractData
                $ModelUserFinal->setField('contractData', $TDRet['content']);
            }
        }

        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 't_wfd_user_bank');
        $DBWfdUserBank = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
        $BankRet = $DBWfdUserBank->getRecord($DBWfdUserBank->kvobjTable(), '*', ['userOid' => $uid]);
        if ($BankRet) {
            if (!empty($BankRet['cardNumb'])) {
                //fill bindCardId
                $ModelUserFinal->setField('bindCardId', $BankRet['cardNumb']);
            }
            if (!empty($BankRet['bankName'])) {
                \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 't_platform_bankcard');
                $DBPlatformBankcard = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
                $BankInfoRet = $DBPlatformBankcard->getRecord($DBPlatformBankcard->kvobjTable(), '*', ['bankName' => $BankRet['bankName']]);
                if ($BankInfoRet && !empty($BankInfoRet['bankCode'])) {
                    //fill bankCardCode
                    $ModelUserFinal->setField('bankCardCode', $BankInfoRet['bankCode']);
                }
            }
        }

        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 't_bank_order');
        $DBBankOrder = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
        $BankOrderRet = $DBBankOrder->getRecord($DBBankOrder->kvobjTable(), '*', ['userOid' => $uid, 'type' => '02', 'returnCode' => '0000'], 'sort createTime');
        if ($BankOrderRet) {
            if (!empty($BankOrderRet['createTime'])) {
                //fill withdrawTime
                $ModelUserFinal->setField('withdrawTime', strtotime($BankOrderRet['createTime']));
            }
            if (!empty($BankOrderRet['orderNo'])) {
                //fill withdrawTime
                $ModelUserFinal->setField('withdrawId', $BankOrderRet['orderNo']);
            }
        }

        $ModelUserFinal->saveToDB();
    }
}