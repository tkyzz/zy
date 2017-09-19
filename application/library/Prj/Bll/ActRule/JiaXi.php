<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/27
 * Time: 11:59
 */

namespace Prj\Bll\ActRule;


class JiaXi extends \Prj\Bll\_BllBase
{
    protected $activityTypes = '';

    protected $args;

    protected $config = [
        'rules' => [
            '0_0' => '', //0~5000 送的红包
        ],
        'labels' => [],
        'couponInfo' => [
            'couponType' => 'redPackets',
            'name' => '加息红包',
            'description' => '加息红包',
            'upperAmount' => 0,
            'productList' => [],
            'disableDate' => 7,
        ],
    ];

    protected function initCoupon(){
        \Prj\Loger::out(\Sooh2\Misc\Ini::getInstance()->getIni('Activity.JiaXi.red.name'));
        $this->config['couponInfo']['name'] = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.JiaXi.red.name');
        $this->config['couponInfo']['description'] = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.JiaXi.red.description');
        $this->config['couponInfo']['disableDate'] = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.JiaXi.red.disableDate');
    }

    protected function getRedAmountFromOrder($orderId){
        \Prj\Loger::setOrderId($orderId);
        $order = \Prj\Model\MimosaTradeOrder::getCopy($orderId);
        $order->load(true);
        if(!$order->exists()){
            return $this->resultError('订单信息不存在！');
        }
        $this->args['orderInfo'] = $order->dump();
        try{
            $jzRateCouponIncome = $order->getField('jzRateCouponIncome');
        }catch (\Exception $e){
            \Prj\Loger::out('jzRateCouponIncome: null');
            return $this->resultError('该订单无需发放加息红包！');
        }
        \Prj\Loger::out('jzRateCouponIncome: '.$jzRateCouponIncome);
        if($jzRateCouponIncome <= 0){
            return $this->resultError('该订单无需发放加息红包！');
        }
        return $this->resultOK([
            'amount' => $jzRateCouponIncome,
        ]);
    }

    public function getRewardInfo($orderId){
        $amountRes = $this->getRedAmountFromOrder($orderId);
        if(!$this->checkRes($amountRes))return $amountRes;
        $amount = round($amountRes['data']['amount'] * 100);

        $orderInfo = $this->args['orderInfo'];
        $userInfoRes = \Prj\Bll\User::getInstance()->getUcUserInfoByInvestorId($orderInfo['investorOid']);
        if(!$this->checkRes($userInfoRes))return $userInfoRes;

        $userInfo = $userInfoRes['data']['info'];
        \Prj\Loger::setUid($userInfo['oid']);
        $this->initCoupon();
        $this->config['couponInfo']['upperAmount'] = $amount;
        \Prj\Loger::out('券信息: '.json_encode($this->config['couponInfo'] , 256));
        return $this->resultOK([
            'orderInfo' => $orderInfo,
            'couponInfo' => $this->config['couponInfo'],
            'userOid' => $userInfo['oid'],
        ]);
    }


}