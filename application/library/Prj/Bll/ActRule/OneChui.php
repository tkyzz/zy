<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/27
 * Time: 11:59
 */

namespace Prj\Bll\ActRule;


class OneChui extends \Prj\Bll\ActRule\_ActRuleBase
{
    protected $activityTypes = 'hammer';

    protected $config = [
        'rules' => [
            '0_0' => '', //0~5000 送的红包
        ],
        'labels' => [],
        'couponInfo' => [
            'name' => '',
            'desc' => '',
            'investAmount' => 0,
            'productList' => [],
            'disableDate' => 0,
        ],
    ];

    /**
     * 获取符合规则的订单
     * @param $productId
     * @return array
     */
    protected function getOrderByProductId($productId){
        //找出最后一条订单
        $orderInfo = \Prj\Model\MimosaTradeOrder::getLastSucOrderByProId($productId);
        if(empty($orderInfo)){
            \Prj\Loger::out('[一锤定音]查询不到相关的订单[productId:'. $productId .']!' , LOG_ERR);
            return \Lib\Misc\Result::get(RET_ERR , '查询不到相关的订单!');
        }
        \Prj\Loger::out('订单ID: '.$orderInfo['oid']);
        $res = $this->setActivityTypes($orderInfo['oid']);
        return \Lib\Misc\Result::get(RET_SUCC , '' , [
            'info' => $orderInfo,
        ]);
    }

}