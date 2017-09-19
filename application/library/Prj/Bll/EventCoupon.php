<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;
use \Lib\Misc\Result;
use \Prj\Loger;

/**
 * 这个类主要是各种基于活动的优惠券的发放,调用发券接口
 * Class EventCoupon
 * @package Prj\Bll
 */
class EventCoupon extends _BllBase {

    protected static $oids = [];

    protected $event_code; //活动的类型编码

    /**
     * 对应发券的发放渠道 sendWithJavaSender=java接口发送,sendWithLocalSender=本地的发券
     * @var array
     */
    protected $code_sender = [
        // \Prj\Model\Activity::register_code => 'sendWithLocalSender',
        // \Prj\Model\Activity::onechui_code => 'sendWithLocalSender',
        'default' => 'sendWithLocalSender',
    ];

    /**
     * Hand 设置活动 code
     * @param $code
     * @return $this
     */
    protected function setEventCode($code){
        \Prj\Loger::setKv('Event' , $code);
        $this->event_code = $code;
        return $this;
    }

    public function __construct(){
        parent::__construct();
    }

    protected function free(){
        $this->event_code = null;
    }

    /**
     * 发送注册事件的红包
     * @param $eventId
     * @param $ucUid
     * @return array
     */
    protected function sendRegisterCoupon($eventId , $ucUid){
        $tag = \Prj\Model\Activity::register_code;
        $this->setEventCode($tag);
        $uniWhere = [
            'type' => $tag,
            'ucUserId' => $ucUid,
            'eventId' => $eventId,
        ];
        return self::sendCouponByEventId($eventId , $ucUid , $tag , $uniWhere);
    }

    /**
     * 发送绑卡事件的红包
     * @param $eventId
     * @param $ucUid
     * @return array
     */
    protected function sendBindCoupon($eventId , $ucUid){
        $tag = \Prj\Model\Activity::bind_code;
        $this->setEventCode($tag);
        $uniWhere = [
            'type' => $tag,
            'ucUserId' => $ucUid,
        ];
        return self::sendCouponByEventId($eventId , $ucUid , $tag , $uniWhere);
    }

    /**
     * 发送充值事件的红包
     * @param $eventId
     * @param $ucUid
     * @return array
     *
     */
    protected function sendRechargeCoupon($eventId , $ucUid){
        $tag = \Prj\Model\Activity::charge_code;
        $this->setEventCode($tag);
        $uniWhere = [
            'type' => $tag,
            'ucUserId' => $ucUid,
        ];
        return self::sendCouponByEventId($eventId , $ucUid , $tag , $uniWhere);
    }

    /**
     * 发送一锤定音事件的红包
     * @param $eventId
     * @param $productId
     * @return array
     */
    protected function sendOneChuiCoupon($eventId , $productId){
        if(empty($productId))return $this->resultError('参数错误#productId');
        $tag = \Prj\Model\Activity::onechui_code;
        $this->setEventCode($tag);
        Loger::out('产品ID: '.$productId);

        if(\Prj\Tool\System::isZy()){
            $rewardInfoRes = \Prj\Bll\ActRuleZy\ActOneChui::getInstance()->getCouponInfo([
                'actOid' => $eventId,
                'productId' => $productId,
            ]);
            if(!Result::check($rewardInfoRes))return $rewardInfoRes;
            $orderId = $rewardInfoRes['data']['orderInfo']['orderId'];
            $couponInfo = ['oid' => $rewardInfoRes['data']['couponId']];
            $ucUid = $rewardInfoRes['data']['userId'];

        }else{
            $rewardInfoRes = \Prj\Bll\ActRule\OneChui::getInstance()->getRewardInfo($productId , $eventId);
            if(!Result::check($rewardInfoRes))return $rewardInfoRes;
            $orderId = $rewardInfoRes['data']['orderInfo']['oid'];
            $couponInfo = $rewardInfoRes['data']['couponInfo'];
            $ucUid = $rewardInfoRes['data']['userOid'];
        }

        $uniWhere = [
            'type' => $tag,
            'eventId' => $eventId,
            'productId' => $productId,
        ];
        return self::sendCouponByEventId($eventId , $ucUid , $tag , $uniWhere , $couponInfo , [
            'productId' => $productId,
            'orderId' => $orderId,
        ]);
    }

