<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;
use Prj\Tool\Misc;

/**
 * Description of User
 *
 * @author simon.wang
 */
class User extends _BllBase
{

    protected $userId;

    public function __construct($userId = '')
    {
        if (!empty($userId)) {
            $this->userId = $userId;
        }
    }

    /**
     * 检查开关位状态
     * @param string $type 类型
     * @return bool|mixed 1：打开状态；0：关闭状态
     * @author lingtima@gmail.com
     */
    public function checkSwitchStatus($type = 'login')
    {
        return \Prj\Bll\ActivityConfig::getInstance()->getConfig('系统配置', "system_{$type}_switch");
    }

    /**
     * Hand
     * todo 国槐老接口
     * 是否绑卡
     * @return int|null
     */
    public function checkBind()
    {
        if (empty($this->userId)) return null;
        $card = \Prj\Model\UserBank::getOneCardByUserOid($this->userId);
        if (empty($card)) {
            //未绑卡
            return 0;
        } else {
            if (empty($card['bankName'])) {
                //已经解绑
                return 2;
            } else {
                //已绑卡
                return 1;
            }
        }
    }

    /**
     * Hand
     * 是否充值
     * todo 国槐老接口
     * @return bool|null
     */
    public function checkRecharge()
    {
        if (empty($this->userId)) return null;
        $ucUser = \Prj\Model\UcUser::getUserByOid($this->userId);
        if (empty($ucUser)) return null;
        $setUserId = $ucUser['memberOid'];
        $recharge = \Prj\Model\BankOrder::getOneRechargeByUserOid($setUserId);
        return empty($recharge) ? false : true;
    }

    /**
     * Hand
     * 是否购买过
     * todo 国槐老接口
     * @return bool|null
     */
    public function checkBuy()
    {
        if (empty($this->userId)) return null;
        $miUser = \Prj\Model\MimosaUser::getUserByUcUserId($this->userId);
        if (empty($miUser)) return null;
        $miUserId = $miUser['oid'];
        $buy = \Prj\Model\TradeOrder::getOneOrderByUserOid($miUserId);
        return empty($buy) ? false : true;
    }

    /**
     * 对用户密码加盐加密
     * @param string $pwd 密码明文
     * @param string $salt 盐
     * @return string 密码密文
     * @author lingtima@gmail.com
     */
    public function encryptPwd($pwd, $salt)
    {
        return bin2hex(sha1(hex2bin($salt) . $pwd, true));
    }

    public function localLogin($uid, $secondsKeep = 0, $phone, $platform = 'app', $args = [])
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('===========begin local login');

