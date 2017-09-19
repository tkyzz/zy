<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-20 14:59
 */

namespace Prj\Events;

class DeviceactivationOk extends \Sooh2\EvtQue\EvtProcess
{
    public $deviceType;
    public $deviceId;
    public $deviceContractId;

    public function onEvt()
    {
        \Prj\Loger::out('begin refill device');
        $this->parseObjId($this->evtData->objId);
        if (strtolower($this->deviceType) == 'idfa') {
            $this->refill();
        }
        \Prj\Loger::out('end refill device');
    }


    /**
     * 反填设备相关的信息
     * @author lingtima@gmail.com
     */
    public function refill()
    {
        $notice = $this->checkReport($this->deviceId, $this->deviceContractId);
        if ($notice) {
            \Prj\Bll\Channel\Factory::getFactory($notice['channelName'])->reportActivation(strtolower($this->deviceType), $this->deviceId, $this->deviceContractId, $notice);
            return true;
        }

        return false;
    }

    /**
     * 解析渠道号
     * @param $objId
     * @return array
     * @author lingtima@gmail.com
     */
    public function parseObjId($objId)
    {
        $arr = explode(':', $objId);
        list($this->deviceType, $this->deviceId, $this->deviceContractId) = explode(':', $objId);
        return $arr;
    }

    /**
     * 返回需要上报的那条notice
     * @param $idfa
     * @return bool|mixed
     * @author lingtima@gmail.com
     */
    public function checkReport($idfa, $contractId)
    {
        $_DBChannelNotice = \Prj\Model\ChannelNotice::getCopy('')->dbWithTablename();
        $noticeList = $_DBChannelNotice->getRecords($_DBChannelNotice->kvobjTable(), '*', ['idfa' => $idfa]);

        if ($noticeList && is_array($noticeList)) {
            if (count($noticeList) != 1) {
                \Prj\Loger::out('多个渠道通知过此idfa');
                $canFlag = true;
                $earliestTime = time();
                foreach ($noticeList as $k => $v) {
                    if ($v['callbackStatus'] == 1) {
                        \Prj\Loger::out('此渠道尝试上报过，至此放弃上报');
                        $canFlag = false;
                        break;
                    }
                    if (strtotime($v['createTime']) < $earliestTime) {
                        $earliestTime = strtotime($v['createTime']);
                        $canFlag = $k;
                    }
                }
                if ($canFlag !== false) {
                    \Prj\Loger::out('挑选通知日期最早的渠道上报');
                    return $noticeList[$canFlag];
                }
                return false;
            }
            $notice = $noticeList[0];
            if ($notice['callbackStatus'] == 1) {
                \Prj\Loger::out('此idfa曾经上报过，从此中断，不再上报');
                return false;
            }
            return $notice;
        } else {
            \Prj\Loger::out('未找到此idfa的上报记录，从此中断，不再上报');
            return false;
        }
    }
}