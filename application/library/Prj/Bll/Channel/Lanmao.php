<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-18 11:33
 */

namespace Prj\Bll\Channel;

class Lanmao extends Base
{
    public function callback($url)
    {
        return false;
    }

    /**
     * 已经作废
     * @param $noticeId
     * @return bool
     * @author lingtima@gmail.com
     */
    public function channelCallback($noticeId)
    {
        //更新notice表
        $ModelChannelNotice = \Prj\Model\ChannelNotice::getCopy($noticeId);
        $ModelChannelNotice->load();
        if (!$ModelChannelNotice->exists()) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('cant found record from ModelChannelNotice by id:' . $noticeId);
            return false;
        }
        $ModelChannelNotice->setField('callbackStatus', 1);
        $ModelChannelNotice->setField('callbackRet', 1);
        $ModelChannelNotice->setField('callbackCreateTime', date('Y-m-d H:i:s', time()));
        $ModelChannelNotice->saveToDB();

        //更新deviceContractid表
        $ModelDeviceContractid = \Prj\Model\DeviceContractid::getCopy('idfa', $ModelChannelNotice->getField('idfa'), $ModelChannelNotice->getField('contractId'));
        $ModelDeviceContractid->load();
        if (!$ModelDeviceContractid->exists()) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('cant found record from ModelDeviceContractid by pkey:(idfa:' . $ModelChannelNotice->getField('idfa') . ':' . $ModelChannelNotice->getField('contractId'));
            return false;
        }
        $ModelDeviceContractid->setField('callbackRet', 1);//上报成功
        $ModelDeviceContractid->setField('callbackTime', date('Y-m-d H:i:s', time()));
        $ModelDeviceContractid->saveToDB();

        return true;
    }

    public function notice($channelName, $idfa, $appid, $args)
    {
        if (empty($args)) {
            return false;
        }

        $ModelChannelNotice = \Prj\Model\ChannelNotice::getCopyByAppidAndIdfa($appid, $idfa);
        $ModelChannelNotice->load();
        if ($ModelChannelNotice->exists()) {
            //已经通知过
            return true;
        } else {
            $ModelChannelActivatingTmp = \Prj\Model\ChannelActivatingTmp::getCopyByAppidIdfa($appid, $idfa);
            $ModelChannelActivatingTmp->load();
            if ($ModelChannelActivatingTmp->exists()) {
                return false;
            }
        }

        $ModelChannelNotice->setField('createTime', date('Y-m-d H:i:s'));
        $ModelChannelNotice->setField('channelName', $channelName);
        foreach ($args as $k => $v) {
            $ModelChannelNotice->setField($k, $v);
        }
        $ret = $ModelChannelNotice->saveToDB();
        if ($ret) {
            $ModelChannelNotice->load(true);
            $ret =  $ModelChannelNotice->getField('id');//新增
        }

        return $ret;
    }
}