    /**
     * 发送一鸣惊人的红包
     * @param $eventId
     * @param $productId
     * @return array
     */
    protected function sendYiMingCoupon($eventId , $productId){
        $tag = \Prj\Model\Activity::yiming_code;
        $this->setEventCode($tag);
        Loger::out('产品ID: '.$productId);

        if(\Prj\Tool\System::isZy()){
            $rewardInfoRes = \Prj\Bll\ActRuleZy\ActYiMing::getInstance()->getCouponInfo([
                'actOid' => $eventId,
                'productId' => $productId,
            ]);
            if(!Result::check($rewardInfoRes))return $rewardInfoRes;
            $orderId = $rewardInfoRes['data']['orderInfo']['orderId'];
            $couponInfo = ['oid' => $rewardInfoRes['data']['couponId']];
            $ucUid = $rewardInfoRes['data']['userId'];

        }else{
            $rewardInfoRes = \Prj\Bll\ActRule\OneChui::getInstance()->getRewardInfo($productId , $eventId);
            if(!Result::check($rewardInfoRes))return $rewardInfoRes;
            $orderId = $rewardInfoRes['data']['orderInfo']['oid'];
            $couponInfo = $rewardInfoRes['data']['couponInfo'];
            $ucUid = $rewardInfoRes['data']['userOid'];
        }

        $uniWhere = [
            'type' => $tag,
            'eventId' => $eventId,
            'productId' => $productId,
        ];
        return self::sendCouponByEventId($eventId , $ucUid , $tag , $uniWhere , $couponInfo , [
            'productId' => $productId,
            'orderId' => $orderId,
        ]);
    }

    /**
     * 发送首次购买的红包
     * @param $eventId
     * @param $ucUid
     * @return array
     */
    protected function sendFirstBuyCoupon($eventId , $ucUid){
        $tag = \Prj\Model\Activity::buy_code;
        $this->setEventCode($tag);
        $uniWhere = [
            'type' => $tag,
            'ucUserId' => $ucUid,
            'eventId' => $eventId,
        ];
        return self::sendCouponByEventId($eventId , $ucUid , $tag , $uniWhere);
    }

    public function sendRebateCoupon($rebateId){
        $rule = \Prj\Bll\ActivityConfig::getInstance()->getConfig('其它配置' , 'rebate_rule');
        $ruleArr = json_decode($rule , true);
        if(empty($ruleArr))$this->fatalErr('无效的返利发券规则!!!' , null , $rule);

        $rebate = \Prj\Model\InviteRebateInfo::getCopy($rebateId);
        $rebate->load();
        if(!$rebate->exists())return $this->resultError('返利信息不存在');
        if($rebate->getField('status') == 1)return $this->resultError('返利已经发放');
        $couponType = strtoupper($rebate->getField('couponType'));
        $userId = $rebate->getField('uid');
        if($couponType == \Prj\Model\Coupon::type_coupon){
            $tag = \Prj\Model\Activity::invite_code;
            //$eventId = $tag . '_' . $couponType;
            $couponId = $ruleArr[floatval(round($rebate->getField('amount') / 100 , 2))];
            $couponAmount = 0;
        }else if($couponType == \Prj\Model\Coupon::type_redPackets){
            $tag = \Prj\Model\Activity::rebate_code;
            //$eventId = $tag . '_' . $couponType;
            $couponId = $ruleArr[0];
            $couponAmount = $rebate->getField('amount');
        }else{
            $this->fatalErr('不支持的卡券信息!!!');
        }
        $this->setEventCode($tag);
        if(empty($couponId))$this->fatalErr('无法匹配卡券信息!!!');
        $rebateFinal = \Prj\Model\InviteFinal::getCopy($userId , $rebate->getField('formUid'));
        $rebateFinal->load();
        if(!$rebateFinal->exists())return $this->resultError('返利统计信息不存在');

        $uniwhere = [
            'type' => $tag,
            //'eventId' => $eventId,
            'ucUserId' => $rebate->getField('uid'),
            'orderId' => $rebate->getField('orderNo'),
        ];
        $res = self::sendCouponByEventId('' , $userId , $tag , $uniwhere , [
            'oid' => $couponId,
            'amount' => $couponAmount,
        ]);

        $params = [
            'rebateId' => $rebateId,
            'userId' => $userId,
        ];
        if(!$this->checkRes($res)){
            $params['status'] = 4;
            $params['message'] = $res['message'];
        }else{
            $params['status'] = 1;
            $params['message'] = '';
        }

        $res = \Prj\Bll\Rebate::getInstance()->updateRebateStatus($params);
        return $res;
    }