        //解除登录锁定
        \Prj\Redis\LoginFailed::unlock($phone);
        \Sooh2\Misc\Loger::getInstance()->app_trace('解除登录锁定结束');

        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load(true);
        if ($ModelUserFinal->exists()) {
            $args = array_merge($args, ['isTiro' => $ModelUserFinal->getField('isTiro') == 0 ? 'false' : 'true']);
        }

//        \Prj\Session::getInstance()->login($uid, $secondsKeep);
        return \Prj\Session::getInstance()->newlogin($uid, $secondsKeep ? : $this->getSessionKeepExpire($platform), $platform, $args);
    }

    public function localLogout($uid, $platform = 'app')
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('============begin local logout');
        return \Prj\Session::getInstance()->newlogout($uid, $platform);
    }

    public function beforeUpdatePwd($uid, $userPwd)
    {
        $url = \Sooh2\Misc\Ini::getInstance()->getIni('application.serverip.ZYSettlement') . '/ZYSettlement/account/pwdCheckRepeat';
        \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
        $curl = \Sooh2\Curl::factory();

        $funcGetArgs = function () use ($uid, $userPwd) {
            return [
                'reqTime' => time(),
                'data' => [
                    'userId' => $uid,
                    'password' => $userPwd,
                ],
            ];
        };

        $strArgs = json_encode($funcGetArgs());
        $str = $curl->httpPost($url, $strArgs);
        \Sooh2\Misc\Loger::getInstance()->app_trace('original:' . $str);
        $ret = json_decode($str, true);
        \Sooh2\Misc\Loger::getInstance()->app_trace($ret);
        if ($ret['code'] = 10000) {
            return $ret['data']['result'];//true没有重复，false已重复
        }
        return false;
    }

    /**
     * 变更用户密码
     * @param string $uid 用户ID
     * @author lingtima@gmail.com
     */
    public function updatePwd($uid)
    {
        $this->kickedOut($uid);
    }

    /**
     * 给所有session执行登出操作
     * @param string $uid 用户ID
     * @return bool
     * @author lingtima@gmail.com
     */
    public function logoutForAllSession($uid)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('============begin local logout');

        //获取用户所有sessionId
        $arrSessionId = \Prj\Redis\SessionId::get($uid);
        if (!$arrSessionId) {
            return false;
        }

        //全session登出
        foreach ($arrSessionId as $sessionId => $v) {
            \Prj\Session::getInstance()->newlogout($uid, $v['platform'], $sessionId);
        }
        \Prj\Redis\SessionId::del($uid);

        return true;
    }

    /**
     * 更新session中的新手标志位
     * @param string $uid yonghuID
     * @param int $isTiro 是否新手：1新手，0不是新手
     * @return bool
     * @author lingtima@gmail.com
     */
    public function setTiroInSession($uid, $isTiro = 1)
    {
        //获取用户所有sessionId
        $arrSessionId = \Prj\Redis\SessionId::get($uid);
        if (!$arrSessionId) {
            return true;
        }

        //更新session
        foreach ($arrSessionId as $sessionId => $v) {
            $param = ['isTiro' => $isTiro ? 'true' : 'false'];
            \Prj\Session::getInstance()->javaSetAttr($param);
        }
        return true;
    }

    /**
     * 本地注册
     * @param $uid
     * @param $phone
     * @return bool|\Sooh2\DB\KVObj
     * @author lingtima@gmail.com
     */
    public function localRegister($uid, $phone)
    {
        $ret = \Prj\Model\User::createNew($uid, $phone, 'phone');
        return $ret;
    }

    /**
     * 新版用户注册
     * @param string $phone 手机号
     * @param string $contractId 渠道号
     * @param array $args args
     * @param bool $allowFakeReg 是否允许假注册来重置contractId
     * @return bool|\Prj\Model\UserFinal
     * @author lingtima@gmail.com
     */
    public function newBigRegister($phone, $contractId = '', $args = [], $allowFakeReg = true)
    {
        //填写了正确邀请码的算是自己人渠道
        if ($upInviteTree = \Prj\Bll\Invite::getInstance()->getUpInviteTreeByCode($args['inviteCode'])) {
            $contractId = \Sooh2\Misc\Ini::getInstance()->getIni('contract.own.contractId');
            $allowFakeReg = false;
        }

        if ($NewModelUser = \Prj\Model\User::createNewSelf($phone, $contractId, $args)) {
            $uid = $NewModelUser->pkey()['oid'];
            $mineInviteCode = $NewModelUser->getField('sceneId');
            if ($ret = \Prj\Model\UserLogin::create($uid, $phone, 'phone')) {
                //从假注册中获取渠道号
                if ($allowFakeReg && $_tmpContractId = $this->getRealContractForRegister($phone)) {
                    $args['contractId'] = $_tmpContractId;
                } else {
                    $args['contractId'] = $contractId;
                }
                if ($ModelUserFinal = \Prj\Model\UserFinal::createMew($uid, $phone, $args, $mineInviteCode)) {
                    if (isset($args['tdData']) && !empty($args['tdData'])) {
                        \Prj\Bll\Td::getInstance()->infoFromApp(['userId' => $uid, 'type' => 'register', 'content' => $args['tdData']]);
                    }
                    $this->registerEvt($uid);
                    return $ModelUserFinal;
                }
            }
        }
        return false;
    }

    /**
     * 注册时间通知
     * @param $uid
     * @author lingtima@gmail.com
     */
    public function registerEvt($uid)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('注册通知事件开始');
        if ($ret = \Prj\Model\EventQueue::addOne('JavaEvt\RegisterOk', '', $uid, '')) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('注册通知事件结束，成功。eventId：' . $ret);
        } else {
            \Sooh2\Misc\Loger::getInstance()->app_trace('注册通知事件结束，失败');
        }
    }

    /**
     * 获取session有效期时间
     * @param string $platform 平台
     * @return int
     * @author lingtima@gmail.com
     */
    protected function getSessionKeepExpire($platform = 'APP')
    {
        switch (strtoupper($platform)) {
            case 'APP':
            case 'ANDROID':
            case 'IOS':
                return (86400 * 7);
            case 'PC':
            case 'WX':
            case 'LANDING':
            case 'H5':
                return 3600 * 2;
            default:
                return 1800;
        }
    }

    /**
     * 创建用户ID
     * @return bool|string
     * @author lingtima@gmail.com
     */
    public function createUid()
    {
        $uid = substr(md5(microtime()), 0, 28);
        return $uid;
    }

    /**
     * @param $phone
     * @param string $pwd
     * @return bool|\Prj\Model\UserFinal
     * @author lingtima@gmail.com
     */
    public function registerNew($phone, $pwd = '')
    {
        $ModelUcUser = \Prj\Model\UcUser::getCopyByPhone($phone);
        $ModelUcUser->load();
        if (!$ModelUcUser->exists()) {
            //旧版本JAVA接口未注册，即tb_user表不存在此记录，但此表PHP维护不了，故报错返回
            \Prj\Loger::out('user not found! phone:' . $phone, LOG_ERR);
            return false;
        }

        $ret = \Prj\Model\User::createNew($ModelUcUser->getField('oid'), $phone, 'phone');
        return $ret;
    }

    /**
     * 新建用户，用户新旧版本切换期间（20170630-宝宝树注册页）
     * @param string $phone phone
     * @param string $pwd pwd
     * @return bool|\Sooh2\DB\KVObj
     * @author lingtima@gmail.com
     */
    public function register2($phone, $pwd = '')
    {
        $ModelUcUser = \Prj\Model\UcUser::getCopyByPhone($phone);
        $ModelUcUser->load();
        if (!$ModelUcUser->exists()) {
            //旧版本JAVA接口未注册，即tb_user表不存在此记录，但此表PHP维护不了，故报错返回
            \Prj\Loger::out('user not found! phone:' . $phone, LOG_ERR);
            return false;
        }

        $ret = \Prj\Model\User::create2($ModelUcUser->getField('oid'), $phone, 'phone');
        return $ret;
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
    public function createContractNew($uid, $platform = '', $contractId = '', $contractData = '', $tdId = '', $otherArgs = '', $inviteCode = '')
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
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
            }
        } else {
            //生成自己的邀请码
            $BllInvite = \Prj\Bll\Invite::getInstance();
            $inviteCode = $BllInvite->writeInviteCode($uid);
        }
        $ModelUserFinal->saveToDB();
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

    /**
     * 通过mimosa投资者ID获取uc用户信息
     * todo 国槐老接口
     * @param $investorId
     * @return array
     */
    public function getUcUserInfoByInvestorId($investorId){
        \Prj\Loger::out('investorId: '.$investorId);
        $investorInfo = \Prj\Model\MimosaUser::getUserByMiUserId($investorId);
        if(empty($investorInfo))return $this->resultError('投资者信息不存在！');
        $userId = $investorInfo['userOid'];
        \Prj\Loger::out('userOid: '.$userId);
        $userInfo = \Prj\Model\UcUser::getCopy($userId);
        $userInfo->load();
        if(!$userInfo->exists())return $this->resultError('用户信息不存在！');
        return $this->resultOK([
            'info' => $userInfo->dump(),
        ]);
    }

    /**
     * 如果用户曾经伪注册过，则返回当时的渠道号
     * @param $phone
     * @return bool
     * @author lingtima@gmail.com
     */
    public function getRealContractForRegister($phone)
    {
        $ModelFakePhoneContract = \Prj\Model\FakePhoneContract::getCopy($phone);
        $ModelFakePhoneContract->load();
        if ($ModelFakePhoneContract->exists()) {
            return $ModelFakePhoneContract->getField('contractId');
        }
        return false;
    }

    public function userBasicInfo($uid)
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
                if (!empty($checkinBook)) {
                    $data = json_decode($checkinBook, true);
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
            $info['phone'] = substr_replace($ModelUser->getField('userAcc'), '****', 3, 4);
            try {
                $info['nickname'] = $ModelUserFinal->getField('nickname');
            } catch (\Exception $ex) {
                $info['nickname'] = '';
            }
            $info['isCheckin'] = $funcIsCheckin($ModelUser->getField('checkinBook'));
            $info['ymdReg'] = $ModelUserFinal->getField('ymdReg');
            $info['wallet'] = $ModelUserFinal->getField('wallet');

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
            $info['isBindCard'] = $ModelUserFinal->getField('isBindCard') ? 1 : 0;
            $info['isRecharge'] = $ModelUserFinal->getField('rechargeTime') ? 1 : 0;
            $info['isOrder'] = $ModelUserFinal->getField('orderTime') ? 1 : 0;
            $info['bankCard'] = $bindCardId ? substr_replace($bindCardId, str_repeat('*', strlen($bindCardId) - 3), 0, -3) : '';//银行卡号，脱敏，只显示尾三;
            $info['bankCode'] = $ModelUserFinal->getField('bankCardCode') ? : '';
            $info['isTiro'] = $ModelUserFinal->getField('isTiro');

            return $info;
        }

        return null;
    }

    public function getFieldByUid($uid, $field = 'phone')
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        return $ModelUserFinal->getField($field);
    }

    /**
     * 校验是否签到
     * @param string $userId 用户ID
     * @param mixed $checkinBook checkinBook字段
     * @return bool
     * @author lingtima@gmail.com
     */
    public function isCheckin($userId = '', $checkinBook = '')
    {
        if (!empty($userId)) {
            $ModelCheckin = \Prj\Model\CheckIn::getCopy($userId, date('Ymd'));
            $ModelCheckin->load();
            return $ModelCheckin->exists();
        }

        if (!empty($checkinBook)) {
            $data = is_array($checkinBook) ? $checkinBook : json_decode($checkinBook, true);
            if (isset($data['lastRewardDate']) && $data['lastRewardDate'] == date('Ymd')) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取push用的ID
     * @param string $phone phone
     * @return string
     * @author lingtima@gmail.com
     */
    public function getIdForPush($phone)
    {
        $randomStr = '8G68I2gFstbFYamFpjpeb';
//        return '8' . substr($phone, 0, 1) . 'tbFY' . substr($phone, 1, 1) . 'jpe' . substr();
        return $phone . $randomStr;
    }

    public function lockUser($uid)
    {
        //目前没有操作
        $this->kickedOut($uid);
        return true;
    }

    /**
     * 踢出用户
     * @param string $uid 用户ID
     * @return bool true踢出成功，false踢出失败
     * @author lingtima@gmail.com
     */
    public function kickedOut($uid)
    {
        $this->logoutForAllSession($uid);
        //TODO 主动通知。。。

        $params = [
            'custom' => true, //是否开启自定义推送,使用PHP自定义模板进行推送
            'data' => [
                'type' => 1,//推送客户群类型 0-全局 1-个人
                'customType' => [1, 2],//推送客户端类型
                'templateContent' => json_encode(['content' => ['type' => 'kickedout', 'timestamp' => time() * 1000]]),//透传内容
            ]
        ];
        \Prj\EvtMsg\Sender::getInstance()->setSender('push', $params)->sendCustomMsg('推送踢人', '踢出用户：' . $uid, [$uid], ['push']);

        return true;
    }

    /**
     * 检查用户是否被冻结
     * @param string $uid 用户ID
     * @return bool true被冻结，false未被冻结
     * @throws \Exception
     * @author lingtima@gmail.com
     */
    public function checkFreeze($uid)
    {
        return \Prj\Redis\UserFreeze::contains($uid);
    }

    /**
     * 冻结用户
     * @param string $uid 用户ID
     * @return bool true冻结成功，false冻结失败
     * @author lingtima@gmail.com
     */
    public function freeze($uid)
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            $ModelUserFinal->setField('freeze', time());
            if ($ModelUserFinal->saveToDB()) {
                \Prj\Redis\UserFreeze::freeze($uid);
                return $this->kickedOut($uid);
            }
        }
        return false;
    }

    /**
     * 解冻用户
     * @param string $uid 用户ID
     * @return bool true解冻成功，false解冻失败
     * @author lingtima@gmail.com
     */
    public function unfreeze($uid)
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            $ModelUserFinal->setField('freeze', 0);
            if ($ModelUserFinal->saveToDB()) {
                \Prj\Redis\UserFreeze::unfreeze($uid);
                return true;
            }
        }
        return false;
    }
}
