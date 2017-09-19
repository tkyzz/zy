<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;
use EasyWeChat\Core\Exception;
use \Lib\Misc\Result;
use \Prj\Loger;

/**
 * Description of User
 *
 * @author simon.wang
 */
class Coupon extends _BllBase {

    protected $max_redpaket = 500;

    protected $max_coupon = 1000;
    /**
     * 添加一条券配置
     * productsMap : [ '003' => '悦月盈' ]
     * @param array $params
     * @return array
     */
   public function addCoupon($params = []){
       \Prj\Loger::$prefix = '[addCoupon]';

       if(!Result::paramsCheck($params , ['count']))
           return Result::get(RET_ERR , '参数错误['. Result::$errorParam .']');
       if(!$params['isFloat']){
           if(!Result::paramsCheck($params , ['upperAmount']))
           return Result::get(RET_ERR , '参数错误['. Result::$errorParam .']');
       }

       $oid = isset($params['oid']) ? $params['oid'] : \Lib\Misc\StringH::createOid();
       $params['description'] = isset($params['description']) ? $params['description'] : '全场适用';
       $params['type'] = isset($params['type']) ? $params['type'] : 'coupon';
       $params['amountType'] = isset($params['amountType']) ? $params['amountType'] : 'number';
       $params['disableDate'] = isset($params['disableDate']) ? $params['disableDate'] : 0;
       $params['investAmount'] = (isset($params['investAmount']) && !empty($params['investAmount'])) ? $params['investAmount'] : 0;
       $params['products'] = (isset($params['productsMap']) && !empty($params['productsMap'])) ?
           implode(',',array_values($params['productsMap'])) : '适用全场';
       //浮动金额的卡券需要定义额度
       if($this->isFloadCoupon($params)) {
           $params['upperAmount'] = 0;
           $params['isFloat'] = 1;
           $params['remainAmount'] = $params['totalAmount'];
           if($params['totalAmount'] <= 0)return $this->resultError('总额度不能为0');
       }else{
           $params['isFloat'] = 0;
           $params['totalAmount'] = $params['remainAmount'] = 0;
       }
       $params['remainCount'] = $params['count'];
       if($params['count'] <= 0)return $this->resultError('总数量不能为0');

       //本地库插入
       $coupon = \Prj\Model\Coupon::getCopy($oid);
       $coupon->load();
       if($coupon->exists())return Result::get(RET_ERR , '券记录已经存在!');
       $coupon->setField('createTime' , date('YmdHis'));
       $coupon->setField('status' , 'wait');
       $coupon->setField('isFloat' , $params['isFloat']);
       $coupon->setField('typeCode' , $params['type']);
       $coupon->setField('title' , $params['name']);
       $coupon->setField('labels' , is_array($params['labels']) ? implode(',' , $params['labels']) : '');
        $coupon->setField("purposeCode",$params['purposeCode']);
       $coupon->setField('count' , $params['count']);
       $coupon->setField('remainCount' , $params['remainCount']);
       $coupon->setField('updateTime' , date('YmdHis'));
       $coupon->setField('expire' , $params['disableDate']);
       $coupon->setField('investAmount' , $params['investAmount'] * 100);
       $coupon->setField('totalAmount' , $params['totalAmount'] * 100);
       $coupon->setField('remainAmount' , $params['remainAmount'] * 100);
       $coupon->setField('amount' , $params['upperAmount'] * 100);
       $coupon->setField('useCount' , 0);

       try{
           $ret = $coupon->saveToDB();
           if(!$ret)return Result::get(RET_ERR , '更新失败!');
           \Prj\Loger::out('本地券 '.$oid.' 入库成功...');
       }catch (\Exception $e){
           return Result::get(RET_ERR , $e->getMessage());
       }

       $params['oid'] = $oid;

       try{
           if(\Prj\Tool\System::isGh()){
               $res = \Prj\Bll\GH\Coupon::getInstance()->add($params); //更新国槐的表
           }else if(\Prj\Tool\System::isZy()){
               $res = \Prj\Bll\ZY\CouponLabel::getInstance()->add($params); //更新zy的表
           }else{
               $res = \Lib\Misc\Result::get(99999 , '系统配置错误!!!');
           }
       }catch (\Exception $e){
           $res = \Lib\Misc\Result::get(99999 , $e->getMessage());
       }

       if(!$this->checkRes($res)){
           //删除本地库的记录
           $ret = \Prj\Model\Coupon::deleteOne(['oid' => $oid]);
           if(!$ret){
               $msg = '更新失败,回滚错误! ';
               $this->fatalErr($msg);
           }
           return $this->resultError($res['message']);
       }

       return $this->resultOK();

   }

