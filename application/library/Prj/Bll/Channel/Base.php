<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-18 11:30
 */

namespace Prj\Bll\Channel;

use Sooh2\Curl;

class Base extends \Prj\Bll\_BllBase
{
    public $doCallback = false;
    public $noticeId;
    public $contractId;
    public $userId;

    /**
     * 通知接口，当第三方合作平台需要通知我方时触发此动作
     * 将idfa等标识写入数据库
     * @param string $channelName channelName
     * @param string $idfa idfa
     * @param string $appid appid
     * @param array $args args
     * @return bool
     * @author lingtima@gmail.com
     */
    public function notice($channelName, $idfa, $appid, $args)
    {
        if (empty($args)) {
            return false;
        }

        $ModelChannelNotice = \Prj\Model\ChannelNotice::getCopyByAppidAndIdfa($appid, $idfa);
//        $ModelChannelNotice = \Prj\Model\ChannelNotice::getCopyByChannelAppidIdfa($channelName, $appid, $idfa);
        $ModelChannelNotice->load();
        if ($ModelChannelNotice->exists()) {
            //已经通知过
            return true;
        }

        $ModelChannelNotice->setField('createTime', date('Y-m-d H:i:s'));
        $ModelChannelNotice->setField('channelName', $channelName);
        foreach ($args as $k => $v) {
            $ModelChannelNotice->setField($k, $v);
        }
        $ret = $ModelChannelNotice->saveToDB();
        if ($ret) {
            $ModelChannelNotice->load(true);
            return $ModelChannelNotice->getField('id');//新增
        }
        return $ret;
    }

    /**
     * 激活上报-激活时反填与透传-激活指下载后初次打开app，此时用户并未注册
     * @param string $deviceType 设备类型：idfa或者imei
     * @param string $deviceId 设备ID
     * @param string $contractId 渠道号
     * @param array $notice notice 详情
     * @return bool
     * @author lingtima@gmail.com
     */
    public function reportActivation($deviceType, $deviceId, $contractId, $notice)
    {
        //仅仅更新channel_notice与device_contractId表
        $channelNoticeParams = $deviceContractIdParams = [];
        $channelNoticeParams['callbackStatus'] = 1;
        $channelNoticeParams['callbackCreateTime'] = $deviceContractIdParams['callbackTime'] = date('Y-m-d H:i:s', time());
        $deviceContractIdParams['callbackRet'] = 1;//准备激活
        if (!empty($notice['callback'])) {
            if ($this->callback($notice['callback'])) {
                $channelNoticeParams['callbackRet'] = 1;
                $deviceContractIdParams['callbackRet'] = 2;//已经激活回调
            }
        }

        $channelNoticeParams['contractId'] = $contractId;
        $this->updChannelNotice($notice['id'], $channelNoticeParams);

        $this->updDeviceContract($deviceType, $deviceId, $contractId, $deviceContractIdParams);
        return true;
    }

    /**
     * 发现用户透传的设备信息时触发此动作-透传动作目前在用户注册完成后触发
     * 修改用户contractId
     * 修改用户渠道解析数据
     * 调用上报方法
     * @param $userId
     * @param $idfa
     * @param $notice
     * @return bool
     * @author lingtima@gmail.com
     */
    public function report($userId, $idfa, $notice)
    {
        $channelName = $notice['channelName'];
        $this->noticeId = $notice['id'];
        $this->userId = $userId;

        $contractId = $this->getContractId($channelName);
        $this->contractId = $contractId;
        \Sooh2\Misc\Loger::getInstance()->app_trace('contractId from aisi:' . $contractId);
        //反填contractId 更新[国槐用户表t_wfd_user]中的channelId
        $ModelUcUser = \Prj\Model\UcUser::getCopy($userId);
        $ModelUcUser->load();
        $updRet = false;
        if ($ModelUcUser->exists()) {
            $_UcUserDB = $ModelUcUser->dbWithTablename();
            $updRet = $_UcUserDB->updRecords($_UcUserDB->kvobjTable(), ['channelid' => $contractId], ['oid' => $userId]);
            \Sooh2\Misc\Loger::getInstance()->app_trace($_UcUserDB->lastCmd());
            \Sooh2\Misc\Loger::getInstance()->app_trace('save user contranctId to table:' . $_UcUserDB->kvobjTable() . '. userId:' . $userId . '. contractId:' . $contractId);
        }

        if ($updRet) {
            //解析channelId
            $this->updChannelRes($contractId, $userId);

            //更新uid
            $ModelChannelNotice = \Prj\Model\ChannelNotice::getCopy($this->noticeId);
            $ModelChannelNotice->load();
            $ModelChannelNotice->setField('uid', $this->userId);
            $ModelChannelNotice->saveToDB();

            if ($this->callback($notice['callback'])) {
                $this->updNotice();
//                return true;
            }
        }
        return $updRet;
    }