    /**
     * 发送加息红包
     * @param $orderId
     * @return array
     */
    public function sendJiaXiRedpakets($orderId){
        \Prj\Loger::setOrderId($orderId);
        \Prj\Loger::setTag('JiaXi');
        Loger::out('-------------------------');
        $tag = \Prj\Model\Activity::jiaxi_code;
        $this->setEventCode($tag);
        $uniWhere = [
            'orderId' => $orderId,
            'type' => $tag,
        ];
        $rewardInfoRes = \Prj\Bll\ActRule\JiaXi::getInstance()->getRewardInfo($orderId);
        if(!Result::check($rewardInfoRes))return $rewardInfoRes;
        $couponInfo = $rewardInfoRes['data']['couponInfo'];
        $ucUid = $rewardInfoRes['data']['userOid'];

        return self::sendCouponByEventId('' , $ucUid , $tag , $uniWhere , $couponInfo );
    }

     /**
     * Hand 发送生日礼包
     * @param $userId
     * @param $date
     * @param $couponId
     * @return array
     */
    public function sendBirthCoupon($userId , $date , $couponId){
        $res = \Prj\Bll\Coupon::getInstance()->getRecord(['oid' => $couponId]);
        if(!$this->checkRes($res))return $res;
        $info = $res['data']['info'];
        if(empty($info))return $this->resultError('不存在的券信息!!!');
        \Prj\Loger::setTag('Birth');
        Loger::out('-------------------------');
        $tag = 'birth_' . date('Y' , strtotime($date)) . '_' . $couponId;
        $this->setEventCode($tag);
        $uniWhere = [
            'ucUserId' => $userId,
            'type' => $tag,
            //'orderId' => 5,
        ];
        $info['couponType'] = $info['type'];
        $info['upperAmount'] = round($info['upperAmount'] * 100);
        $info['disableDate'] = round((strtotime(date('Ymt')) - strtotime(date('Ymd'))) / 86400);
        $info['productList'] = $this->getProductList($info['labels']);
        //var_dump($info);
        return self::sendCouponByEventId('' , $userId , $tag , $uniWhere , $info);
    }

    /**
     * 获取券的使用产品范围
     * @param $labels
     * @return array
     */
    protected function getProductList($labels){
        \Prj\Loger::outVal('labels' , $labels);
        $tmp = [];
        foreach($labels as $v){
            if(empty($v))continue;
            if(\Prj\Tool\System::isZy()){
                $label = \Prj\Model\ZyBusiness\SystemLabel::getCopy($v);
                $label->load();
                $tmp[] = ['productCode' => $label->getField('labelNo') , 'productName' => $label->getField('labelName')];
            }else{
                $label = \Prj\Model\Mimosa\Label::getCopy($v);
                $label->load();
                if(!$label->exists())$this->fatalErr('lablelId: '.$v.' does not exists !');
                $tmp[] = ['productCode' => $label->getField('labelCode'), 'productName' => $label->getField('labelName')];
            }
        }
        return $tmp;
    }


