<?php
/**
 * 用户final和业务相关的业务
 * Author: lingtima@gmail.com
 * Time: 2017-06-29 15:01
 */

namespace Prj\Bll;

class UserFinal extends _BllBase
{
    protected $evt;
    /**
     * 更新final表
     * @param array $params
     * @return array
     */
    public function setInfo($params = []){
        \Prj\Loger::out('【更新用户信息】'.$this->evt. ' ' . json_encode($params , 256));
        if (!isset($params['uid']) || empty($params['uid'])) {
            return $this->resultError('用户ID不能为空');
        }

        $ModelUser = \Prj\Model\User::getCopy($params['uid']);
        $ModelUser->load();
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($params['uid']);
        $ModelUserFinal->load();
        if (!$ModelUser->exists() || !$ModelUserFinal->exists()) {
            return $this->resultError('用户不存在');
        }

        if (isset($params['realName']) && !empty($params['realName'])) { //绑卡
            $ModelUserFinal->setField('nickname', $params['realName']);
            $ModelUserFinal->setField('realname', $params['realName']);
            if(empty($ModelUserFinal->getField('realVerifiedTime')))
                $ModelUserFinal->setField('realVerifiedTime', date('Ymd', time()));
        }
        if (isset($params['payPwd']) && !empty($params['payPwd'])) { //设置支付密码
            $ModelUser->setField('payPwd', 'true');
        }
        if (isset($params['isBindCard']) && $params['isBindCard'] !== '') { //绑卡/解绑
            $ModelUserFinal->setField('isBindCard', $params['isBindCard']);
            if(!$params['isBindCard'])$ModelUserFinal->setField('bindCardId' , '');
        }
        if (isset($params['bindCardId']) && !empty($params['bindCardId'])) { //绑卡
            $ModelUserFinal->setField('bindCardId', $params['bindCardId']);
            if(empty($ModelUserFinal->getField('ymdBindCard')))
                $ModelUserFinal->setField('ymdBindCard', date('Ymd' , $params['bindCardTime'] ? strtotime( $params['bindCardTime'] ) : time() ));

        }
        if (isset($params['bankCardCode']) && !empty($params['bankCardCode'])) { //绑卡
            $ModelUserFinal->setField('bankCardCode', $params['bankCardCode']);
        }
        if (isset($params['rechargeTime']) && !empty($params['rechargeTime'])) { //充值
           if(empty($ModelUserFinal->getField('rechargeTime')))
               $ModelUserFinal->setField('rechargeTime' , $params['rechargeTime']);
        }
        if (isset($params['orderTime']) && !empty($params['orderTime'])) { //购买定期
            if(empty($ModelUserFinal->getField('orderTime')))
                $ModelUserFinal->setField('orderTime', date('Ymd', time()));
        }
        if (isset($params['certNo']) && !empty($params['certNo'])) { //身份证
            $ModelUserFinal->setField('certNo', $params['certNo']);
            //更新性别
            $idcardInfo = \Prj\Tool\IdentityCard::getInstance()->getInfo($params['certNo']);
            $ModelUserFinal->setField('gender', $idcardInfo['sex'] ? 1 : 2);
            $ModelUserFinal->setField('ymdBirthday', date('Ymd', strtotime($idcardInfo['birth'])));
            $ModelUserFinal->setField('addrCode', $idcardInfo['addrCode']);
        }

        if (isset($params['rebateNum']) && $params['rebateNum'] !== ''){ //返利
            $ModelUserFinal->incField('rebateNum', $params['rebateNum']);
        }

        if(isset($params['rebateAmount']) && $params['rebateAmount'] !== ''){ //返利
            $ModelUserFinal->incField('rebateAmount', $params['rebateAmount']);
        }

        if(isset($params['waitRebateNum']) && $params['waitRebateNum'] !== ''){ //返利
            $ModelUserFinal->incField('waitRebateNum' , $params['waitRebateNum']);
        }

        if(isset($params['waitRebateAmount']) && $params['waitRebateAmount'] !== ''){ //返利
            $ModelUserFinal->incField('waitRebateAmount' , $params['waitRebateAmount']);
        }

        if(isset($params['contractId']) && $params['contractId'] !== ''){
            $ModelUserFinal->setField('contractId' , $params['contractId']); //TD反填
        }

        if(isset($params['isTiro']) && $params['isTiro'] == 0){ //购买定期
            \Prj\Loger::out('将用户更新为非新手');
            $ModelUserFinal->setField('isTiro' , 0);
            //todo 设置新手的session
            \Prj\Bll\User::getInstance()->setTiroInSession($params['uid'], 0);
        }

        if(isset($params['ymdFirstBuy']) && !empty($params['ymdFirstBuy'])){ //购买的时候
            if(empty($ModelUserFinal->getField('ymdFirstBuy')))
                $ModelUserFinal->setField('ymdFirstBuy' , $params['ymdFirstBuy']);
        }

        $ModelUserFinal->saveToDB();
        $ret = $ModelUser->saveToDB();
        if(!$ret)return $this->resultError('更新失败!!!');
        return $this->resultOK();
    }

