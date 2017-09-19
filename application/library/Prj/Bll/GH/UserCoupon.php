<?php
/**
 * 国槐的券更新
 * 同步更新国槐的数据库
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/28
 * Time: 14:59
 */

namespace Prj\Bll\GH;

class UserCoupon extends \Prj\Bll\_BllBase
{
    public function add($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['couponInfo','userId','amount','isFloat'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        if(!\Lib\Misc\Result::paramsCheck($params['couponInfo'] , [
            'type','oid','name','description','amountType','investAmount','products','rules','amountType'
        ])){
            return $this->resultError('券参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $couponInfo = $params['couponInfo'];
        $time = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        $finish = date('Y-m-d' , strtotime('+'.$couponInfo['disableDate'].' days'));
        $amount = $params['amount'];
        $isFloat = $params['isFloat'];
        $this->log($couponInfo['products'] , 'products');
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

        try{
            //开启事务
            \Prj\Model\Tulip\UserCoupon::startTransaction();

            if($isFloat){
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
}