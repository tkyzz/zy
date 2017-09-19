<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/27
 * Time: 11:59
 */

namespace Prj\Bll\ActRule;


class YiMing extends \Prj\Bll\ActRule\_ActRuleBase
{
    protected $activityTypes = 'famous';

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
        $where = [
            'orderType' => 'invest',
            'orderStatus' => \Prj\Model\MimosaTradeOrder::$orderStatus_success,
            'productOid' => $productId,
        ];
        $record = \Prj\Model\MimosaTradeOrder::getRecord(null , $where , 'rsort orderAmount rsort createTime');
        if(empty($record)){
            \Prj\Loger::out('异常: 产品['.$productId.']查无符合[一鸣惊人]条件的订单!' , LOG_ERR);
            return \Lib\Misc\Result::get(RET_ERR , '查无订单');
        }
        \Prj\Loger::out('订单ID: '.$record['oid']);
        $res = $this->setActivityTypes($record['oid']);
        return \Lib\Misc\Result::get(RET_SUCC , '' , [
            'info' => $record,
        ]);
    }

}