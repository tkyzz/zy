<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/16
 * Time: 15:14
 */
namespace Prj\Bll\Tmp;

use function GuzzleHttp\Psr7\str;
use Lib\Misc\StringH;
use Prj\Loger;
use Prj\Model\TmpChocolate;
use Sooh2\DB\Cases\AccountLog;

class Chocolate0828 extends \Prj\Bll\_BllBase
{
    protected $orderType = 'CHOCOLATE';

    protected $config = [
        'start' =>  '20160828000000',
        'finish' => '20170914235959',
        'dayChocolateTimes' => 10, //日领取巧克力次数
        'dayExchangeAmount' => 1500, //日兑换金额
        'orderAmount' => 50000, //投资额限制
        'coupon.description' => '巧克力嘿嘿嘿红包',
        'coupon.name' => '巧克力嘿嘿嘿红包',
        'coupon' => [
            'productList' => [
                ['productCode' => '4', 'productName' => '悦享盈'],
                ['productCode' => '5', 'productName' => '悦嘉盈'],
            ],
            'investAmount' => 50000,
            'disableDate' => 7,
        ],
    ];

    protected function init()
    {
        parent::init();
        \Prj\Loger::setKv('Chocolate0828' , '');
    }

    /**
     * Hand 活动检查
     * @param null $date
     * @return array
     */
    public function actCheck($date = null){
        $date = $date ?: date('YmdHis');
        $start = $this->getConf('start');
        $finish = $this->getConf('finish');
        if($date < $start){
            $this->log($date . ' < ' . $start);
            return $this->resultError('活动尚未开始' , 10);
        }
        if($date > $finish){
            $this->log($date . ' > ' . $finish);
            return $this->resultError('活动已经结束' , 11);
        }
        return $this->resultOK();
    }

    /**
     * Hand 更新日限制
     * @param $dayValue
     * @param $num
     * @param int $init
     * @return array
     */
    protected function dayValueUpd($dayValue , $num , $init = 0){
        if(empty($dayValue) || substr($dayValue , 0 , 8) != date('Ymd')){
            $dayValue = date('Ymd') . str_pad($init , 5 , 0 , STR_PAD_LEFT);
        }
        $remain = substr($dayValue , -5) + $num;
        \Prj\Loger::out('dayValue: '.$dayValue.' change: ' . $num);
        if($remain < 0){
            return $this->resultError('今日领取次数已达上限' , 33);
        }
        return $this->resultOK([
            'dayValue' => date('Ymd') . str_pad($remain , 5 , 0 , STR_PAD_LEFT)
        ]);
    }

    /**
     * Hand
     * @param $dayValue
     * @param int $init
     * @return bool|int|string
     */
    protected function getTodayValue($dayValue , $init = 0){
        if(empty($dayValue) || substr($dayValue , 0 , 8) != date('Ymd')){
            \Prj\Loger::out('getTodayValue dayValue: ' . $dayValue .' date: '.substr($dayValue , 8));
            return $init;
        }else{
            return substr($dayValue , -5) - 0;
        }
    }

    /**
     * Hand 读取配置
     * @param $key
     * @return mixed
     */
    public function getConf($key){
        $value = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.Chocolate0828.' . $key);
        if(!empty($value) && $key != 'coupon'){
            \Prj\Loger::out('getConf '.$key. ' ' . var_export($value , true) . ' from ini');
            return $value;
        }
        $value = $this->config[$key];
        \Prj\Loger::out('getConf '.$key. ' ' . $value);
        if($value === null || $value === '')$this->fatalErr('参数缺失!!!');
        return $value;
    }