    /**
     * 更新一条券配置
     * @param $params
     * @return array
     */
   public function updateCoupon($params){
       if(!Result::paramsCheck($params , ['oid']))return Result::get(RET_ERR , '参数错误['.Result::$errorParam.']');
        //只能改发行数量和失效状态 注意发行数量不要小于领取数量
       $oid = $params['oid']; //*
       $coupon = \Prj\Model\Coupon::getCopy($oid);
       $coupon->load();
       if(!$coupon->exists())return Result::get(RET_ERR , '券信息不存在!');
       $couponInfo = $this->getRecords(['oid' => $oid])['data'][0];
       if(empty($couponInfo))return Result::get(RET_ERR , '券信息不存在!');
       $initData = $coupon->dump();//保存初始数据

       $updateData = [];
        if(isset($params['status']) && $params['status'] == 'no'){
            $coupon->setField('status' , 'no');
            $updateData['status'] = 'no';
        }else{
            //更新总数量,更新剩余数量
            if($params['count'] && $params['count'] >= 0){
                if($params['count'] < $couponInfo['getCount'])return Result::get(RET_ERR , '发行数量不能小于已领取的数量['. $couponInfo['getCount'] .']!');
                $updateData['count'] = $params['count'];
                $updateData['remainCount'] = $params['count'] - $couponInfo['getCount'];
                $coupon->setField('count' , $params['count']);
                $coupon->setField('remainCount' , $updateData['remainCount']);
            }else{
                return Result::get(RET_ERR , '发行数量不能小于0!');
            }

            if($this->isFloadCoupon($couponInfo)){
                //更新总额度,更新剩余额度
                if($params['totalAmount'] && $params['totalAmount'] >= 0){
                    if($params['totalAmount'] < $couponInfo['getAmount'])return Result::get(RET_ERR , '发行额度不能小于已领取的金额['. $couponInfo['totalAmount'] .']!');
                    $updateData['totalAmount'] = $params['totalAmount'];
                    $updateData['remainAmount'] = $params['totalAmount'] - $couponInfo['getAmount'];
                    $coupon->setField('totalAmount' , $params['totalAmount'] * 100);
                    $coupon->setField('remainAmount' , $updateData['remainAmount'] * 100);
                }else{
                    return Result::get(RET_ERR , '发行额度不能小于0!');
                }
            }
        }
       $ret = $coupon->saveToDB();
        if(!$ret)return Result::get(RET_ERR , '更新本地记录失败!');

        $updateData['oid'] = $oid;
//        $res = \Prj\Bll\GH\Coupon::getInstance()->editMore($updateData); //更新国槐的表
//
//        if(!$this->checkRes($res)){
//            //更新失败回滚
//            foreach ($initData as $k => $v){
//                $coupon->setField($k , $v);
//            }
//            $ret = $coupon->saveToDB();
//            if(!$ret){
//                $msg = 'tulip.coupon更新错误,回滚失败!';
//                Loger::getInstance()->sys_warning($msg);
//                return Result::get(RET_ERR , $msg);
//            }
//            return Result::get(RET_ERR , '更新失败!');
//        }
        return Result::get(RET_SUCC);
   }