    /**
     * Hand 用JAVA的方式发券
     * @param array $params
     * @return array
     */
    protected function sendWithJavaSender($params = []){
        $this->log('发券方式: ' . __METHOD__);
        $couponInfo = $params['couponInfo'];
        $ucUid = $params['userId'];
        $eventId = $params['eventId'];
        $amount = $params['amount'];
        $reqId = $params['reqId'];

        $send = new \Lib\Services\SendCoupon;
        $send->setUserId($ucUid)
            ->setEventId($eventId)
            ->setReqOid($reqId)
            ->setDesc($couponInfo['description'])
            ->setName($couponInfo['name'])
            ->setAmount( $amount )
            ->setProductList($couponInfo['productList'])
            ->setInvestAmount($couponInfo['investAmount']);
        if(isset($couponInfo['couponType']))$send->setCouponType($couponInfo['couponType']);
        if(isset($couponInfo['disableDate']))$send->setDisableDate($couponInfo['disableDate']);

        if(!empty($couponInfo)){
            $ret = $send->sendCouponToUser();
        }else{
            $ret = $send->sendCouponToUser2();
        }

        if($ret){
            Loger::out('发券成功!');
            return $this->resultOK([
                'obj' => $send,
            ]);
        }else{
            Loger::out('发券失败!' , LOG_ERR);
            return $this->resultError([
                'obj' => $send,
            ]);
        }
    }

