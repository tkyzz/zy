<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/12
 * Time: 17:14
 */
namespace Prj\Bll;

use Prj\Model\UserFinal;

class Rebate extends \Prj\Bll\_BllBase
{
    public static $firstInvestToMineAmount = 1000;
    public static $firstInvestToInviterAmount = 500;

    /**
     * 获取返利详情列表
     * referOid=用户ID userOid=被邀请的ID
     * @param array $params
     * @return array
     */
    public function getRebateDetailList($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['referOid','userOid'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $params['model'] = \Prj\Model\UserRebateDetail::getClassName();
        $params['where'] = [
            'referOid' => $params['referOid'],
            'userOid' => $params['userOid'],
        ];
        return $this->_getList($params);
    }

    /**
     * 获取用户的返利详情
     * @param array $params
     * @return array
     */
    public function getMyUserRebateDetail($params = []){
        $param = [];
        if(!\Lib\Misc\Result::paramsCheck($params , ['referOid','userOid'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $pageRes = $this->getRebateDetailList($params);
        if(!$this->checkRes($pageRes))return $pageRes;

        foreach ((array)$pageRes['data']['content'] as $k => $v){
            $tmp = $v;
            $tmp['amount'] = floatval($tmp['amount']);
            $tmp['createTime'] -= 0;
            $tmp['statusTime'] -= 0;
            if($tmp['status'] == 0){
                $param['totalWaitRebate'] += $tmp['amount'];
            }else{
                $param['totalAmount'] += $tmp['amount'];
            }
            $pageRes['data']['content'][$k] = $tmp;
            \Prj\Loger::outVal('pageRes' , $tmp);
        }

        $page = $pageRes['data'];

        $lastRecord = \Prj\Model\UserRebateDetail::getRecord(null , [
            'referOid' => $params['referOid'],
            'userOid' => $params['userOid'],
            'status' => 1,
        ] , 'rsort createTime');

        $param['lastRebate'] = floatval(isset($lastRecord['amount']) ? $lastRecord['amount'] : 0);
        $param['lastRebateTime'] = $lastRecord['createTime'] - 0;
        $param['showName'] = '***';
        $param['userMobile'] = \Lib\Misc\StringH::hideStr($lastRecord['userMobile'] , 3 , 4);
        $param['userName'] = $lastRecord['userName'];
        $param['userOid'] = '8a9bfa0e5dfbdd23015dfbebf9ff0001';

        return $this->resultOK(compact('page' , 'param'));

    }

    /**
     * 发放返利
     * @param array $orderInfo 订单详情
     * @param array $proInfo 产品详情
     * @return bool
     * @author lingtima@gmail.com
     */
    public function runRebateOnBuy($orderInfo, $proInfo)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('begin ' . __FUNCTION__ . '=========');
        $uid = $orderInfo['userId'];
        $inviteTree = \Prj\Bll\Invite::getInstance()->getUpInviteTree($uid);
        if (!$inviteTree || empty($inviteTree['inviter'])) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('没有邀请关系，end');
            return false;//没有上级邀请人
        }

        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            if ($isFirstBuy = $ModelUserFinal->getField('isTiro')) {
                \Sooh2\Misc\Loger::getInstance()->app_trace('首次投资');
                //发放红包
                $this->giveInviteCoupon($inviteTree['inviter']);
                $this->giveBeInviteCoupon($uid);

                //首次投资
                $userRebateRet = \Prj\Model\InviteRebateInfo::giveRebate($uid, $uid, self::$firstInvestToMineAmount, $orderInfo['orderNo'], $proInfo['productId'], 1, [
                    'couponType' => \Prj\Model\Coupon::type_coupon,
                    'couponInvestAmount' => 600000,
                    'couponName' => '受邀红包',
                    'couponProductList' => [
                        'qitouDay' => 30
                    ],
                ], 1);
                $inviterRebateRet = \Prj\Model\InviteRebateInfo::giveRebate($inviteTree['inviter'], $uid, self::$firstInvestToInviterAmount, $orderInfo['orderNo'], 1, $proInfo['productId'], [
                    'couponType' => \Prj\Model\Coupon::type_coupon,
                    'couponInvestAmount' => 300000,
                    'couponName' => '邀请红包',
                    'couponProductList' => [
                        'qitouDay' => 30
                    ],
                ], 1);
                if ($userRebateRet && $inviterRebateRet) {
                    \Prj\Model\InviteFinal::updateRebate($uid, $uid, self::$firstInvestToMineAmount);
                    \Prj\Model\UserFinal::updateRebate($uid, self::$firstInvestToMineAmount);
//                    \Prj\Model\InviteFinal::updateWaitRebate($uid, $uid, self::$firstInvestToMineAmount);
//                    \Prj\Model\UserFinal::updateWaitRebate($uid, self::$firstInvestToMineAmount);

                    \Prj\Model\InviteFinal::updateRebate($inviteTree['inviter'], $uid, self::$firstInvestToInviterAmount);
                    \Prj\Model\UserFinal::updateRebate($inviteTree['inviter'], self::$firstInvestToInviterAmount);
//                    \Prj\Model\InviteFinal::updateWaitRebate($inviteTree['inviter'], $uid, self::$firstInvestToInviterAmount);
//                    \Prj\Model\UserFinal::updateWaitRebate($inviteTree['inviter'], self::$firstInvestToInviterAmount);
                }
            }

                //是否在1年有效期内
                \Sooh2\Misc\Loger::getInstance()->app_trace('非首次投资');
                if (strtotime('+1 years', strtotime($ModelUserFinal->getField('ymdReg'))) > strtotime($orderInfo['createTime'])) {
                    $rebateAmount = $this->getExceptedRebateAmount($orderInfo['payAmount'] * 100, $proInfo['durationPeriodDays'], $proInfo['incomeCalcBasis']);
                    $ret = \Prj\Model\InviteRebateInfo::giveRebate($inviteTree['inviter'], $uid, $rebateAmount, $orderInfo['orderNo'], $proInfo['productId']);
                    if ($ret) {
                        \Prj\Model\InviteFinal::updateWaitRebate($inviteTree['inviter'], $uid, $rebateAmount);
                        \Prj\Model\UserFinal::updateWaitRebate($inviteTree['inviter'], $rebateAmount);
                    }
                }
            }
        \Sooh2\Misc\Loger::getInstance()->app_trace('end ' . __FUNCTION__ . '=============');
    }

    /**
     * 计算返利金额
     * 返利金额=受邀人净投资额×标的期限÷365天×0.2%
     * @param float $newAmount 净投资额
     * @param int $durationDays 标的期限
     * @param int $incomeCalcDays 年计息天数
     * @param float $rate 利率系数
     * @return int
     * @author lingtima@gmail.com
     */
    public function getExceptedRebateAmount($newAmount, $durationDays, $incomeCalcDays = 365, $rate = 0.002)
    {
        $expectedAmount = floor($newAmount * $durationDays * $rate / $incomeCalcDays);
        \Sooh2\Misc\Loger::getInstance()->app_trace('excepted rebate amount:' . $expectedAmount);
        return $expectedAmount;
    }

    /**
     * Hand 跟新返利信息的状态
     * @param array $params
     * @return array
     */
    public function updateRebateStatus($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['rebateId','userId','status'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $rebateId = $params['rebateId'];
        $userId = $params['userId'];
        $status = $params['status'];
        $msg = $params['message'];
        $rebate = \Prj\Model\InviteRebateInfo::getCopy($rebateId);
        $rebate->load(true);
        if(!$rebate->exists())return $this->resultError('返利信息不存在');
        $rebateFinal = \Prj\Model\InviteFinal::getCopy($userId , $rebate->getField('formUid'));
        $rebateFinal->load(true);
        if(!$rebateFinal->exists())return $this->resultError('返利统计信息不存在');

        try{
            \Prj\Model\InviteRebateInfo::startTransaction();
            if($status == 4){
                $rebate->setField('status' , 4);
                $rebate->setField('ret' , $msg);
                $ret = $rebate->saveToDB();
                if(!$ret)$this->fatalErr('返利信息更新失败!!!'); //返利信息更新失败回滚
            }elseif($status == 1){
                $rebate->setField('status' , 1);
                $ret = $rebate->saveToDB();
                if(!$ret)$this->fatalErr('返利信息更新失败!!!'); //返利信息更新失败回滚
                //更新统计信息
                $rebateFinal->incField('rebateNum' , 1);
                $rebateFinal->incField('rebateWaitNum' , -1);
                $rebateFinal->incField('rebateAmount' , $rebate->getField('amount'));
                $rebateFinal->incField('rebateWaitAmount' , -1 * $rebate->getField('amount'));
                $rebateFinal->setField('lastStatus' , 1);
                $rebateFinal->setField('lastRebateTime' , date('Y-m-d H:i:s'));
                $rebateFinal->setField('lastAmount' , $rebate->getField('amount'));
                $ret = $rebateFinal->saveToDB();
                if(!$ret)$this->fatalErr('返利统计更新失败!!!'); //返利统计更新失败回滚

                //更新统计信息
                $res = \Prj\Bll\UserFinal::getInstance()->setInfo([
                    'uid' => $userId,
                    'rebateNum' => 1,
                    'waitRebateNum' => -1,
                    'rebateAmount' => $rebate->getField('amount'),
                    'rebateWaitAmount' => -1 * $rebate->getField('amount'),
                ]);
                if(!$this->checkRes($res)){
                    \Prj\Loger::out($res['message'] , LOG_ERR);
                }
            }else{
                $this->fatalErr('未知的返利状态更新!!!');
            }

            \Prj\Model\InviteRebateInfo::commit();
            return $this->resultOK();
        }catch (\Exception $e){
            \Prj\Model\InviteRebateInfo::rollback();
            return $this->resultError($e->getMessage());
        }
    }

    /**
     * 发放邀请红包
     * @author lingtima@gmail.com
     */
    public function giveInviteCoupon($uid)
    {
        $sender = \Lib\Services\SendCouponLocal::getInstance();
        $ret = $sender->setCouponId(\Sooh2\Misc\Ini::getInstance()->getIni('coupon.invite.couponId'))->sendCoupon($uid);
        return $ret['code'] == 10000 ? $ret['data']['info'] : false;
    }

    /**
     * 发放受邀红包
     * @author lingtima@gmail.com
     */
    public function giveBeInviteCoupon($uid)
    {
        $sender = \Lib\Services\SendCouponLocal::getInstance();
        $ret = $sender->setCouponId(\Sooh2\Misc\Ini::getInstance()->getIni('coupon.beInvite.couponId'))->sendCoupon($uid);
        return $ret['code'] == 10000 ? $ret['data']['info'] : false;
    }

    /**
     * Hand 发送返利现金红包
     * @param $orderNo
     * @return array
     */
    public function giveRedpacket($orderNo){
        $rebate = \Prj\Model\InviteRebateInfo::getCopy(['orderNo' => $orderNo , 'couponType' => \Prj\Model\Coupon::type_redPackets]);
        $rebate->load();
        if(!$rebate->exists())return $this->resultError('订单无返利');
        if($rebate->getField('status') != 0)return $this->resultError('返利已受理');
        return \Prj\Bll\EventCoupon::getInstance()->sendRebateCoupon($rebate->getField('id'));
    }
}