    /**
     * 获取券的详细信息
     * @param array $where
     * @param null $sortgrpby
     * @param null $pageSize
     * @param int $rsFrom
     * @return array
     */
   public function getRecords($where = [], $sortgrpby = null,$pageSize = null,$rsFrom = 0){
        $data = \Prj\Model\Coupon::getRecords(null , $where, $sortgrpby,$pageSize,$rsFrom);
        if($data){
            foreach ($data as &$v){
                if(\Prj\Tool\System::isGh()){
                    $info = \Prj\Model\TulipCoupon::getOne(['oid' => $v['oid']]);
                    if(!empty($info)){
                        foreach ($info as $kk => $vv){
                            $v[$kk] = $vv;
                        }
                        $v['getCount'] = $v['count'] - $v['remainCount']; //领取数量
                        $v['getAmount'] = $v['totalAmount'] - $v['remainAmount']; //领取金额
                        $v['labels'] = explode(',' , $v['labels']);
                        $v['typeCH'] = \Prj\Model\Coupon::$type_map[$v['type']];
                        $v['isFloatCH'] = $v['isFloat'] ? '是' : '否';
                    }
                }else if(\Prj\Tool\System::isZy()){
                    $v['name'] = $v['title'];
                    $v['type'] = $v['typeCode'];
                    $v['amount'] = round($v['amount']/100 , 2);
                    $v['investAmount'] = round($v['investAmount']/100 , 2);
                    $v['totalAmount'] = round($v['totalAmount']/100 , 2);
                    $v['remainAmount'] = round($v['remainAmount']/100 , 2);
                    $v['upperAmount'] = $v['amount'];
                    $v['getCount'] = $v['count'] - $v['remainCount']; //领取数量
                    $v['getAmount'] = round($v['totalAmount'] - $v['remainAmount'] , 2); //领取金额
//                    $labelRes = \Prj\Bll\ZY\Coupon::getInstance()->getCouponLabelsDetail($v['oid']);
//                    if(!$this->checkRes($labelRes))return $labelRes;
                    $v['labels'] = explode(',',$v['labels']);
                    $v['typeCH'] = \Prj\Model\Coupon::$type_map[strtoupper($v['type'])];
                    $v['isFloatCH'] = $v['isFloat'] ? '是' : '否';
                }else{
                    $this->fatalErr('系统配置错误!!!');
                }

            }
        }
        return Result::get(RET_SUCC , '' , $data);
   }

    /**
     * Hand 获取一条券配置
     * @param array $where
     * @return array
     */
   public function getRecord($where = []){
       $res = $this->getRecords($where , null , 1);
       if(!$this->checkRes($res))return $res;
       return $this->resultOK([
            'info' => $res['data'][0],
       ]);
   }

/**
     * Hand 获取优惠券列表
     * content=列表,size=单页长度,total=总条数,totalPages=总页数
     * @param array $params
     * @return array
     */
   public function getUserCoupon($params = []){
       if(!\Lib\Misc\Result::paramsCheck($params , ['userId','status','page','rows'])){
           return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
       }

       $where = [
           'userId' => $params['userId'],
           'status' => $params['status'],
       ];
       $content = \Prj\Model\Tulip\UserCoupon::getRecords('' , $where , 'rsort finish' , $params['rows'] , ($params['page'] - 1) * $params['rows']);
       foreach ($content as &$v){
           if($params['type'] == 'old'){
               $v['finish'] .= ' 00:00:00';
           }else{
               $this->str2time($v['finish']);
               $this->str2time($v['start']);
               $this->str2time($v['leadTime']);
               $this->str2time($v['useTime']);
           }
           $v['amount'] = floatval($v['amount']);
           $v['minAmt'] = floatval($v['investAmount']);
       }
       $size = $params['rows'];
       $total = \Prj\Model\Tulip\UserCoupon::getRecord('count(1) as total' , $where)['total'];

       if($params['type'] == 'old'){
           return $this->resultOK([
               'rows' => $content,
               'size' => $size,
               'total' => $total,
               'totalPages' => ceil($total / $size),
           ]);
       }else{
           return $this->resultOK([
               'content' => $content,
               'size' => $size,
               'total' => $total,
               'totalPages' => ceil($total / $size),
           ]);
       }
   }

    /**
     * Hand 卡券参数检查
     * @param $couponTplInfo
     * @param null $amount
     * @return array
     */
   public function checkCouponTpl($couponTplInfo , $amount = null){
       \Prj\Loger::setKv('couponId' , $couponTplInfo['oid']);

       //系统支持的券类型
       if(!in_array(strtoupper($couponTplInfo['type']) , \Prj\Model\Coupon::$support_send_types)){
           return $this->resultError('目前还不支持此种类型卡券的发放#' . $couponTplInfo['type']);
       }
       if($couponTplInfo['status'] != 'yes')return $this->resultError('券已经失效');

       //浮动金额券的检查(红包和浮动券)
       if($this->isFloadCoupon($couponTplInfo)){
           if($amount <= 0)return $this->resultError('浮动券必须指定金额');
           if($couponTplInfo['remainAmount'] < $amount)return $this->resultError('券存额不足');
            //卡券面值极限值限制
           if($couponTplInfo['type'] ==  \Prj\Model\Coupon::type_coupon){
                if($amount > $this->max_coupon)return $this->resultError('不能大于卡券最大面值#'.$this->max_coupon);
           }else if($couponTplInfo['type'] ==  \Prj\Model\Coupon::type_redPackets){
               if($amount > $this->max_redpaket)return $this->resultError('不能大于红包最大面值#'.$this->max_redpaket);
           }
       }else{
           if($amount > 0 && $amount != $couponTplInfo['upperAmount'])return $this->resultError('非浮动券禁止指定金额');
           if($couponTplInfo['upperAmount'] <= 0)return $this->resultError('抵用券金额错误');
           if($couponTplInfo['remainCount'] <= 0)return $this->resultError('券余量不足');
       }

       return $this->resultOK();
   }