    /**
     * Hand 通过手机号领取巧克力
     * @param array $params
     * @return array
     */
    public function getChocolate($params = []){
        \Prj\Loger::setKv('a' , 'getChocolate');
        if(!\Lib\Misc\Result::paramsCheck($params , ['phone' , 'boxOid'])){
            return $this->resultError('参数错误');
        }
        $phone = $params['phone'];
        $boxOid = $params['boxOid'];
        $openId = $params['openId'];
        //活动检查
        $res = $this->actCheck();
        $res['data']['info']['phone'] = $phone;
        if(!$this->checkRes($res))return $res;

        //获取巧克力账户
        $userModel = \Prj\Model\TmpChocolateUser::getOneCopy($phone , $this->getConf('dayChocolateTimes'));
        if(empty($userModel))return $this->resultError('请求太快，请稍后重试');
        $dayChocolateTimes = $userModel->getField('dayChocolateTimes');

        //获取用户
        $user = \Prj\Model\User::getCopyByPhone($phone);
        $user->load();
        if($user->exists()){
            $userId = $user->getField('oid');
        }else{
            $userId = '';
        }

        //盒子是否存在
        $count = \Prj\Model\TmpChocolate::getCount(['boxOid' => $boxOid]);
        if(empty($count))return $this->resultError('无效的巧克力盒子');

        //是否已经领取过
        $record = \Prj\Model\TmpChocolate::getOne(['boxOid' => $boxOid , 'phone' =>$phone]);
        if(!empty($record))return \Lib\Misc\Result::get(88 , 'success' , [
            'info' => $record,
        ]);

        //盒子已经空了
        $record = \Prj\Model\TmpChocolate::getOne(['boxOid' => $boxOid , 'phone' => '']);
        if(empty($record))return $this->resultError('你来晚了，盒子已经空空如也' , 66 , [
            'info' => [
                'phone' => $phone
            ]
        ]);

        //扣减当日领取次数
        $res = $this->dayValueUpd($dayChocolateTimes , -1 , $userModel->getField('dayChocolateTimesLimit'));
        if(!$this->checkRes($res))return $this->resultError($res['message'] , $res['code'] , [
            'info' => [
                'phone' => $phone
            ]
        ]);
        $userModel->setField('dayChocolateTimes' , $res['data']['dayValue']);
        $userModel->incField('amount' , $record['amount']);

        $choco = \Prj\Model\TmpChocolate::getCopy($record['oid']);
        $choco->load();
        if(!empty($choco->getField('phone')))return $this->resultError('服务繁忙[1]');
        $choco->setField('phone' , $phone);
        $choco->setField('statusCode' , 1);
        $choco->setField('leadTime' , date('Y-m-d H:i:s'));

        //防止连点
        $holdRes = $this->hold(__METHOD__.'_'.$phone);
        if(!$this->checkRes($holdRes))return $holdRes;

        //开始更新数据库
        try{
            \Prj\Model\TmpChocolate::startTransaction();
            //巧克力账户更新
            $ret = $userModel->saveToDB();
            if(!$ret)$this->fatalErr('TmpChocolateUser update failed');
            //巧克力更新
            $ret = $choco->saveToDB();
            if(!$ret)$this->fatalErr('TmpChocolate update failed (两个人争抢同一块巧克力)');
            //流水账更新
            $log = \Sooh2\DB\Cases\AccountLog::getRecentCopy($phone);
            $log->transactionStart($record['amount'] , $this->orderType , 'CHOCO:' . $record['oid']);
            $ret = $log->transactionCommit();
            if(!$ret)$this->fatalErr('AccountLog update failed');

            if($openId){
                $wechatUser = \Prj\Model\WechatOpenidPhone::getCopy($openId);
                $wechatUser->setField('phone' , $phone);
                $wechatUser->setField('createTime' , date('Y-m-d H:i:s'));
                $wechatUser->setField('updateTime' , date('Y-m-d H:i:s'));
                $wechatUser->saveToDB();
            }

            \Prj\Model\TmpChocolate::commit();

        }catch (\Exception $e){
            \Prj\Loger::out('[ERROR] ' . $e->getMessage() , LOG_ERR);
            \Prj\Model\TmpChocolate::rollback();
            return $this->resultError('服务繁忙[2]');
        }

        //发送推送
        $pushId = $this->getConf('pushId');
        \Prj\EvtMsg\JavaApiPush::getInstance('')->setTemplateId($pushId);
        $ret = \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($pushId , round($record['amount'] / 100 , 2) , $userId , ['push']);
        \Prj\Loger::outVal('【发送推送】' , $ret);

        return $this->resultOK([
            'info' => $choco->dump(),
        ]);
    }