    /**
     * 反填完渠道数据后的回调处理，仅包含主动调用第三方接口
     * 被动等待第三方调用时不可使用
     * @param $url
     * @return bool
     * @author lingtima@gmail.com
     */
    public function callback($url)
    {
        $url = urldecode($url);
        \Sooh2\Misc\Loger::getInstance()->app_trace('callback URL:' . $url);
        if (!$this->doCallback || empty($url)) {
            return true;
        }

        //重试两次
        $retry = 2;
        $callbackFlag = false;
        while ($retry > 0) {
            $callbackResp = \Sooh2\Curl::factory([])->httpGet($url);
            \Sooh2\Misc\Loger::getInstance()->app_trace('callback result for ' . $url . ':' . $callbackResp);
            //可能有两种返回格式:1/0    或者   {"success":"true/false","message":"msg..."}
            if (is_numeric($callbackResp)) {
                if ($callbackResp == 1) {
                    $callbackFlag = true;
                    break;
                } else {
                    $retry--;
                    continue;
                }
            } else {
                if ($callbackResp = json_decode($callbackResp, true)) {
                    if (isset($callbackResp['success']) && $callbackResp['success'] == 'true') {
                        $callbackFlag = true;
                        break;
                    } else {
                        $retry--;
                        continue;
                    }
                } else {
                    $retry--;
                    continue;
                }
            }
        }

        if ($callbackFlag) {
            return true;
        }
        return false;
    }

    /**
     * 最后更新通知表，意味着一个idfa的流程全部执行完了
     * 通知-设备id接口透传-解析与反填-上报第三方
     * @param string $noticeId noticeId
     * @param string $contractId contractId
     * @return bool
     * @author lingtima@gmail.com
     */
    public function updNotice($noticeId = '', $contractId = '')
    {
        $ModelChannelNotice = \Prj\Model\ChannelNotice::getCopy($noticeId ? : $this->noticeId);
        $ModelChannelNotice->load();
        if ($ModelChannelNotice->exists()) {
            $ModelChannelNotice->setField('callbackStatus', 1);
            $ModelChannelNotice->setField('callbackCreateTime', date('Y-m-d H:i:s', time()));
            $ModelChannelNotice->setField('callbackRet', 1);
            $ModelChannelNotice->setField('contractId', $contractId);
            $ModelChannelNotice->saveToDB();
           \Sooh2\Misc\Loger::getInstance()->app_trace('update aisi_notice table, last SQL:' . $ModelChannelNotice->dbWithTablename()->lastCmd());
           return true;
        } else {
            \Sooh2\Misc\Loger::getInstance()->app_trace('cant found record in table Channel_notice by id:' . $noticeId ? : $this->noticeId);
        }
        return false;
    }

