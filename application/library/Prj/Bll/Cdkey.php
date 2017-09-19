<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/30
 * Time: 13:32
 */

namespace Prj\Bll;

use Lib\Misc\Result;

class Cdkey extends \Prj\Bll\_BllBase
{
    protected $lang = [
        100 => '兑换码错误，请输入正确的兑换码',
        101 => '兑换时间还未开始',
        102 => '兑换时间已结束',
        103 => '兑换名额已满，请关注后续活动',
        104 => '该兑换码已使用，请关注后续活动',
        105 => '您已兑换该奖品',
    ];

    /**
     * Hand 奖品发放类
     * @param array $award
     * @return array
     */
    protected function getSender($award = []){
        $map = [
            \Prj\Model\CdkeyAward::$type_coupon => 'AwardCoupon',
        ];
        /** @var \Prj\Bll\Award\_AwardBase $class */
        $class = "\Prj\Bll\Award\\" . $map[ $award['typeCode'] ];
        if(!class_exists($class))return $this->resultError('服务器繁忙[sender null]');
        return $this->resultOK([
            'obj' => $class::getInstance(),
        ]);
    }

    protected function init(){
        \Prj\Loger::setKv(__CLASS__);
    }

    /**
     * Hand 使用兑换码
     * @param array $params
     * @return array
     */
    public function useKey($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['words' , 'userId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }

        $words = $params['words'];
        $userId = $params['userId'];

        $cdkey = \Prj\Model\Cdkey::getOneByWords($words);
        if($cdkey){
            //固定的兑换码
            $checkRes = $this->checkCdkey($cdkey);
            if(!$this->checkRes($checkRes))return $checkRes;
            $exist = \Prj\Model\CdkeyUser::getRecord(null , [
                'cdkeyId' => $cdkey['oid'],
                'userId' => $userId,
            ]);
            if(!empty($exist))return $this->returnMsg(105);
            //请求速度限制
            $hold = $this->hold(__METHOD__.':'.$userId);
            if(!$this->checkRes($hold))return $hold;
            //领用+1
            try{
                \Prj\Model\CdkeyUser::startTransaction();
                $cdkModel = \Prj\Model\Cdkey::getCopy($cdkey['oid']);
                $cdkModel->load();
                $cdkModel->incField('getCount' , 1);
                $ret = $cdkModel->saveToDB();
                if(!$ret)$this->fatalErr('cdk扣减失败!!!');
                //发放奖品
                /** @var \Prj\Bll\Award\AwardCoupon $sender */
                $sender = $checkRes['data']['senderObj'];
                $awardInfo = $checkRes['data']['awardInfo'];
                $sendRes = $sender->send([
                    'couponId' => $awardInfo['couponId'],
                    'userId' => $userId,
                ]);
                if(!$this->checkRes($sendRes))$this->fatalErr($sendRes);
                // $this->log($sendRes,  'sendRes');
                //入库
                $ret = \Prj\Model\CdkeyUser::add([
                    'cdkeyId' => $cdkey['oid'],
                    'fromUserId' => $userId,
                    'userId' => $userId,
                    'words' => $cdkey['words'],
                    'statusCode' => \Prj\Model\CdkeyUser::$status_used,
                    'useTime' => date('Y-m-d H:i:s'),
                    'args' => json_encode([
                        'coupon' => $sendRes['data']['info'],
                    ] , 256),
                ]);
                if(!$ret)$this->fatalErr('ckd使用失败!!!');
                \Prj\Model\CdkeyUser::commit();
            }catch (\Exception $e){
                \Prj\Model\CdkeyUser::rollback();
                return $this->resultError('服务器繁忙['.$e->getMessage().']');
            }
        }else{
            //随机的兑换码
            $userCdkey = \Prj\Model\CdkeyUser::getRecord(null , ['words' => $words]);
            if(empty($userCdkey))return $this->returnMsg(100);
            $cdkey = \Prj\Model\Cdkey::getCopy($userCdkey['cdkeyId']);
            $cdkey->load();
            if(!$cdkey->exists())return $this->resultError('服务器繁忙[ckdey null]');
            $checkRes = $this->checkCdkey($cdkey->dump());
            if(!$this->checkRes($checkRes))return $checkRes;
            $exist = \Prj\Model\CdkeyUser::getRecord(null , ['userId' => $userId , 'cdkeyId' => $userCdkey['cdkeyId']]);
            if(!empty($exist))return $this->returnMsg(105);

            if($userCdkey['statusCode'] != 'UNUSED')return $this->returnMsg(104);
            //更新
            $hold = $this->hold(__METHOD__.':'.$userId);
            if(!$this->checkRes($hold))return $hold;
            try{
                \Prj\Model\CdkeyUser::startTransaction();
                $ret = \Prj\Model\CdkeyUser::used($userCdkey['oid'] , $userId);
                if(!$ret)return $this->fatalErr('服务器繁忙[9]');
                //发放奖品
                /** @var \Prj\Bll\Award\AwardCoupon $sender */
                $sender = $checkRes['data']['senderObj'];
                $awardInfo = $checkRes['data']['awardInfo'];
                $sendRes = $sender->send([
                    'couponId' => $awardInfo['couponId'],
                    'userId' => $userId,
                ]);
                if(!$this->checkRes($sendRes))$this->fatalErr($sendRes);
                \Prj\Model\CdkeyUser::commit();
            }catch (\Exception $e){
                \Prj\Model\CdkeyUser::rollback();
                return $this->resultError('服务器繁忙['. $e->getMessage() .']');
            }
        }
        return $this->resultOK('恭喜您，成功兑换！');
    }

    /**
     * Hand 获取一个兑换码
     * @param array $params
     * @return array
     */
    public function getKey($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['cdkeyId' , 'userId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }

        $cdkeyId = $params['cdkeyId'];
        $userId = $params['userId'];
        $words = strtoupper(\Lib\Misc\StringH::createCdk());
        $cdkey = \Prj\Model\Cdkey::getCopy($cdkeyId);
        $cdkey->load();
        if(!$cdkey->exists())return $this->resultError('服务器繁忙[1]');
        $cdKeyInfo = $cdkey->dump();

        $res = $this->checkCdkey($cdKeyInfo);
        if(!$this->checkRes($res))return $res;
        if(!empty($cdKeyInfo['words']))return $this->resultError('服务器繁忙[2]');
        $exist = \Prj\Model\CdkeyUser::getRecord(null , [
            'fromUserId' => $userId,
            'cdkeyId' => $cdkeyId,
        ]);
        if(!empty($exist)){
            return $this->resultOK([
                'words' => $exist['words']
            ]);
        }

        //兑换码唯一不重复
        $retry = 10;
        while (true){
            $retry --;
            $keyRepeat = \Prj\Model\CdkeyUser::getRecord(null , ['words' => $words]);
            if(empty($keyRepeat)){
                break;
            }else{
                $words = strtoupper(\Lib\Misc\StringH::createCdk());
            }
            if($retry <= 0)break;
        }

        $hold = $this->hold(__METHOD__.':'.$userId);
        if(!$this->checkRes($hold))return $hold;
        //领用+1
        //入库
        try{
            \Prj\Model\CdkeyUser::startTransaction();
            $cdkey->incField('getCount' , 1);
            $ret = $cdkey->saveToDB();
            if(!$ret)$this->fatalErr('cdk扣减失败!!!');
            $ret = \Prj\Model\CdkeyUser::add([
                'cdkeyId' => $cdkeyId,
                'fromUserId' => $userId,
                'words' => $words,
                'statusCode' => \Prj\Model\CdkeyUser::$status_unused,
                'useTime' => date('Y-m-d H:i:s'),
            ]);
            if(!$ret)return $this->fatalErr('cdk入库失败!!!');
            \Prj\Model\CdkeyUser::commit();
        }catch (\Exception $e){
            \Prj\Model\CdkeyUser::rollback();
            return $this->resultError('服务器繁忙[3]');
        }
        return $this->resultOK([
            'words' => $words,
        ]);
    }

    /**
     * Hand 兑换码检查
     * @param array $cdkey
     * @return array
     */
    public function checkCdkey($cdkey = []){
        if($cdkey['statusCode'] != 1)return $this->returnMsg(100);
        if(time() < strtotime($cdkey['start']))return $this->returnMsg(101);
        if(time() > strtotime($cdkey['finish']))return $this->returnMsg(102);
        if(!empty($cdkey['count']) && $cdkey['getCount'] >= $cdkey['count'])
            return $this->returnMsg(103);
        $award = \Prj\Model\CdkeyAward::getCopy(['cdkeyId' => $cdkey['oid'] , 'statusCode' => 1]);
        $award->load();
        if(!$award->exists())return $this->resultError('服务器繁忙[award null]');
        $senderRes = $this->getSender($award->dump());
        if(!$this->checkRes($senderRes))return $senderRes;
        return $this->resultOK([
            'awardInfo' => $award->dump(),
            'senderObj' => $senderRes['data']['obj']
        ]);
    }

    /**
     * Hand 根据code返回信息
     * @param $num
     * @return array
     */
    protected function returnMsg($num){
        return $this->resultError($this->lang[$num]);
    }
}