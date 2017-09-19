<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-13 10:47
 */

namespace Lib\Services;

use Prj\Tool\TimeTool;
use Prj\Tool\Random;
use Sooh2\Misc\Ini;

/**
 * Class CheckinBook
 * @package Lib\Services
 */

class SendCoupon
{
    protected static $type_conpon = 'coupon';

    protected static $type_redPackets = 'redPackets';

    public static $typeMap = [
        '002' => ['productCode' => '002', 'productName' => '新手标'],
        '004' => ['productCode' => '004', 'productName' => '稳定收益'],
        '005' => ['productCode' => '005', 'productName' => '悦享盈'], //90天 or 120天
        '003' => ['productCode' => '003', 'productName' => '悦月盈'], //30天
        '001' => ['productCode' => '001', 'productName' => '掌薪宝'],
    ];
    /**
     * 发放卡券的配置
     * @var array
     */
    protected $voucherReqContentConfig = [
        'couponType' => 'coupon',
        'description' => '签到红包',
        'disableDate' => 0,
        'investAmount' => 10000,
        'name' => '签到红包',
        'totalAmount' => 0,
        'upperAmount' => 0,
        'userList' => [
            ['userId' => ''],
        ],
        'productList' => [
            ['productCode' => '003', 'productName' => '悦月盈'],
        ],
        'validPeriod' => 1,
        'weight' => 'any',
    ];

    protected $userId;

    protected $eventId;

    protected $reqOid;

    public function setDesc($desc){
        $this->voucherReqContentConfig['description'] = $desc;
        return $this;
    }

    public function setDisableDate($disableDate){
        $this->voucherReqContentConfig['disableDate'] = $disableDate ? $disableDate : 0;
        return $this;
    }

    public function setCouponType($couponType){
        $this->voucherReqContentConfig['couponType'] = $couponType ? $couponType : (self::$type_conpon);
        return $this;
    }

    public function setProductList($productList){
        if($productList)$this->voucherReqContentConfig['productList'] = $productList;
        return $this;
    }

    public function setInvestAmount($amount){
        if($amount)$this->voucherReqContentConfig['investAmount'] = $amount;
        return $this;
    }

    public function setAmount($amount){
        $this->voucherReqContentConfig['totalAmount'] = $amount;
        $this->voucherReqContentConfig['upperAmount'] = $amount;
        return $this;
    }

    public function setEventId($eventId){
        $this->eventId = $eventId ? $eventId : '';
        return $this;
    }

    public function getAmount($type = 'fen'){
        if($type == 'fen')return round($this->voucherReqContentConfig['totalAmount'] * 100);
        return $this->voucherReqContentConfig['totalAmount'];
    }

    public function setUserId($userId){
        $this->voucherReqContentConfig['userList'] = [
            ['userId' => $userId]
        ];
        $this->userId = $userId;
        return $this;
    }

    public function setName($name){
        $this->voucherReqContentConfig['name'] = $name;
        return $this;
    }

    public function setReqOid($reqOid = null){
        $reqOid = $reqOid ? $reqOid : $this->createReqOid($this->userId);
        $this->reqOid = $reqOid;
        return $this;
    }

    public function createReqOid($userId = ''){
        return \Lib\Misc\StringH::createOid();
    }

    public function getReqOid(){
        return $this->reqOid;
    }

    /**
     * 调用java发放自定义卡券
     * @return bool
     */
    public function sendCouponToUser()
    {
        $voucherContent = $this->voucherReqContentConfig;

        $reqOid = $this->getReqOid();
        $arrLogin = [
            'account' => Ini::getInstance()->getIni('application.adminlogin.name'),
            'password' => Ini::getInstance()->getIni('application.adminlogin.passwd'),
            'system' => Ini::getInstance()->getIni('application.adminlogin.system'),
        ];

        $url ='http://' . Ini::getInstance()->getIni('application.serverip.mimosaold') . Ini::getInstance()->getIni('Urls.javaApiLoginGuoHuai');
        //login
        $Curl = \Sooh2\Curl::factory();
        \Prj\Loger::out(__METHOD__.' 请求参数: ' . json_encode(['reqOid' => $reqOid, 'reqContent' => $voucherContent] , 256));
        $flag = $Curl->httpPost('http://' . Ini::getInstance()->getIni('application.serverip.mimosaold') . Ini::getInstance()->getIni('Urls.javaApiLoginGuoHuai'), $arrLogin);
        \Prj\Loger::out(__METHOD__.' 后台登录: '.$flag);
        $couponUrl = 'http://' . Ini::getInstance()->getIni('application.serverip.tulipold') . Ini::getInstance()->getIni('Urls.javaApiSendCouponToUser');
        \Prj\Loger::out(__METHOD__.' 请求地址: ' .$couponUrl);
        $ret = $Curl->httpGet($couponUrl, ['reqOid' => $reqOid, 'reqContent' => \Sooh2\Util::toJsonSimple($voucherContent)]);
        \Prj\Loger::out(__METHOD__.' 发券结果: ' .$ret);
        $ret = json_decode($ret, true);


//        $ret = [
//            'errorCode' => 0,
//        ];
        if ($ret && !empty($ret) && isset($ret['errorCode']) && $ret['errorCode'] === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 调用java发放活动卡券
     * @return bool
     */
    public function sendCouponToUser2(){
        $voucherContent = [
            'amount' => 0,
            'eventId' => $this->eventId,
            'userList' => $this->voucherReqContentConfig['userList'],
        ];

        $reqOid = $this->getReqOid();
        $arrLogin = [
            'account' => Ini::getInstance()->getIni('application.adminlogin.name'),
            'password' => Ini::getInstance()->getIni('application.adminlogin.passwd'),
            'system' => Ini::getInstance()->getIni('application.adminlogin.system'),
        ];

        $url ='http://' . Ini::getInstance()->getIni('application.serverip.mimosaold') . Ini::getInstance()->getIni('Urls.javaApiLoginGuoHuai');
        //login
        $Curl = \Sooh2\Curl::factory();
        \Prj\Loger::out(__METHOD__.' 请求参数: ' . json_encode(['reqOid' => $reqOid, 'reqContent' => $voucherContent] , 256));
        $flag = $Curl->httpPost('http://' . Ini::getInstance()->getIni('application.serverip.mimosaold') . Ini::getInstance()->getIni('Urls.javaApiLoginGuoHuai'), $arrLogin);
        \Prj\Loger::out(__METHOD__.' 后台登录: '.$flag);
        $couponUrl = 'http://' . Ini::getInstance()->getIni('application.serverip.tulipold') . Ini::getInstance()->getIni('Urls.javaApiSendCouponToUser2');
        \Prj\Loger::out(__METHOD__.' 请求地址: ' .$couponUrl);
        $ret = $Curl->httpGet($couponUrl, ['reqOid' => $reqOid, 'reqContent' => \Sooh2\Util::toJsonSimple($voucherContent)]);
        \Prj\Loger::out(__METHOD__.' 发券结果: ' .$ret);
        $ret = json_decode($ret, true);


//        $ret = [
//            'errorCode' => 0,
//        ];
        if ($ret && !empty($ret) && isset($ret['errorCode']) && $ret['errorCode'] === 0) {
            return true;
        } else {
            return false;
        }
    }
}
