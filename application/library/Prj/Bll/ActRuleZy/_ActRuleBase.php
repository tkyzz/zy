<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/3
 * Time: 17:43
 */
namespace Prj\Bll\ActRuleZy;

class _ActRuleBase extends \Prj\Bll\_BllBase
{
    protected $actInfo;

    protected $userId;

    protected $activityTypes;

    protected $orderInfo;

    protected function _init($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['actOid'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $actId = $params['actOid'];
        $actInfo = \Prj\Model\Activity::getOne([
            'oid' => $actId ,
            'actCode' => $this->getActCode(),
        ]);
        if(empty($actInfo))return $this->resultError('活动不存在');
        $res = \Prj\Bll\Event::checkOpenEvent($actInfo);
        if(!$this->checkRes($res))return $res;
        $actInfo['rules'] = json_decode($actInfo['rules'] , true);
        if(empty($actInfo['rules']))return $this->resultError('规则解析失败');
        $actInfo['labels'] = explode(',' , $actInfo['labels']);
        if(!empty($actInfo['labels'])){
            $proLabels = \Prj\Model\ZyBusiness\ProductLabel::getLabels($params['productId']);
            if(empty(array_intersect($proLabels , $actInfo['labels']))){
                $this->log($proLabels , '产品标签');
                $this->log($actInfo['labels'] , '活动标签');
                return $this->resultError('标签不匹配');
            }
        }
        $this->actInfo = $actInfo;
        return $this->resultOK();
    }

    protected function getActCode(){
        throw new \Exception('重写');
    }

    protected function getAmount($params = []){
        throw new \Exception('重写');
    }

    public function getCouponInfo($params = []){
        $res = $this->_init($params);
        if(!$this->checkRes($res))return $res;
        $amountRes = $this->getAmount($params);
        if(!$this->checkRes($res))return $amountRes;
        $amount = $amountRes['data']['amount'];
        $rules = $this->actInfo['rules'];
        \Prj\Loger::out('投资金额: [amount: '. $amount .']');

        foreach ($rules as $k => $v){
            $amountArr = explode('_' , $k);
            if($amount >= $amountArr[0] && $amount < $amountArr[1]){
                \Prj\Loger::out('符合区间: '.$amountArr[0].' ~ '.$amountArr[1]);
                if(!empty($this->activityTypes)){
                    if(empty($this->orderInfo)){
                        return $this->resultError('缺少订单信息');
                    }
                    \Prj\Loger::setKv('orderNo' , $this->orderInfo['orderNo']);
                    $ret = \Prj\Model\ZyBusiness\TradOrder::updActTag($this->orderInfo['orderId'] , $this->activityTypes);
                    if($ret){
                        $this->log('订单标记添加成功#' . $this->activityTypes);
                    }else{
                        \Prj\Loger::out('订单标记添加失败' , LOG_ERR);
                    }
                }
                \Prj\Loger::setKv('couponId' , $v);
                $data = [
                    'couponId' => $v,
                    'userId' => $this->userId,
                ];
                if($this->orderInfo){
                    $data['orderInfo'] = $this->orderInfo;
                }
                return $this->resultOK($data);
            }
            continue;
        }
        return $this->resultError('投资金额不满足条件');
    }
}