    /**
     * Hand 领取代金券
     * @param array $params
     * @return array
     */
    public function getCoupon($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['userId'])){
            return $this->resultError('参数错误');
        }
        $userId = $params['userId'];
        \Prj\Loger::setKv('chocolateExchange' , '');
        \Prj\Loger::setUid($userId);
        //活动检查
        $res = $this->actCheck();
        if(!$this->checkRes($res))return $res;

        $user = \Prj\Model\User::getCopy($userId);
        $user->load();
        if(!$user->exists())return $this->resultError('不存在的用户');
        $phone = $user->getField('userAcc');
        \Prj\Loger::setPhone($phone);

        //用户巧克力账户余额检查
        $chocoUser = \Prj\Model\TmpChocolateUser::getOneCopy($phone , $this->getConf('dayChocolateTimes'));
        if(empty($chocoUser))return $this->resultError('服务繁忙[0]');
        $wallet = $chocoUser->getField('amount');
        if($wallet <= 0)return $this->resultError('可用余额为0');

        //日兑换余额检查
        $dayExchangeAmount = $chocoUser->getField('dayExchangeAmount');
        $dayExchangeAmountInit = $this->getConf('dayExchangeAmount');
        $remain = $this->getTodayValue($dayExchangeAmount , $dayExchangeAmountInit);
        if($remain <= 0){
            return $this->resultError('今日可兑换金额为0');
        }

        //扣减日限制
        $exAmount = $wallet < $remain ? $wallet : $remain; //兑换的金额
        if(\Prj\Tool\Debug::isTestEnv())$exAmount = 1;
        $changeRes = $this->dayValueUpd($dayExchangeAmount , $exAmount * -1 , $dayExchangeAmountInit);
        if(!$this->checkRes($changeRes)){
            return $this->resultError('服务繁忙[10]');
        }
        $chocoUser->setField('dayExchangeAmount' , $changeRes['data']['dayValue']);
        $chocoUser->incField('amount' , -1 * $exAmount);
        $chocoUser->incField('couponAmount' , $exAmount);
        $chocoUser->setField('updateTime' , date('Y-m-d H:i:s'));

        //生成发券记录
        $reqId = \Lib\Misc\StringH::createOid();
        $couponLog = \Prj\Model\TmpChocolateCoupon::getCopy($reqId);
        $couponLog->load();
        if($couponLog->exists())return $this->resultError('服务繁忙[1]');
        $couponData = [
            'userId' => $userId,
            'phone' => $phone,
            'reqId' => $reqId,
            'amount' => $exAmount,
            'statusCode' => 0,
            'createTime' => date('Y-m-d H:i:s'),
        ];
        foreach ($couponData as $k => $v){
            $couponLog->setField($k , $v);
        }

        //防止连点
        $holdRes = $this->hold(__METHOD__.'_'.$phone);
        if(!$this->checkRes($holdRes))return $holdRes;

        //开始数据库更新
        try{
            $chocoUser::startTransaction();
            //巧克力账户更新
            $ret = $chocoUser->saveToDB();
            if(!$ret)$this->fatalErr('TmpChocolateUser Update Failed');
            //券记录更新
            $ret = $couponLog->saveToDB();
            if(!$ret)$this->fatalErr('TmpChocolateCoupon Update Failed');
            //流水账更新
            $log = \Sooh2\DB\Cases\AccountLog::getRecentCopy($phone);
            $log->transactionStart($exAmount * -1 , $this->orderType , 'REQID:' . $reqId);
            $ret = $log->transactionCommit();
            if(!$ret)$this->fatalErr('AccountLog update failed');

            $chocoUser::commit();
        }catch (\Exception $e){
            \Prj\Loger::out('[ERROR] ' . $e->getMessage() , LOG_ERR);
            $chocoUser::rollback();
            return $this->resultError('服务繁忙[2]');
        }

        //开始发券.如果发放失败不重试,人工介入
        $sendRes = $this->sendCoupon([
            'userId' => $userId,
            'amount' => $exAmount,
            'reqId' => $reqId,
        ]);

        if(!$this->checkRes($sendRes)){
            $couponLog->setField('statusCode' , 4);
            $couponLog->saveToDB();
            return $this->resultError('');
        }else{
            $couponLog->setField('statusCode' , 1);
            $couponLog->saveToDB();
            //发送站内信
            $mailTitle = $this->getConf('mailTitle');
            $mailContent = $this->getConf('mailContent');
            $mailContent = str_replace('{num}' , round($exAmount/100 , 2) , $mailContent);
            $ret = \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($mailTitle , $mailContent , $userId , ['msg']);
            \Prj\Loger::outVal('【发送站内信】' , $ret);
            return $this->resultOK([
                'amount' => round($exAmount/100 , 2)
            ]);
        }

    }

    /**
     * Hand 将账户余额兑换成兑换券
     * @param array $params
     * @return array
     */
    protected function sendCoupon($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['userId' , 'amount' , 'reqId'])){
            return $this->resultError('参数错误');
        }
        $userId = $params['userId'];
        $amount = $params['amount'];
        $reqId = $params['reqId'];
        $coupon = $this->getConf('coupon');
        $sender = new \Lib\Services\SendCoupon;

        $sender->setUserId($userId)
            ->setReqOid($reqId)
            ->setName($this->getConf('coupon.name'))
            ->setDesc($this->getConf('coupon.description'))
            ->setAmount(round($amount / 100 , 2))
            ->setProductList($coupon['productList'])
            ->setInvestAmount(round($coupon['investAmount'] / 100))
            ->setCouponType('coupon')
            ->setDisableDate($coupon['disableDate']);

        $ret = $sender->sendCouponToUser();
        if(!$ret)return $this->resultError('红包发放失败');
        return $this->resultOK();
    }
    
    public function checkAddTimes($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['orderId'])){
            return $this->resultError('参数错误');
        }
        //活动检查
        $orderId = $params['orderId'];
        $res = $this->actCheck();
        if(!$this->checkRes($res))return $res;

        $order = \Prj\Model\TradeOrder::getCopy($orderId);
        $order->load();
        if(!$order->exists())return $this->resultError('订单不存在!!!');

        $orderAmount = $order->getField('orderAmount');
        if(round($orderAmount * 100) < $this->getConf('orderAmount'))
            return $this->resultError('订单金额不满足!!!');

        $investorId = $order->getField('investorOid');
        $investorInfo = \Prj\Model\MimosaUser::getUserByMiUserId($investorId);
        if(empty($investorInfo))return $this->resultError('投资者信息不存在!!!');

        $userId = $investorInfo['userOid'];
        $user = \Prj\Model\User::getCopy($userId);
        $user->load();
        if(!$user->exists())return $this->resultError('用户信息不存在!!!');

        $createTime = $user->getField('createTime');
        $res = $this->actCheck(date('YmdHis' , strtotime($createTime)));
        if(!$this->checkRes($res))return $this->resultError('非活动时间注册!!!');

        $rebateInfo = \Prj\Model\UserRebateInfo::getCopy(['userOid' => $userId]);
        $rebateInfo->load();

        if(\Prj\Tool\Debug::isTestEnv()){
            $byUserId = '41aab1ac6f4c179db3ab957e92dc';
        }else{
            if(!$rebateInfo->exists())return $this->resultError('没有邀请关系!!!');
            $byUserId = $rebateInfo->getField('referOid');
        }


        //增加领取次数
        $res = $this->addDayChocolateTimesLimit([
            'userId' => $byUserId,
            'fromUserId' => $userId,
            'orderId' => $orderId,
        ]);
        
        return $res;
    }

    protected function addDayChocolateTimesLimit($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['userId' , 'fromUserId' , 'orderId'])){
            return $this->resultError('参数错误');
        }
        $userId = $params['userId'];
        $fromUserId = $params['fromUserId'];
        $orderId = $params['orderId'];
        $inviteOrder = \Prj\Model\TmpChocolateInviteOrder::getCopy([
            'userId' => $userId,
            'fromUserId' => $fromUserId,
        ]);
        $inviteOrder->load();
        if($inviteOrder->exists())return $this->resultError('已经获得过奖励!!!');

        $user = \Prj\Model\User::getCopy($userId);
        $user->load();
        if(!$user->exists())return $this->resultError('用户信息不存在!!!');
        $phone = $user->getField('userAcc');

        $addData = [
            'oid' => \Lib\Misc\StringH::createOid(),
            'orderId' => $orderId,
            'createTime' => date('Y-m-d H:i:s'),
        ];
        foreach ($addData as $k => $v){
            $inviteOrder->setField($k , $v);
        }

        $chocoUser = \Prj\Model\TmpChocolateUser::getOneCopy($phone);
        if(empty($chocoUser))return $this->resultError('服务繁忙[0]');
        $dayChocolateTimes = $chocoUser->getField('dayChocolateTimes');
        $changeRes = $this->dayValueUpd($dayChocolateTimes , 1 , $chocoUser->getField('dayChocolateTimesLimit'));
        if(!$this->checkRes($changeRes))return $changeRes;
        $chocoUser->setField('dayChocolateTimes' , $changeRes['data']['dayValue']);
        $chocoUser->incField('dayChocolateTimesLimit' , 1);

        //开始更新数据库
        try{
            $chocoUser::startTransaction();
            //更新巧克力用户
            $ret = $chocoUser->saveToDB();
            if(!$ret)$this->fatalErr('TmpChocolateUser Update Failed');
            //邀请人奖励记录
            $ret = $inviteOrder->saveToDB();
            if(!$ret)$this->fatalErr('TmpChocolateInviteOrder Update Failed');
            $chocoUser::commit();
        }catch (\Exception $e){
            \Prj\Loger::out('[ERROR] ' . $e->getMessage() , LOG_ERR);
            $chocoUser::rollback();
            return $this->resultError('服务繁忙[1]');
        }

        return $this->resultOK();
    }

    public function accountInfo($userId){
        $user = \Prj\Model\User::getCopy($userId);
        $user->load();
        if(!$user->exists())return $this->resultError('用户信息不存在');

        $phone = $user->getField('userAcc');
        $chocoUser = \Prj\Model\TmpChocolateUser::getOneCopy($phone , $this->getConf('dayChocolateTimes'));
        $data['phone'] = $phone;
        $data['dayChocolateTimesLimit'] = $chocoUser->getField('dayChocolateTimesLimit');
        $data['dayChocolateTimesRemain'] =
            $this->getTodayValue($chocoUser->getField('dayChocolateTimes') , $chocoUser->getField('dayChocolateTimesLimit'));
        $data['amount'] = round($chocoUser->getField('amount')/100 , 2);
        $data['dayExchangeAmountRemain'] =
            $this->getTodayValue($chocoUser->getField('dayExchangeAmount') , $this->getConf('dayExchangeAmount'));
        $data['dayExchangeAmountRemain'] = round($data['dayExchangeAmountRemain']/100 , 2);
        $data['timeCheck'] = $this->actCheck()['code'];

        $data['config'] = [
            'dayExchangeAmountLimit' => round($this->getConf('dayExchangeAmount')/100),
            'start' => strtotime($this->getConf('start')) . '000',
            'finish' => strtotime($this->getConf('finish')) . '000',
            'orderAmount' => round($this->getConf('orderAmount')/100),
        ];
        return $this->resultOK($data);
    }

    public function myList($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['userId' , 'pageNo' , 'pageSize'])){
            return $this->resultError('参数错误');
        }
        $pageSize = $params['pageSize'];
        $pageNo = $params['pageNo'];
        $userId = $params['userId'];

        $user = \Prj\Model\User::getCopy($userId);
        $user->load();
        if(!$user->exists())return $this->resultError('用户信息不存在');

        $where = ['phone' => $user->getField('userAcc')];
        $total = \Prj\Model\TmpChocolate::getCount($where);
        $list = \Prj\Model\TmpChocolate::getRecords('leadTime,amount' , $where , 'rsort createTime' , $pageSize , ($pageNo - 1) * $pageSize);

        if($list){
            foreach ($list as &$value){
                $value['createTime'] = strtotime($value['leadTime']) . '000';
                $value['amount'] = round($value['amount'] / 100 , 2);
                unset($value['leadTime']);
            }
        }

        return $this->resultOK([
            'list' => $list,
            "pageInfo"=> [
                "pageNo"=> $pageNo,
                "pageSize"=> $pageSize,
                "totalSize"=> $total,
                "totalPage"=> ceil($total / $pageSize)
            ]
        ]);
    }

    public function boxList($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['boxOid'])){
            return $this->resultError('参数错误');
        }
        $boxOid = $params['boxOid'];

        $sql = <<<sql
