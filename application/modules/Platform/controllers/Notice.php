<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-17 11:55
 */

class NoticeController extends \Prj\Framework\Ctrl
{
    /**
     * 输入格式形如 {'data':[],'message':'','code':10000}
     * Hand 输出数组
     * @param $result
     */
    protected function outPut($result , $log = false){
//        $arr = $this->formatResult($result);
        if($log) {
            \Prj\Loger::outVal('outPut' , $result);
        }
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        header('Content-type: application/json');
        echo \Sooh2\Util::toJsonSimple($result);
    }

    /**
     * 爱思助手通知接口-由爱思助手调用
     * @return int
     * @author lingtima@gmail.com
     */
    public function aisinoticeAction()
    {
        $channelName = 'Aisi';
        $params = [
            'appid' => $this->_request->get('appid'),//应用唯一标识，对接APP标识，双方约定。这里用app的appStore
            'mac' => $this->_request->get('mac', ''),
            'idfa' => $this->_request->get('idfa'),
            'openudid' => $this->_request->get('openudid',''),
            'os' => $this->_request->get('os', ''),
            'callback' => $this->_request->get('callback'),
            'channelName' => $channelName,
        ];

        if (empty($params['appid']) || empty($params['idfa']) || empty($params['callback'])) {
            $this->outPut(['success' => 'false', 'message' => '参数不合法'], true);
            return 0;
        }

        $idfa = $params['idfa'];
        $appid = $params['appid'];
        unset($params['channelName'], $params['idfa'], $params['appid']);
        if (\Prj\Bll\Channel\Factory::getFactory($channelName)->notice($channelName, $idfa, $appid, $params)) {
            $this->outPut(['success' => 'true', 'message' => '成功'], true);
        } else {
            $this->outPut(['success' => 'false', 'message' => '失败'], true);
        }
    }

    /**
     * 模拟爱思的回调接口，仅返回消息不做任何处理
     * @return int
     * @author lingtima@gmail.com
     */
    public function aisicallbackAction()
    {
        $params = [
            'aisicid' => $this->_request->get('aisicid'),
            'aisi' => $this->_request->get('aisi'),
            'appid' => $this->_request->get('appid'),
            'mac' => $this->_request->get('mac'),
            'idfa' => $this->_request->get('idfa'),
            'os' => $this->_request->get('os'),
            'rt' => $this->_request->get('rt'),
        ];

        \Sooh2\Misc\Loger::getInstance()->app_trace($params);
        $this->outPut(['success' => 'true', 'message' => '这里是返回消息'], true);
        return 1;
    }

    /**
     * 懒猫试玩-通知接口
     * @author lingtima@gmail.com
     */
    public function lanmaonoticeAction()
    {
        $channelName = 'Lanmao';
        $params = [
            'appid' => $this->_request->get('appid'),
            'adid' => $this->_request->get('adid'),
            'idfa' => $this->_request->get('idfa'),
            'ip' => $this->_request->get('ip'),
            'channelName' => $channelName,
        ];
        if (empty($params['appid']) || empty($params['idfa'])) {
            $this->outPut(['success' => 'false', 'message' => '参数不合法']);
            return 0;
        }

        $idfa = $params['idfa'];
        $appid = $params['appid'];
        unset($params['channelName'], $params['idfa'], $params['appid']);
        if (\Prj\Bll\Channel\Factory::getFactory($channelName)->notice($channelName, $idfa, $appid, $params)) {
            $this->outPut(['success' => 'true', 'message' => '成功'], true);
        } else {
            $this->outPut(['success' => 'false', 'message' => '失败'], true);
        }
    }

    /**
     * 懒猫试玩-排重接口
     * @return int 1已经激活，0未激活
     * @author lingtima@gmail.com
     */
    public function lanmaocallbackAction()
    {
        $channelName = 'Lanmao';
        $params = [
            'appid' => $this->_request->get('appid'),
            'idfa' => $this->_request->get('idfa'),
        ];

        if (strpos($params['idfa'], ',')) {
            $idfa = explode(',', $params['idfa']);
        } else {
            $idfa[] = $params['idfa'];
        }

        \Prj\Loger::out($idfa);
        $data = [];
        foreach ($idfa as $v) {
            $_DBChannelNotice = \Prj\Model\ChannelNotice::getCopy('')->dbWithTablename();
            $noticeList = $_DBChannelNotice->getRecords($_DBChannelNotice->kvobjTable(), '*', ['idfa' => $v]);
            if (!empty($noticeList)) {
                foreach ($noticeList as $kk => $vk) {
                    if ($vk['callbackStatus'] == 1) {
                        $data[$v] = 1;
                        break;
                    }
                }
            }

            if (!isset($data[$v])) {
                $_DBChannelActivatingTmp = \Prj\Model\ChannelActivatingTmp::getCopy('')->dbWithTablename();
                $ChannelActivatingTmpList = $_DBChannelActivatingTmp->getRecords($_DBChannelActivatingTmp->kvobjTable(), '*', ['idfa' => $v]);
                if (!empty($ChannelActivatingTmpList)) {
                    $data[$v] = 1;
                } else {
                    $data[$v] = 0;
                }
            }
        }

        $this->outPut($data, true);
        return 1;
    }

    /**
     * 来赚通知上报
     * @author lingtima@gmail.com
     */
    public function laizhuannoticeAction()
    {
        $channelName = 'Laizhuan';
        $params = [
            'appid' => $this->_request->get('appid'),
            'source' => $this->_request->get('source'),//渠道来源标识，如laizhuan
            'ip' => $this->_request->get('ip'),
            'idfa' => $this->_request->get('idfa'),
            'channelName' => $channelName,
        ];
        if (empty($params['appid']) || empty($params['idfa'])) {
            $this->outPut(['success' => 'false', 'message' => '参数不合法']);
            return 0;
        }

        $idfa = $params['idfa'];
        $appid = $params['appid'];
        unset($params['channelName'], $params['idfa'], $params['appid'], $params['source']);
        if (\Prj\Bll\Channel\Factory::getFactory($channelName)->notice($channelName, $idfa, $appid, $params)) {
            $this->outPut(['success' => 'true', 'message' => 'success'], true);
        } else {
            $this->outPut(['success' => 'false', 'message' => 'fail'], true);
        }
    }

    /**
     * 来赚排重
     * 1未激活，0已激活
     * @author lingtima@gmail.com
     */
    public function laizhuancallbackAction()
    {
        $channelName = 'Laizhuan';
        $params = [
            'appid' => $this->_request->get('appid'),
            'idfa' => $this->_request->get('idfa'),
        ];

        $_DBChannelNotice = \Prj\Model\ChannelNotice::getCopy('')->dbWithTablename();
        $noticeList = $_DBChannelNotice->getRecords($_DBChannelNotice->kvobjTable(), '*', ['idfa' => $params['idfa']]);
        if (!empty($noticeList)) {
            foreach ($noticeList as $k => $v) {
                if ($v['callbackStatus'] == 1) {
                    $data['status'] = 0;
                    break;
                }
            }
        }

        if (!isset($data)) {
            $data['status'] = 1;
        }
        $this->outPut($data, true);
        return 1;
    }
}