    /**
     * Hand 是否是浮动金额卡券
     * @param array $params
     * @return bool
     */
   public function isFloadCoupon($params = []){
       if(!\Lib\Misc\Result::paramsCheck($params , ['isFloat'])){
           $this->fatalErr('参数错误#'.\Lib\Misc\Result::$errorParam);
       }
        if($params['isFloat'] == 1){
            return true;
        }else{
            return false;
        }
   }

    /***
     * Hand 给用户发放指定模板的卡券
     * @param $userId
     * @param $couponId
     * @param null $amount 单位分
     * @param null $eventId
     * @param null $inventAmount
     * @param null $expire
     * @return array
     */
   public function sendCouponByCouponTplId($userId , $couponId , $amount = null , $eventId = null , $inventAmount = null , $expire = null){
       if(empty($userId))return $this->resultError('参数错误#userId');
       if(empty($couponId))return $this->resultError('参数错误#couponId');
       \Prj\Loger::setKv('couponId' , $couponId);
       \Prj\Loger::setUid($userId);

       if($amount)$amount = round($amount / 100 , 2); //amount 转化成元

       $res = $this->getRecord(['oid' => $couponId]);
       if(!$this->checkRes($res))return $res;

       $couponInfo = $res['data']['info'];
       \Prj\Loger::outVal('couponInfo' , $res);
       if(empty($couponInfo))return $this->resultError('不存在的券配置');

       //检查券的合法性
       $res = $this->checkCouponTpl($couponInfo , $amount);
       if(!$this->checkRes($res))return $res;
       //检查活动的合法性
       if($eventId){
           $res = \Prj\Bll\EventCoupon::getInstance()->checkEvent($eventId);
           if(!$this->checkRes($res))return $res;
       }

       //开始发券
       return $this->addUserCoupon([
           'couponInfo' => $couponInfo,
           'userId' => $userId,
           'amount' => $amount,
           'eventId' => $eventId,
           'investAmount' => $inventAmount,
           'expire' => $expire,
       ]);
   }

