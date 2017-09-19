<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/30
 * Time: 13:32
 */

namespace Prj\Bll;

use Lib\Misc\Result;

class BaobaoTreeZy extends \Prj\Bll\_BllBase
{
    protected function init(){
        \Prj\Loger::$prefix .= '[BaobaoTreeZy]';
    }

    public function sendOrder($orderId){
        $this->log('orderNo: '.$orderId);
        $orderInfo = \Prj\Model\ZyBusiness\TradOrder::getOne(['orderNo'=>$orderId]);
//        $orderInfo = \Prj\Model\MimosaTradeOrder::getOne(['oid' => $orderId]);
        $res = $this->check($orderInfo);
        if(!$this->checkRes($res))return $res;

        //开始上报订单
        $res = \Lib\Services\BaobaoTree::getInstance()->sendOrder($orderInfo);
        return $res;
    }

    protected function check($orderInfo){
        if(empty($orderInfo)){
            $this->fatalErr('未查询到订单信息!');
        }

        $this->log('订单 orderType: '.$orderInfo['orderType'].' orderStatus: '.$orderInfo['orderStatus']);

        if($orderInfo['orderStatus']!="CONFIRMED") return $this->resultError('订单尚未交易成功!');


        $ucUser = \Prj\Model\User::getOne(['oid' => $orderInfo['userId']]);
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