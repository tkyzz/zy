<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/30
 * Time: 13:32
 */

namespace Prj\Bll;

use Lib\Misc\Result;

class BaobaoTree extends \Prj\Bll\_BllBase
{
    protected function init(){
        \Prj\Loger::$prefix .= '[BaobaoTree]';
    }

    public function sendOrder($orderId){
        $this->log('orderId: '.$orderId);
        $orderInfo = \Prj\Model\MimosaTradeOrder::getOne(['oid' => $orderId]);
        $res = $this->check($orderInfo);
        if(!$this->checkRes($res))return $res;

        //开始上报订单
        $res = \Lib\Services\BaobaoTree::getInstance()->sendOrder($orderInfo);
        return $res;
    }

    protected function check($orderInfo){
        if(!count($orderInfo)){
            $this->fatalErr('未查询到订单信息!');
        }

        $this->log('订单 orderType: '.$orderInfo['orderType'].' orderStatus: '.$orderInfo['orderStatus']);
        if(!in_array($orderInfo['orderStatus'] , \Prj\Model\MimosaTradeOrder::$orderStatus_success)){
            return $this->resultError('订单尚未交易成功!');
        }

        $miUser = \Prj\Model\MimosaUser::getUserByMiUserId($orderInfo['investorOid']);
        if(empty($miUser)){
            $this->fatalErr('未查询到投资者信息!');
        }
        $ucUser = \Prj\Model\User::getOne(['oid' => $miUser['userOid']]);
        if(empty($ucUser)){
            $this->fatalErr('未查询到用户信息!');
        }
        $this->log('用户 uid: '.$ucUser['oid'].' phone: '.$ucUser['userAcc'] . ' channelid: '.$ucUser['channelid']);
        $iniChannels = \Sooh2\Misc\Ini::getInstance()->getIni('BaobaoTree.pro.channels');
        $this->log('宝宝树渠道: '.$iniChannels);
        $channels = explode(',' , $iniChannels);
        if(!in_array($ucUser['channelid'] , $channels) )return $this->resultError('非宝宝树渠道用户 pass !');
        return $this->resultOK();
    }
}