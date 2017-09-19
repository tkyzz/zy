<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/27
 * Time: 11:59
 */

namespace Prj\Bll\ActRule;


use Lib\Misc\Result;

class _ActRuleBase extends \Prj\Bll\_BllBase
{
    protected $config = [];

    protected $activityTypes;

    /**
     * Hand 根据规则获取奖励的券
     * @param $amount
     * @param $rules
     * @return string
     */
    protected function getCouponId($amount , $rules){
        \Prj\Loger::out('投资金额: [amount: '. $amount .']');
        $couponRule = $rules;
        foreach ($couponRule as $k => $v){
            $amountArr = explode('_' , $k);
            if($amount >= $amountArr[0] && $amount < $amountArr[1]){
                \Prj\Loger::out('符合区间: '.$amountArr[0].' ~ '.$amountArr[1]);
                return $v;
            }
            continue;
        }
        \Prj\Loger::out('投资金额未达到奖励标准!');
        return '';
    }

    /**
     * 从订单里提取奖励信息
     * @param array $params
     * @return array
     */
    protected function getCouponInfoByOrder($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['orderAmount'])){
            return \Lib\Misc\Result::get(RET_ERR , '参数错误['.\Lib\Misc\Result::$errorParam.']');
        }
        $couponId = $this->getCouponId($params['orderAmount'] , $this->config['rules']);
        if(empty($couponId))return Result::get(RET_ERR , '投资金额未达到要求!');
        $couponInfoRes = \Prj\Bll\Coupon::fa()->getRecords(['oid' => $couponId]);
        if(!\Lib\Misc\Result::check($couponInfoRes))return $couponInfoRes;
        $couponTpl = $couponInfoRes['data'][0];
        \Prj\Loger::out('couponId:' .$couponTpl['oid'] . '#卡券状态：'.$couponTpl['status']);
        // \Prj\Loger::out($couponTpl);
        if($couponTpl['status'] == 'no')return \Lib\Misc\Result::get(RET_SUCC , '券已经失效不可用！');
        $this->initCoupon($couponTpl);
        $couponInfo = $this->config['couponInfo'];
        \Prj\Loger::out('券信息: '.json_encode($couponInfo , 256));
        return \Lib\Misc\Result::get(RET_SUCC , '' , [
            'info' => $couponInfo,
        ]);
    }

    /**
     * 初始化卡券信息
     * @param $couponTpl
     */
    protected function initCoupon($couponTpl){
        $this->config['couponInfo'] = [
            'oid' => $couponTpl['oid'],
            'name' => $couponTpl['name'],
            'description' => $couponTpl['description'],
            'investAmount' => $couponTpl['investAmount'],
            'productList' => $this->getProductList($couponTpl['labels']),
            'disableDate' => $couponTpl['disableDate'],
            'upperAmount' => $couponTpl['upperAmount'] * 100,
        ];
    }

    /**
     * 获取券的使用产品范围
     * @param $labels
     * @return array
     */
    protected function getProductList($labels){
        $tmp = [];
        foreach($labels as $v){
            $label = \Prj\Model\Mimosa\Label::getCopy($v);
            $label->load();
            if(!$label->exists())$this->fatalErr('lablelId: '.$v.' does not exists !');
            $tmp[] = ['productCode' => $label->getField('labelCode'), 'productName' => $label->getField('labelName')];
        }
        return $tmp;
    }

    /**
     * 根据产品ID获取相关奖励信息
     * @param $productId
     * @return array
     */
    public function getRewardInfo($productId , $eventId){
        $this->initRule($eventId);
        $bool = $this->checkLabel($productId);
        if(!$bool)return \Lib\Misc\Result::get(RET_ERR , '产品标签不符合！');
        $orderInfoRes = $this->getOrderByProductId($productId);
        if(!\Lib\Misc\Result::check($orderInfoRes))return $orderInfoRes;
        $orderInfo = $orderInfoRes['data']['info'];
        \Prj\Loger::setOrderId($orderInfo['oid']);
        $couponInfoRes = $this->getCouponInfoByOrder($orderInfo);
        if(!\Lib\Misc\Result::check($couponInfoRes))return $couponInfoRes;
        $couponInfo = $couponInfoRes['data']['info'];
        $miUser = \Prj\Model\MimosaUser::getUserByMiUserId($orderInfo['investorOid']);
        if(empty($miUser)){
            \Prj\Loger::out('用户信息异常![investorOid: '.$orderInfo['investorOid'].']');
            return \Lib\Misc\Result::get(RET_ERR , '用户信息异常!');
        }
        \Prj\Loger::setUid($miUser['userOid']);
        $data = [
            'orderInfo' => $orderInfo,
            'couponInfo' => $couponInfo,
            'userOid' => $miUser['userOid'],
        ];
        return \Lib\Misc\Result::get(RET_SUCC , '' , $data);
    }

    /**
     * 初始化活动规则
     * @param $eventId
     */
    protected function initRule($eventId){
        $coupon = \Prj\Model\Activity::getCopy($eventId);
        $coupon->load();
        $rules = $coupon->getField('rules');
        //\Prj\Loger::out($rules);
        $this->config['rules'] = $rules;
        $this->config['labels'] = explode(',' , $coupon->getField('labels'));
        \Prj\Loger::out('活动限定标签：'.$coupon->getField('labels'));
    }

    /**
     * 限定标签检查
     * @param $productId
     * @return bool
     */
    protected function checkLabel($productId){
        if(empty($this->config['labels'])){
            \Prj\Loger::out('活动限定的标签为空！！！' , LOG_WARNING);
            return true;
        }
        $proLabels = \Prj\Model\Mimosa\LabelProduct::getLabels($productId);
        \Prj\Loger::out('产品标签：'. implode(',' , $proLabels));
        if(empty($proLabels))return false;
        $check = array_intersect($proLabels , $this->config['labels']);
        if(empty($check)){
            return false;
        }else{
            \Prj\Loger::out('符合标签：'.implode(',' , $check));
            return true;
        }
    }

    /**
     * 给订单打标记
     * @param $orderId
     * @return array
     */
    public function setActivityTypes($orderId){
        if(empty($this->activityTypes))return \Lib\Misc\Result::get(RET_ERR , '标志配置为空！');
        $res = \Prj\Model\MimosaTradeOrder::setActivityTypes($orderId , $this->activityTypes);
        if(!$res){
            \Prj\Loger::out('订单：'.$orderId.' 打标记. '.$this->activityTypes .' 失败！', LOG_ERR);
            return \Lib\Misc\Result::get(RET_ERR , '打标记失败！');
        }else{
            \Prj\Loger::out('订单：'.$orderId.' 打标记. '.$this->activityTypes .' 成功！');
            return \Lib\Misc\Result::get(RET_SUCC);
        }
    }

}