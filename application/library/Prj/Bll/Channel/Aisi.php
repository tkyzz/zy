<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-18 11:32
 */

namespace Prj\Bll\Channel;

class Aisi extends Base
{
    public $doCallback = true;

    public function notice($channelName, $idfa, $appid, $args)
    {
        $ret = parent::notice($channelName, $idfa, $appid, $args);
        if ($ret && time() < $this->getSwitchTimeConf()) {
            //默认激活，直接执行激活回调
            //仅仅更新channel_notice与device_contractId表
            $channelNoticeParams = [];

            $ModelChannelNotice = \Prj\Model\ChannelNotice::getCopy($ret);
            $ModelChannelNotice->load(true);
            $channelNoticeParams['callbackStatus'] = 1;
            $channelNoticeParams['callbackCreateTime'] = date('Y-m-d H:i:s', time());
            if ($ModelChannelNotice->exists() && !empty($ModelChannelNotice->getField('callback'))) {
                if ($this->callback($ModelChannelNotice->getField('callback'))) {
                    $channelNoticeParams['callbackRet'] = 1;
                }
            }

            $channelNoticeParams['contractId'] = $this->getContractidByChannelAppid($channelName, $appid);
            $this->updChannelNotice($ret, $channelNoticeParams);
        }

        return $ret;
    }
}