    public function evtBindCard($userId , $orderNo , $idCardNo , $bankCardNo){
        $this->evt = 'evtBindCard';
        $bindInfo = \Prj\Model\Payment\BankBind::getRecord(null , [
            'status' => 'BIND',
            'userId' => $userId,
        ]);
        if(empty($bindInfo))return $this->resultError('卡信息不存在!!!');
        $bankInfo = \Prj\Model\Payment\BankInfo::getRecord(null , [
            'bankId' => $bindInfo['bankId']
        ]);
        if(empty($bankInfo))return $this->resultError('银行信息不存在!!!');
        return $this->setInfo([
            'uid' => $userId,
            'isBindCard' => 1,
            'bindCardId' => $bankCardNo,
            'realName' => $bindInfo['realName'],
            'bankCardCode' => $bankInfo['bankCode'],
            'certNo' => $idCardNo
        ]);
    }

    public function evtUnBind($userId){
        $this->evt = 'evtUnBind';
        return $this->setInfo([
            'uid' => $userId,
            'isBindCard' => 0
        ]);
    }

    public function evtRecharge($userId , $orderTime){
        $this->evt = 'evtRecharge';
        if($orderTime === null)$orderTime = date('Ymd');
        return $this->setInfo([
            'uid' => $userId,
            'rechargeTime' => $orderTime,
        ]);
    }

    public function evtSetPayPwd($userId){
        $this->evt = 'evtSetPayPwd';
        return $this->setInfo([
            'uid' => $userId,
            'payPwd' => 'true',
        ]);
    }

    /**
     * Hand 购买定期
     * @param $userId
     * @param null $orderTime
     * @return array
     */
    public function evtBuyTime($userId , $orderTime = null){
        $this->evt = 'evtBuyTime';
        if(empty($orderTime))$orderTime = date('Ymd');
        return $this->setInfo([
            'uid' => $userId,
            'orderTime' => $orderTime,
            'ymdFirstBuy' => $orderTime,
            'isTiro' => 0,
        ]);
    }


    public function evtBuyCurrent($userId , $orderTime = null){
        $this->evt = 'evtBuyCurrent';
        if(empty($orderTime))$orderTime = date('Ymd');
        return $this->setInfo([
            'uid' => $userId,
            'ymdFirstBuy' => $orderTime,
        ]);
    }

    /**
     * 持有订单后-更新canInvite字段
     * @param $uid
     * @author lingtima@gmail.com
     */
    public function evtOrderHolding($uid)
    {
        $this->evt = 'evtOrderHolding';
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            if ($ModelUserFinal->getField('canInvite') == 0) {
                $ModelUserFinal->setField('canInvite', 1);
                $ModelUserFinal->saveToDB();
            }
        }
    }


    /**
     * Hand 获取用户的是否绑卡的状态
     * @param \Prj\Model\UserFinal $final
     * @return int
     */
    public function getIsBindCard(\Prj\Model\UserFinal $final){
        $userId = $final->getField('uid');
        $is = $final->getField('isBindCard');
        if(!$is){
            $realIs = \Prj\Model\Payment\AccountBankBind::hasBind($userId);
            if($realIs === true){
                //绑卡状态不同步
                \Prj\Loger::out('【致命错误】用户的绑卡状态不同步,请速速排查 uid:' . $userId);
                $this->syncBankInfo($final);
                return 1;
            }else{
                return 0;
            }
        }else{
            if(empty($final->getField('realname')) || empty($final->getField('bindCardId')) ||
                empty($final->getField('bankCardCode')) || empty($final->getField('certNo'))){
                $this->syncBankInfo($final);
            }
            return $is;
        }
    }

    /**
     * Hand 查询并同步新手状态
     * @param \Prj\Model\UserFinal $final
     * @return int
     */
    public function getIsTiro(\Prj\Model\UserFinal $final){
        $userId = $final->getField('uid');
        $is = $final->getField('isTiro');
        //1=新手  0=非新手
        if($is){
            //如果是新手,检查是否是真的新手
            $lastDingOrder = \Prj\Model\ZyBusiness\TradOrder::getLastDingOrder($userId);
            \Prj\Loger::outVal('lastDingOrder' , $lastDingOrder);
            if(empty($lastDingOrder)){
                return 1;
            }else{
                \Prj\Loger::out('【致命错误】用户的新手状态不同步,请速速排查 uid:' . $userId);
                $this->evtBuyTime($userId , 0);
                return 0;
            }
        }else{
            return 0;
        }
    }

    /**
     * Hand 同步绑卡数据
     * @param \Prj\Model\UserFinal $final
     * @return bool
     */
    protected function syncBankInfo(\Prj\Model\UserFinal $final){
        $userId = $final->getField('uid');
        $bindCardId = $final->getField('bindCardId');
        if(empty($bindCardId)){
            $bankRes = \Lib\Services\ZYSettlement::getInstance()->getAccount($userId);
            if(!$this->checkRes($bankRes))return true;
            $info = $bankRes['data'];
            $this->evtBindCard($userId , '' , $info['certNo'] , $info['bankCard']);
            return $info;
        }
        return true;
    }
}