    /**
     * 在国槐库,给用户添加一张卡券
     * 必要参数 'couponInfo','userId'
     * amount 单位元
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
           'type','oid','name','investAmount'
       ])){
           return $this->resultError('券参数错误#' . \Lib\Misc\Result::$errorParam);
       }
       $couponInfo = $params['couponInfo'];
       $this->log($couponInfo , '$couponInfo');
       $isFloat = $this->isFloadCoupon($couponInfo);
       if($isFloat){
           if($params['amount'] <= 0)return $this->resultError('浮动券必须指定金额');
           $amount = $params['amount'];
       }else{
           if($params['investAmount'] !== null)return $this->resultError('非浮动券禁止指定起投金额');
           if($params['expire'] !== null)return $this->resultError('非浮动券禁止指定有效期');
           $amount = $couponInfo['upperAmount'];
       }

       $params['amount'] = $amount;
       $params['isFloat'] = $isFloat;
       $roll = [
           'oid' => $couponInfo['oid']
       ]; //记录需要回滚的操作
       $this->log($isFloat , 'isfloat');
       if($isFloat){
           $changeAmount = round(-1 * $amount * 100 );
           $ret = \Prj\Model\Coupon::updateNum($couponInfo['oid'] , 'remainAmount' , $changeAmount);
           if(!$ret)return $this->resultError('卡券余额扣减失败');
           $roll['remainAmount'] = $changeAmount;
       }
       $ret = \Prj\Model\Coupon::updateNum($couponInfo['oid'] , 'remainCount' , -1);
       if(!$ret){
           $this->rollBack($roll);
           return $this->resultError('更新失败[1]');
       }
       $roll['remainCount'] = -1;

       if(\Prj\Tool\System::isGh()){
           $userCoupon = \Prj\Bll\GH\UserCoupon::getInstance();
       }else if(\Prj\Tool\System::isZy()){
           $userCoupon = \Prj\Bll\ZY\UserCoupon::getInstance();
       }else{
           $this->fatalErr('Removal.Main.system 配置异常');
       }

       $res = $userCoupon->add($params);
       if(!$this->checkRes($res)){
           $this->rollBack($roll);
       }

       return $res;
   }

   protected function rollBack($params = []){
        if(isset($params['remainAmount'])){
            $ret = \Prj\Model\Coupon::updateNum($params['oid'] , 'remainAmount' , -1 * $params['remainAmount']);
            if(!$ret)$this->fatalErr('回滚失败[1]');
        }
        if(isset($params['remainCount'])){
            $ret = \Prj\Model\Coupon::updateNum($params['oid'] , 'remainCount' , -1 * $params['remainCount']);
            if(!$ret)$this->fatalErr('回滚失败[2]');
        }
   }

    /**
     * Hand 根据标签查询可用的券
     * @param array $labelCodes
     * @return array
     */
   public function getCouponIdByLabelCodes($labelCodes = []){
        if(empty($labelCodes))return $this->resultError('标签列表不能为空');
        $list = \Prj\Model\TulipCouponRange::getRecords('' , [
            'labelCode' => $labelCodes,
        ]);
        if(empty($list))return $this->resultOK(['list' => []]);
        foreach ($list as $v){
            $info[] = $v['couponBatch'];
        }
        return $this->resultOK(['list' => $info]);
   }





