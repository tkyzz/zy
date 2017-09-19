<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 14:18
 */

namespace Prj\Bll\ZY;

class Coupon extends \Prj\Bll\_BllBase
{
    /**
     * Hand 根据标签查询可用的券
     * @param array $labelIds
     * @return array
     */
    public function getCouponIdByLabelCodes($labelIds = []){
        if(empty($labelIds))return $this->resultError('标签列表不能为空');
        $list = \Prj\Model\ZyBusiness\CouponLabel::getRecords('' , [
            'labelId' => $labelIds,
        ]);
        if(empty($list))return $this->resultOK(['list' => []]);
        foreach ($list as $v){
            $info[] = $v['couponId'];
        }
        return $this->resultOK(['list' => $info]);
    }

    /**
     * 给用户添加一张卡券
     * 必要参数 'couponInfo','userId'
     * couponInfo 必要参数 'type','oid','name','description','amountType','investAmount','products','rules','amountType'
     * @param array $params
     * @return array
     */
    protected function addUserCoupon($params = []){
        if(!isset($params['eventId'])){
            $params['eventId'] = $params['eventTitle'] = '';
        }
        if(!\Lib\Misc\Result::paramsCheck($params , ['couponInfo','userId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        if(!\Lib\Misc\Result::paramsCheck($params['couponInfo'] , [
            'type','oid','name','description','amountType','investAmount','products','rules','amountType'
        ])){
            return $this->resultError('券参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $couponInfo = $params['couponInfo'];
        if($this->isFloadCoupon($couponInfo)){
            if($params['amount'] <= 0)return $this->resultError('浮动券必须指定金额');
            $amount = $params['amount'];
        }else{
            $amount = $couponInfo['upperAmount'];
        }

        $time = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        $finish = date('Y-m-d' , strtotime('+'.$couponInfo['disableDate'].' days'));

        $insertData = [
            'oid' => \Lib\Misc\StringH::createOid('ucoupon'),
            'userId' => $params['userId'],
            'couponBatch' => $couponInfo['oid'],
            'leadTime' => $time,
            'status' => 'notUsed',
            'name' => $couponInfo['name'],
            'description' => $couponInfo['description'],
            'start' => $date,
            'finish' => $finish,
            'amount' => $amount,
            'amountType' => $couponInfo['amountType'],
            'eventId' => $params['eventId'],
            'eventTitle' => $couponInfo['name'],
            'type' => $couponInfo['type'],
            'investAmount' => $couponInfo['investAmount'],
            'products' => $couponInfo['products'],
            'rules' => $couponInfo['rules'],
            'eventTitle' => $couponInfo['name'],
        ];

        //更新 remainCount remainAmount
        try{
            //开启事务
            \Prj\Model\Tulip\UserCoupon::startTransaction();

            if($this->isFloadCoupon($couponInfo)){
                $ret = \Prj\Model\TulipCoupon::updateNum($couponInfo['oid'] , 'remainAmount' , -1 * $amount);
                if(!$ret)$this->fatalErr('卡券余额扣减失败');
            }
            //扣减卡券数量
            $ret = \Prj\Model\TulipCoupon::updateNum($couponInfo['oid'] , 'remainCount' , -1);
            if(!$ret)$this->fatalErr('卡券数量扣减失败');
            //添加用户卡券
            $ret = \Prj\Model\Tulip\UserCoupon::saveOne($insertData);
            if(!$ret)$this->fatalErr('用户添加卡券失败');
            //提交
            \Prj\Model\Tulip\UserCoupon::commit();
        }catch (\Exception $e){
            //回滚
            \Prj\Model\Tulip\UserCoupon::rollback();
            return $this->resultError($e->getMessage());
        }
        \Prj\Loger::out('添加卡券成功!');
        return $this->resultOK([
            'info' => $insertData
        ]);
    }

    /**
     * Hand 获取卡券的标签 ZyBusiness
     * @param $couponId
     * @return array
     */
    public function getCouponLabels($couponId){
        if(empty($couponId)){
            return $this->resultError('参数错误#couponId');
        }

        $list = \Prj\Model\ZyBusiness\CouponLabel::getRecords(null  , ['couponId' => $couponId]);
        $this->log($list , 'list');
        // if(empty($list))return $this->resultError($couponId. ' 查无标签数据');
        foreach ($list as &$v){
            $labelInfo = \Prj\Model\ZyBusiness\SystemLabel::getRecord(null , ['labelId' => $v['labelId']]);
            if(empty($labelInfo))return $this->resultError('缺少标签数据');
            $v = array_merge($v , $labelInfo);
        }
        return $this->resultOK([
            'list' => $list,
        ]);
    }

    /**
     * Hand 获取卡券标签详情 ZyBusiness
     * @param $couponId
     * @return array
     */
    public function getCouponLabelsDetail($couponId){
        $labelNames = $labelNos = $labelIds = [];
        $listRes = $this->getCouponLabels($couponId);
        if(!$this->checkRes($listRes))return $listRes;
        $list = $listRes['data']['list'];
        if(!empty($list)){
            foreach ($list as $v){
                $labelNames[] = $v['labelName'];
                $labelNos[] = $v['labelNo'];
                $labelIds[] = $v['labelId'];
            }
        }
        return $this->resultOK([
            'labelNames' => $labelNames,
            'labelNos' => $labelNos,
            'labelIds' => $labelIds,
        ]);
    }
}