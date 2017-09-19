<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\RefreshStatus;

/**
 * 获取用户基础信息
 * @author simon.wang
 */
class UserBasicInfo extends Basic
{
    public function appendData($viewExt)
    {
        if ($this->getNodeData($this->getUidInSession())) {
            $viewExt->_callForAddStatusData($this->getNodeName(), $this->getNodeData($this->getUidInSession()));
        }
    }

    protected function getNodeData($uid)
    {
        if (!$uid) {
            return ['login' => 0];
        }

        $ModelUser = \Prj\Model\User::getCopy($uid);
        $ModelUser->load();
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load(true);
        if ($ModelUser->exists() && $ModelUserFinal->exists()) {
            /**
             * 判断今天是否签到
             * @param string $checkinBook json格式的签到数据
             * @return int 1已签到，0未签到
             * @author lingtima@gmail.com
             */
            $funcIsCheckin = function ($checkinBook) {
                \Sooh2\Misc\Loger::getInstance()->app_trace($checkinBook);
                if (!empty($checkinBook)) {
                    $data = is_array($checkinBook) ? $checkinBook : json_decode($checkinBook, true);
                    if (isset($data['lastRewardDate']) && $data['lastRewardDate'] == date('Ymd', time())) {
                        return 1;
                    }
                }
                return 0;
            };

            try {
                $bindCardId = $ModelUserFinal->getField('bindCardId');
            } catch (\Exception $ex) {
                $bindCardId = '';
            }

            $info['login'] = 1;
            $info['userId'] = $uid;
            $info['uid4push'] = \Prj\Bll\User::getInstance()->getIdForPush($ModelUser->getField('userAcc'));
            $info['phone'] = substr_replace($ModelUser->getField('userAcc'), '****', 3, 4);
            try {
                $info['nickname'] = $ModelUserFinal->getField('nickname');
            } catch (\Exception $ex) {
                $info['nickname'] = '';
            }
            if ($info['nickname']) {
//                $info['nickname'] = \Lib\Misc\StringH::hideStr($info['nickname'], 1, 2);
                $info['nickname'] = \Prj\Tool\Common::getInstance()->getUnsentitiveName($info['nickname']);
            }
            try{
                $info['isCheckin'] = $funcIsCheckin($ModelUser->getField('checkinBook'));
            }catch (\Exception $e){
                $info['isCheckin'] = 0;
            }
            $info['ymdReg'] = $ModelUserFinal->getField('ymdReg');
            $info['wallet'] = \Prj\Model\Payment\InvestorAssetTotal::getWallet($uid) - 0;

            try {
                $ModelUser->getField('userPwd') && $info['isPwd'] = 1;
            } catch (\Exception $e) {
                $info['isPwd'] = 0;
            }
            try {
                $ModelUser->getField('payPwd') && $info['isPayPwd'] = 1;
            } catch (\Exception $e) {
                $info['isPayPwd'] = 0;
            }

            try {
                $info['inviteCode'] = $ModelUserFinal->getField('inviteCode');
            } catch (\Exception $ex) {
                $info['inviteCode'] = '';
            }
            $info['isRealVerifiedName'] = $ModelUserFinal->getField('realVerifiedTime') ? 1 : 0;
            $info['isBindCard'] = \Prj\Bll\UserFinal::getInstance()->getIsBindCard($ModelUserFinal);
            $info['isRecharge'] = $ModelUserFinal->getField('rechargeTime') ? 1 : 0;
            $info['isOrder'] = $ModelUserFinal->getField('orderTime') ? 1 : 0;
            $info['bankCard'] = $bindCardId ? substr_replace($bindCardId, str_repeat('*', strlen($bindCardId) - 4), 0, -4) : '';//银行卡号，脱敏，只显示尾三;
            $info['bankCode'] = $ModelUserFinal->getField('bankCardCode') ? : '';
            $info['isTiro'] = \Prj\Bll\UserFinal::getInstance()->getIsTiro($ModelUserFinal);

            \Sooh2\Misc\Loger::getInstance()->app_trace($info);
            return $info;
        }

        return null;
    }

    protected function getBankData($uid)
    {
        $UserBankRet = \Prj\Model\UserBank::getOneCardByUserOid($uid);
        if ($UserBankRet) {
            $bankNamCN = $UserBankRet['bankName'];
            if ($bankNamCN) {
                $data['bankName'] = $bankNamCN;
                $data['bankCard'] = $UserBankRet['idNumb'];
                $ModelPlatformBankcard = \Prj\Model\PlatformBankcard::getCopy('');
                $DB = $ModelPlatformBankcard->dbWithTablename();
                $platformBankcardRet = $DB->getRecord($DB->kvobjTable(), '*', ['bankName' => $bankNamCN]);
                if ($platformBankcardRet) {
                    $bankCode = $platformBankcardRet['bankCode'];
                    $data['bankCode'] = $bankCode;
                }
                return $data;
            }
        } else {
            return null;
        }
    }
}
