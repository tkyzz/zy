<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-13 16:34
 */

namespace Prj\Events;

class ClientTransOk extends \Sooh2\EvtQue\EvtProcess
{
    public function onEvt()
    {
        //取消
//        \Prj\Loger::out('begin aisi_callback');
//        $this->channel_callback();
//        \Prj\Loger::out('end aisi_callback');
    }

    /**
     * 爱思助手上报信息
     * @author lingtima@gmail.com
     */
    public function channel_callback()
    {
        $ModelClientTransparent = \Prj\Model\ClientTransparent::getCopy($this->evtData->objId);
        $ModelClientTransparent->load();
        if ($ModelClientTransparent->exists()) {
            $clientContent = $ModelClientTransparent->getField('content');
            \Prj\Loger::out('client_transparent table->content:');
            \Prj\Loger::out($clientContent);
            if ($clientContent) {
                if (isset($clientContent['IDFA']) && $idfa = $clientContent['IDFA']) {
                    \Prj\Loger::out('idfa:' . $idfa);
//                    \Prj\Bll\Channel\Factory::getFactory('Base')->report($this->evtData->userId, $idfa);
                    $notice = $this->checkReport($idfa);
                    if ($notice) {
                        \Prj\Bll\Channel\Factory::getFactory($notice['channelName'])->report($this->evtData->userId, $idfa, $notice);
                        return true;
                    }
                }
            } else {
                return false;
            }
        } else {
            \Prj\Loger::out('cant found objId in client_transparent table, objId:' . $this->evtData->objId);
        }
        return false;
    }

    protected function checkReport($idfa)
    {
        $_DBChannelNotice = \Prj\Model\ChannelNotice::getCopy('')->dbWithTablename();
        $noticeList = $_DBChannelNotice->getRecords($_DBChannelNotice->kvobjTable(), '*', ['idfa' => $idfa]);

        if ($noticeList && is_array($noticeList)) {
            if (count($noticeList) != 1) {
                //多个渠道上报过此idfa，这里直接放弃
                \Prj\Loger::out('多个渠道通知过此idfa，从此中断，不再上报');
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