    /**
     * Hand 用自己的方式发券
     * $params['amount'] 单位元
     * @param array $params
     * @return array
     */
    protected function sendWithLocalSender($params = []){
        $this->log('发券方式: ' . __METHOD__);
        if(!\Lib\Misc\Result::paramsCheck($params , ['couponId','userId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $send = \Lib\Services\SendCouponLocal::getInstance();
        $send->setEventId($params['eventId'])
            ->setCouponId($params['couponId'])
            ->setAmount(round($params['amount'] * 100));
        $ret = $send->sendCoupon($params['userId']);
        if($this->checkRes($ret)){
            Loger::out('发券成功!');
            return $this->resultOK([
                'obj' => $send,
                'couponInfo' => $ret['data']['info']
            ]);
        }else{
            Loger::out('发券失败!' , LOG_ERR);
            return $this->resultError([
                'obj' => $send,
            ]);
        }
    }

    /**
     * 根据活动ID发送已配置红包,
     * todo 参数里面的红包金额单位为分
     * @param $eventId
     * @param $ucUid
     * @param $type
     * @param $uniWhere
     * @param array $couponInfo ['amount','desc','name']
     * @param array $otArgs
     * @return array
     */
    protected static function sendCouponByEventId($eventId = '' , $ucUid , $type , $uniWhere , $couponInfo = [] , $otArgs = []){
        //参数检查
        $that = self::getInstance();
        if(empty($that->event_code))$that->fatalErr('未定义活动类型!');
        if(!isset($uniWhere['type']))$that->fatalErr('参数错误[actCode]');
        //特定的活动发放自定义红包
        if(in_array($uniWhere['type'] , \Prj\Model\Activity::$customRewardCode) && empty($couponInfo)){
            $that->fatalErr('此类型的活动只能发放自定义红包!');
        }
        //通过 $couponInfo 是否传值,来判断是否发送自定义金额的红包
        if($eventId){
            $ret = self::getInstance()->checkEvent($eventId); //检查活动的ID是否合法
            if(!Result::check($ret))return $ret;
        }else{
            $eventId = '';
        }

        if(!empty($couponInfo)){
            if(\Prj\Tool\System::isGh()){
                $couponAmount = $couponInfo['upperAmount'];
                if(empty($couponAmount)){
                    \Prj\Loger::out('upperAmount: '.$couponInfo['upperAmount']);
                    return Result::get(RET_ERR , '无效的红包金额!');
                }
            }else if(\Prj\Tool\System::isZy()){
                $couponAmount = $couponInfo['amount'] - 0;
            }
            $yuanAmount = round($couponAmount / 100 , 2);
            $couponId = $couponInfo['oid'];
        }else{
            $activityInfo = \Prj\Bll\Activity::getInstance()->getRecord(['oid' => $eventId]);
            if(empty($activityInfo['data']['info']))return $that->resultError('活动信息为空!');
            $couponId = $activityInfo['data']['info']['coupons'];
        }

        $reqOid = \Lib\Misc\StringH::createOid('req');
        //是否已经发放过
        $sendInfo = \Prj\Model\ActivityCoupon::getOne($uniWhere);
        if(!empty($sendInfo)){
            if($sendInfo['statusCode'] == 'success')return Result::get(RET_ERR , '已经发放过奖励!');
            Loger::out('已经发过的请求使用原有ReqId: '.$sendInfo['oid']);
            $reqOid = $sendInfo['reqOid'];
            $oid = $sendInfo['oid'];
        }else{
            $oid = $reqOid;
        }

        self::$oids[] = $oid;
        $that->log($oid , 'oid');
        $orderId = isset($uniWhere['orderId']) ? $uniWhere['orderId'] : ( isset($otArgs['orderId']) ? $otArgs['orderId'] : '' );
        $activityCoupon = \Prj\Model\ActivityCoupon::getCopy($oid);
        $activityCoupon->load();
        $activityCoupon->setField('ucUserId' , $ucUid);
        $activityCoupon->setField('eventId' , $eventId);
        $activityCoupon->setField('amount' , $couponAmount - 0);
        $activityCoupon->setField('productId' , isset($otArgs['productId']) ? $otArgs['productId'] : '');
        $activityCoupon->setField('orderId' , $orderId);
        $activityCoupon->setField('type' , $type);
        $activityCoupon->setField('createTime' , date('YmdHis'));
        $activityCoupon->setField('reqOid' , $reqOid);

        $params = [
            'couponInfo' => $couponInfo,
            'userId' => $ucUid,
            'eventId' => $eventId,
            'amount' => $yuanAmount,
            'reqId' => $reqOid,
            'couponId' => $couponId,
        ];

        $sendMethod = isset($that->code_sender[$that->event_code]) ? $that->code_sender[$that->event_code] :
            $that->code_sender['default'];

        $res = $that->{$sendMethod}($params);
        $that->log($res , 'couponInfo');
        //if($sendMethod == 'sendWithLocalSender')var_dump($res);
        $that->free();
        if($that->checkRes($res)){
            $activityCoupon->setField('statusCode' , 'success');
            if(isset($res['data']['couponInfo'])){
                $activityCoupon->setField('userCouponId' , $res['data']['couponInfo']['oid']);
            }
        }else{
            $activityCoupon->setField('statusCode' , 'failed');
            $activityCoupon->setField('ret' , json_encode($res , 256));
            $activityCoupon->saveToDB();
            return Result::get(RET_ERR , '发券失败!');
        }
        $that->log($type , 'type');
        $ret = $activityCoupon->saveToDB();
        if(!$ret)\Prj\Loger::out('activityCoupon add failed...' , LOG_ERR);
        return Result::get(RET_SUCC);
    }

    /**
     * WanJiao
     * @param array $params
     * @return bool
     */
    protected function updateActiviyCouponLog($params = []){
        $oid = $params['oid'];
        $ucUid = $params['ucUid'];
        $eventId = $params['eventId'];
        $amount = $params['amount'];
        $orderId = $params['orderId'];
        $type = $params['type'];
        $reqId = $params['reqOid'];
        $productId = $params['productId'];
        $statusCode = $params['statusCode'];

        $activityCoupon = \Prj\Model\ActivityCoupon::getCopy($oid);
        $activityCoupon->load();
        $activityCoupon->setField('ucUserId' , $ucUid);
        $activityCoupon->setField('eventId' , $eventId);
        $activityCoupon->setField('amount' , $amount);
        $activityCoupon->setField('productId' , $productId);
        $activityCoupon->setField('orderId' , $orderId);
        $activityCoupon->setField('type' , $type);
        $activityCoupon->setField('createTime' , date('YmdHis'));
        $activityCoupon->setField('reqOid' , $reqId);
        $activityCoupon->setField('statusCode' , $statusCode);

        return $activityCoupon->saveToDB();
    }

    protected function initSend($params = []){
        $ucUid = $params['ucUid'];
        $eventId = $params['eventId'];
        $reqId = $params['reqId'];
        $couponInfo = $params['couponInfo'];

        $send = new \Lib\Services\SendCoupon;
        $send->setUserId($ucUid)
            ->setEventId($eventId)
            ->setReqOid($reqId);
        if(!empty($couponInfo)){
            $couponAmount = $couponInfo['upperAmount'];
            if(empty($couponAmount)){
                \Prj\Loger::out('upperAmount: '.$couponInfo['upperAmount']);
                return Result::get(RET_ERR , '无效的红包金额!');
            }
            $yuanAmount = round($couponAmount / 100 , 2);
            $send->setDesc($couponInfo['description'])
                ->setName($couponInfo['name'])
                ->setAmount( $yuanAmount )
                ->setProductList($couponInfo['productList'])
                ->setInvestAmount($couponInfo['investAmount']);
            if(isset($couponInfo['couponType']))$send->setCouponType($couponInfo['couponType']);
            if(isset($couponInfo['disableDate']))$send->setDisableDate($couponInfo['disableDate']);
        }
        if(!$send->getReqOid())return Result::get(RET_ERR , '参数错误[ReqOid]');
        return $this->resultOK([
            'obj' => $send,
        ]);
    }

    /**
     * 检查活动是否可用
     * @param $eventId
     * @return array
     */
    public function checkEvent($eventId){
        if(empty($eventId))return Result::get(RET_ERR , '活动ID不能为空!');
        $eventInfoRes =  \Prj\Bll\Activity::getInstance()->getRecords(['oid' => $eventId]);
        if(!Result::check($eventInfoRes))return $eventInfoRes;
        $eventInfo = $eventInfoRes['data'][0];
        if(empty($eventInfo))return Result::get(RET_ERR , '数据库查无此活动!');
        Loger::out('活动名称: '.$eventInfo['title'] .' 活动类型: '.\Prj\Model\Activity::$actCodeMap[$eventInfo['actCode']]);
        //活动是否可用
        $ret = \Prj\Bll\Event::checkOpenEvent($eventInfo);
        if(!Result::check($ret))return $ret;
        return Result::get(RET_SUCC);
    }

    /**
     * 获取发送券的请求ID
     * @return array
     */
    public static function getOids(){
        return self::$oids;
    }

    /**
     * 根据系统配置的活动遍历发券
     * 活动发券的统一入口,事件触发从这里进去
     * actCode = \Prj\Model\Activity::onechui_code , userId , productId
     * @param array $params
     * @return null
     */
    public function sendCoupon($params = []){
        \Prj\Loger::setKv('`_`');
        if(!Result::paramsCheck($params , ['actCode'])){
            return Result::get(RET_ERR , '参数错误['. Result::$errorParam .']');
        }
        $actCode = $params['actCode'];
        \Prj\Loger::setTag($actCode);
        $ucUid = isset($params['userId']) ? $params['userId'] : null;
        $productId = isset($params['productId']) ? $params['productId'] : null;
        //找出所有有效的活动
        $records = \Prj\Bll\Activity::getInstance()->getValidRecords($actCode);
        if(count($records) > 0){
            foreach ($records as $v){
                \Prj\Loger::free();
                Loger::out('-------------------------');
                $eventId = $v['oid'];
                Loger::out('活动ID: '.$eventId);
                switch ($actCode){
                    case \Prj\Model\Activity::register_code : $res = $this->sendRegisterCoupon($eventId , $ucUid);continue;
                    case \Prj\Model\Activity::bind_code : $res = $this->sendBindCoupon($eventId , $ucUid);continue;
                    case \Prj\Model\Activity::charge_code : $res = $this->sendRechargeCoupon($eventId , $ucUid);continue;
                    case \Prj\Model\Activity::buy_code : $res = $this->sendFirstBuyCoupon($eventId , $ucUid);continue;
                    case \Prj\Model\Activity::onechui_code : $res = $this->sendOneChuiCoupon($eventId , $productId);continue;
                    case \Prj\Model\Activity::yiming_code : $res = $this->sendYiMingCoupon($eventId , $productId);continue;
                    default : {
                        Loger::getInstance()->sys_warning('无法识别的活动类型['. $actCode .']');
                        $res = $this->resultError('无法识别的活动类型['. $actCode .']');
                        continue;
                    }
                }
            }
            return $res;
        }else{
            $msg = '系统暂无可用的['. $actCode .']活动!';
            Loger::out('-------------------------');
            Loger::out($msg);
            return $this->resultOK($msg);
        }
    }
}