    public function getCouponDetail($params)
    {
        if (!Result::paramsCheck($params, ['ucId', 'userId'])) {
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $params['couponType'] = ['COUPON', 'RATECOUPON'];
        $params['couponStatus'] = 'NOTUSED';
        $productId = $params['productId'];
        unset($params['productId']);
        $field = "ucId,userId,name,couponId,couponType,couponStatus,couponAmount,limitInvestAmount,limitLabels,isLimitLabel";
        $coupon = \Prj\Model\ZyBusiness\UserCoupon::getRecord($field, $params);
        if(empty($coupon)) return $this->resultOK([]);
        $cashBalance = \Prj\Model\Payment\InvestorAssetTotal::getWallet($params['userId']);

        $coupon['chargeAmount'] = ($coupon['limitInvestAmount'] > $cashBalance) ? floatval($coupon['limitInvestAmount'] - $cashBalance) : 0;

        if(!$coupon['isLimitLabel']) return $this->resultOK([]);
        $label = \Prj\Model\ZyBusiness\CouponLabel::getRecords("labelId",['couponId'=>$coupon['couponId']]);

        $where = array();
        foreach ($label as $k=>$v){
            $sep = "";
            for ($i=0;$i<$k;$i++) $sep .= " ";
            $where["|"]["*labels".$sep] = "%,".$v['labelId'].",%";
        }
        switch ($coupon['couponType']){
            case 'COUPON':$coupon['couponTypeCh'] = "代金券";break;
            case "RATECOUPON":$coupon['couponTypeCh'] = "加息券";break;
        }
        $where['productId'] = $productId;
        $where['productStatus'] = ['RAISING','DOING_RAISING'];
        $where['!weight'] = 0;
        $productList =  \Prj\Model\Product::getRecords("listJson",$where,'sort weight');
        $list = [];
        $isTiro = \Prj\Bll\Product::getInstance()->getUserTiro();
        foreach ($productList as $k => $v){
            $data = json_decode($v['listJson'],true);

            if(!$isTiro&&substr($data['weight'],0,1)==\Prj\Bll\Product::state_newbie) continue;
            $list[] = $data;

        }

        $coupon['recommendProducts'] = $list?$list:array();
        return $this->resultOK($coupon);
    }




    public function delCoupon($base64str){
       try {
           $pkey = json_decode(hex2bin($base64str), true);
           $ret = \Prj\Model\Coupon::deleteOne($pkey);
           if($ret === true) $this->resultError("删除错误!");
           return $this->resultOK([]);
       }catch (Exception $ex){
           return $this->resultError("删除错误!".$ex->getMessage());
       }
    }



    public function updCoupon($params = []){
        \Prj\Loger::$prefix = '[addCoupon]';

        if(!Result::paramsCheck($params , ['count']))
            return Result::get(RET_ERR , '参数错误['. Result::$errorParam .']');
        if(!$params['isFloat']){
            if(!Result::paramsCheck($params , ['upperAmount']))
                return Result::get(RET_ERR , '参数错误['. Result::$errorParam .']');
        }

        $oid = isset($params['oid']) ? $params['oid'] : \Lib\Misc\StringH::createOid();
        $params['description'] = isset($params['description']) ? $params['description'] : '全场适用';
        $params['type'] = isset($params['type']) ? $params['type'] : 'coupon';
        $params['amountType'] = isset($params['amountType']) ? $params['amountType'] : 'number';
        $params['disableDate'] = isset($params['disableDate']) ? $params['disableDate'] : 0;
        $params['investAmount'] = (isset($params['investAmount']) && !empty($params['investAmount'])) ? $params['investAmount'] : 0;
        $params['products'] = (isset($params['productsMap']) && !empty($params['productsMap'])) ?
            implode(',',array_values($params['productsMap'])) : '适用全场';
        //浮动金额的卡券需要定义额度
        if($this->isFloadCoupon($params)) {
            $params['upperAmount'] = 0;
            $params['isFloat'] = 1;
            $params['remainAmount'] = $params['totalAmount'];
            if($params['totalAmount'] <= 0)return $this->resultError('总额度不能为0');
        }else{
            $params['isFloat'] = 0;
            $params['totalAmount'] = $params['remainAmount'] = 0;
        }
        $params['remainCount'] = $params['count'];
        if($params['count'] <= 0)return $this->resultError('总数量不能为0');

        //本地库插入
        $coupon = \Prj\Model\Coupon::getCopy($oid);
        $coupon->load();
//        if($coupon->exists())return Result::get(RET_ERR , '券记录已经存在!');
        $coupon->setField('createTime' , date('YmdHis'));
        $coupon->setField('status' , 'wait');
        $coupon->setField('isFloat' , $params['isFloat']);
        $coupon->setField('typeCode' , $params['type']);
        $coupon->setField('title' , $params['name']);
        $coupon->setField('labels' , is_array($params['labels']) ? implode(',' , $params['labels']) : '');
        $coupon->setField("purposeCode",$params['purposeCode']);
        $coupon->setField('count' , $params['count']);
        $coupon->setField('remainCount' , $params['remainCount']);
        $coupon->setField('updateTime' , date('YmdHis'));
        $coupon->setField('expire' , $params['disableDate']);
        $coupon->setField('investAmount' , $params['investAmount'] * 100);
        $coupon->setField('totalAmount' , $params['totalAmount'] * 100);
        $coupon->setField('remainAmount' , $params['remainAmount'] * 100);
        $coupon->setField('amount' , $params['upperAmount'] * 100);
        $coupon->setField('useCount' , 0);

        try{
            $ret = $coupon->saveToDB();
            if(!$ret)return Result::get(RET_ERR , '更新失败!');
            \Prj\Loger::out('本地券 '.$oid.' 入库成功...');
        }catch (\Exception $e){
            return Result::get(RET_ERR , $e->getMessage());
        }

        $params['oid'] = $oid;

        try{
            if(\Prj\Tool\System::isGh()){
                $res = \Prj\Bll\GH\Coupon::getInstance()->add($params); //更新国槐的表
            }else if(\Prj\Tool\System::isZy()){
                $res = \Prj\Bll\ZY\CouponLabel::getInstance()->add($params); //更新zy的表
            }else{
                $res = \Lib\Misc\Result::get(99999 , '系统配置错误!!!');
            }
        }catch (\Exception $e){
            $res = \Lib\Misc\Result::get(99999 , $e->getMessage());
        }

        if(!$this->checkRes($res)){
            //删除本地库的记录
            $ret = \Prj\Model\Coupon::deleteOne(['oid' => $oid]);
            if(!$ret){
                $msg = '更新失败,回滚错误! ';
                $this->fatalErr($msg);
            }
            return $this->resultError($res['message']);
        }

        return $this->resultOK();

    }

}