    /**
     * 更新用户渠道信息解析表
     * @param string $contractId 渠道号
     * @param string $userId 用户ID
     * @return bool
     * @author lingtima@gmail.com
     */
    public function updChannelRes($contractId, $userId)
    {
        //解析channelId
        $_tmpUpdRedData = $arrContractArgs = $this->parseContract($contractId);
        \Sooh2\Misc\Loger::getInstance()->app_trace($arrContractArgs);

        $ModelUserChannelRes = \Prj\Model\UserChannelRes::getCopyByUid($userId);
        $ModelUserChannelRes->load();
        $_DBUserChannelRes = $ModelUserChannelRes->dbWithTablename();
//        if ($ModelUserChannelRes->exists()) {
//            \Sooh2\Misc\Loger::getInstance()->app_trace('found record in ' . $_DBUserChannelRes->kvobjTable() . ', by userId = ' . $userId);
//        } else {
//            \Sooh2\Misc\Loger::getInstance()->app_trace('cant found record in ' . $_DBUserChannelRes->kvobjTable() . ', by userId = ' . $userId);
//        }

        $ModelChannelInfo = \Prj\Model\ChannelInfo::getByCopartnerSource($arrContractArgs['channelCode']);
        $ModelChannelInfo->load();
        $ModelChannelInfo->exists() AND $_tmpUpdRedData['channelId'] = $ModelChannelInfo->getField('id');

        $ModelAgreementInfo = \Prj\Model\AgreementInfo::getCopyByAgrNo($arrContractArgs['maskCode']);
        $ModelAgreementInfo->load();
        $ModelAgreementInfo->exists() AND $_tmpUpdRedData['agrId'] = $ModelAgreementInfo->getField('id');

        $ModelSpreadInfo = \Prj\Model\SpreadInfo::getCopyBySpreadNo($arrContractArgs['spreadCode']);
        $ModelSpreadInfo->load();
        $ModelSpreadInfo->exists() AND $_tmpUpdRedData['spreadId'] = $ModelSpreadInfo->getField('id');

        \Sooh2\Misc\Loger::getInstance()->app_trace('=================');

        if ($ModelUserChannelRes->exists()) {
            $_tmpUpdResult = $_DBUserChannelRes->updRecords($_DBUserChannelRes->kvobjTable(), $_tmpUpdRedData, ['userOid' => $userId]);
        } else {
            $_tmpUpdResult = $_DBUserChannelRes->addRecord($_DBUserChannelRes->kvobjTable(), array_merge($_tmpUpdRedData, ['userOid' => $userId, 'createTime' => microtime(true) * 1000]));
        }
//        $_tmpUpdResult = $_DBUserChannelRes->updRecords($_DBUserChannelRes->kvobjTable(), $_tmpUpdRedData, ['userOid' => $userId]);
        \Sooh2\Misc\Loger::getInstance()->app_trace('last UserChannelRes SQL:' . $_DBUserChannelRes->lastCmd());
        \Sooh2\Misc\Loger::getInstance()->app_trace($_tmpUpdResult);
        if ($_tmpUpdResult) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('update table ' . $_DBUserChannelRes->kvobjTable() . ' success, uid:contractId is' . $userId . ':' . $contractId);
            return true;
        }
        \Sooh2\Misc\Loger::getInstance()->app_trace('update table ' . $_DBUserChannelRes->kvobjTable() . ' failed!');
        return false;
    }

    /**
     * 根据渠道名称获取渠道ID
     * @param $channelName
     * @return mixed
     * @author lingtima@gmail.com
     */
    public function getContractId($channelName)
    {
        $channelName = strtolower($channelName);
        $contractId = \Sooh2\Misc\Ini::getInstance()->getIni("AisiNotice.{$channelName}noticeContractId");
        return $contractId;
    }

    /**
     * 解析渠道号
     * @param string $contractId 渠道号
     * @return array ['channelCode', 'publishTime', 'spreadCode', 'maskCode']
     * @author lingtima@gmail.com
     */
    public function parseContract($contractId)
    {
        $arrContractArgs = [
            'channelCode' => substr($contractId, 0, 4),
            'publishTime' => substr($contractId, 4, 8),
            'spreadCode' => substr($contractId, 12, 1),
            'maskCode' => substr($contractId, -5),
        ];

        \Sooh2\Misc\Loger::getInstance()->app_trace($arrContractArgs);
        return $arrContractArgs;
    }

    /**
     * 更新设备号-渠道号表
     * @param string $deviceType 类型
     * @param string $deviceId 设备号
     * @param string $deviceContractid 渠道号
     * @param array $params
     * @return bool
     * @author lingtima@gmail.com
     */
    public function updDeviceContract($deviceType, $deviceId, $deviceContractid, $params)
    {
        $ModelDeviceContractid = \Prj\Model\DeviceContractid::getCopy($deviceType, $deviceId, $deviceContractid);
        $ModelDeviceContractid->load();
        if (!$ModelDeviceContractid->exists()) {
            \Sooh2\Misc\Loger::getInstance()->app_trace("cant found record from ModelDeviceContractid by pkey:({$deviceType}:{$deviceId}:{$deviceContractid})");
            return false;
        }

        foreach ($params as $k => $v) {
            $ModelDeviceContractid->setField($k, $v);
        }
        try {
            $updRet = $ModelDeviceContractid->saveToDB();
            if (!$updRet) {
                \Sooh2\Misc\Loger::getInstance()->app_trace('upd table ModelDeviceContractid failed. last sql:');
                \Sooh2\Misc\Loger::getInstance()->app_trace($ModelDeviceContractid->dbWithTablename()->lastCmd());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            \Sooh2\Misc\Loger::getInstance()->app_trace($e->getMessage());
            return false;
        }
    }

    /**
     * 更新渠道通知表
     * @param string $id 主键ID
     * @param array $params 要更新的数据
     * @return bool
     * @author lingtima@gmail.com
     */
    public function updChannelNotice($id, $params)
    {
        $ModelChannelNotice = \Prj\Model\ChannelNotice::getCopy($id);
        $ModelChannelNotice->load(true);
        if (!$ModelChannelNotice->exists()) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('cant found record from ModelChannelNotice by pkey:' . $id);
            return false;
        }

        foreach ($params as $k => $v) {
            $ModelChannelNotice->setField($k, $v);
        }

        try {
            $updRet = $ModelChannelNotice->saveToDB();
            \Sooh2\Misc\Loger::getInstance()->app_trace($ModelChannelNotice->dbWithTablename()->lastCmd());
            if (!$updRet) {
                \Sooh2\Misc\Loger::getInstance()->app_trace('upd table ModelChannelNotice failed. last sql:');
                \Sooh2\Misc\Loger::getInstance()->app_trace($ModelChannelNotice->dbWithTablename()->lastCmd());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            \Sooh2\Misc\Loger::getInstance()->app_trace($e->getMessage());
            return false;
        }
    }

    protected function getContractidByChannelAppid($channelName, $appid = '')
    {
        $channelName = strtolower($channelName);
        $conf = \Sooh2\Misc\Ini::getInstance()->getIni('jifenqiang');
        if (isset($conf[$channelName])) {
            if (empty($appid)) {
                return $conf[$channelName];
            }
            if (isset($conf[$channelName][$appid])) {
                return $conf[$channelName][$appid];
            }
        }
        return false;
    }

    /**
     * 获取新log/writer接口上线开关
     * @return mixed
     * @author lingtima@gmail.com
     */
    public function getSwitchTimeConf()
    {
        $jifenqiangConf = \Sooh2\Misc\Ini::getInstance()->getIni('jifenqiang');
        return $jifenqiangConf['switchtime'];
    }
}