select choco.phone , choco.leadTime , choco.amount , wu.headimgurl from tb_tmp_chocolate_0 choco
LEFT JOIN tb_wechat_openid_phone_0 wp ON choco.phone = wp.phone
LEFT JOIN tb_wechat_user_0 wu ON wp.openid = wu.openid
where boxOid = '$boxOid' and choco.phone <> ''
ORDER BY choco.leadTime
LIMIT 0,50
sql;

        $list = \Prj\Model\TmpChocolate::query($sql);

        if($list){
            foreach ($list as &$value){
                $value['leadTime'] = strtotime($value['leadTime']) . '000';
                $value['amount'] = round($value['amount'] / 100 , 2);
                $value['phone'] = \Lib\Misc\StringH::hideStr($value['phone'] , 3 , 4);
            }
        }

        return $this->resultOK([
            'list' => $list,
            'config' => [
                'dayExchangeAmountLimit' => round($this->getConf('dayExchangeAmount')/100),
                'start' => strtotime($this->getConf('start')) . '000',
                'finish' => strtotime($this->getConf('finish')) . '000',
                'orderAmount' => round($this->getConf('orderAmount')/100),
                'timeCheck' => $this->actCheck()['code']
            ]
        ]);
    }

    /**
     * Hand 防止用户操作太快
     * @param $id
     * @param $second
     * @return array
     */
    protected function hold($id , $second = 3){
        $key = 'php:hold:' . $id;
        $redis = \Prj\Redis\Base::getDB();
        $val = $redis->exec([
            ['INCR' , $key ]
        ]);
        \Prj\Loger::out("====== hold ==== $key ======== ". ($val - 0) ." ===============================");
        if($val == 1 || $val === false){
            $ret = $redis->exec([
                ['EXPIRE' , $key , $second ]
            ]);
            return $this->resultOK();
        }else{
            return $this->resultError('请求太快，请稍后重试');
        }
    }

    public function giveChocolateBox($uid)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('=========开始发放巧克力盒子');
        if ($record = \Prj\Model\TmpChocolate::getOne(['fromUserId' => $uid, 'ymd' => date('Ymd')])) {
            \Sooh2\Misc\Loger::getInstance()->app_trace('=========END! 今日已领取过');
            \Sooh2\Misc\Loger::getInstance()->app_trace('今日已领取过');
            return $record['boxOid'];
        } else {
            //生成新盒子
            $totalAmount = 1000;
            $totalNum = \Prj\Tool\Random::getInstance()->randomInScopeAsArray([
                '3' => 230,
                '4' => 1355,
                '5' => 3415,
                '6' => 3415,
                '7' => 1355,
                '8' => 230,
            ]);
            if (($arrAmount = \Prj\Tool\Random::getInstance()->shardingAmountOrderNum($totalAmount, $totalNum, 20)) == false) {
                \Sooh2\Misc\Loger::getInstance()->app_trace('切分金额时的配置：amount:' . $totalAmount . '. num:' . $totalNum);
                \Sooh2\Misc\Loger::getInstance()->app_trace('巧克力金额配置不正确，请稍后再试');
                return false;
            }
            \Sooh2\Misc\Loger::getInstance()->app_trace('巧克力金额：' . json_encode($arrAmount));

            $boxOid = floor(microtime(true) * 1000) . \Lib\Misc\StringH::randStr(10);
            if ($tmpChocolateBroker = \Prj\Model\TmpChocolate::getOne(['boxOid' => $boxOid])) {
                \Sooh2\Misc\Loger::getInstance()->app_trace('领取巧克力盒子失败，盒子ID重复，请稍后再试');
                return false;
            }

            //防止并发多领取 HAND
            $holdRes = $this->hold(__METHOD__ . '_' . $uid , 3);
            if(!$this->checkRes($holdRes))return $holdRes;

            \Prj\Model\TmpChocolate::startTransaction();
            foreach ($arrAmount as $v) {
                $try = 5;
                while ($try > 0) {
//                    $oid = sprintf('%.0f', microtime(true) * 1000) . mt_rand(10000, 99999);
                    $ModelTmpChocolate = \Prj\Model\TmpChocolate::getCopy(true);
                    $ModelTmpChocolate->load();
                    \Sooh2\Misc\Loger::getInstance()->app_trace($ModelTmpChocolate->dbWithTablename()->lastCmd());
                    if ($ModelTmpChocolate->exists()) {
                        $try--;
                        continue;
                    } else {
                        $ModelTmpChocolate->setField('boxOid', $boxOid);
                        $ModelTmpChocolate->setField('fromUserId', $uid);
                        $ModelTmpChocolate->setField('amount', $v);
                        $ModelTmpChocolate->setField('ymd', date('Ymd'));
                        $ModelTmpChocolate->setField('createTime', date('Y-m-d H:i:s'));
                        $ModelTmpChocolate->saveToDB();
                        unset($ModelTmpChocolate);
                        break;
                    }
                }

                if ($try <= 0) {
                    //TODO
                    \Sooh2\Misc\Loger::getInstance()->app_trace('一个主键重复且5次尝试后依然重复');
                    \Prj\Model\TmpChocolate::rollback();
                    return false;
                }
            }
            \Prj\Model\TmpChocolate::commit();
        }

        \Sooh2\Misc\Loger::getInstance()->app_trace('=========发放巧克力盒子完成,boxOid:' . $boxOid);
        return $boxOid;
    }
}