<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-13 10:47
 */

namespace Lib\Services;

use Prj\Model\SignIn;
use Sooh2\Misc\Ini;

/**
 * Class CheckinBook
 * @package Lib\Services
 */
class CheckinBook
{
    const errAccountNotExist = '帐号错误';//帐号不存在
    const errCheckinClosed = '签到功能已经关闭';//签到功能已经关闭
    const errTodayDone = '今天已经签到过了';//今天已经签到过了
    const errUnknown = '位置错误';
    const msgCheckinDone = '签到成功';
    const checkSwitch = 1;//是否开启签到

    /**
     * 发放卡券的配置
     * @var array
     */
    protected $voucherReqContentConfig = [
        'couponType' => 'coupon',
        'description' => '签到红包',
        'disableDate' => 0,
        'investAmount' => 100,
        'name' => '签到红包',
        'totalAmount' => 0,
        'upperAmount' => 0,
        'userList' => [
            ['userId' => '111111111111111111'],
        ],
        'productList' => [[], []],
        'validPeriod' => 1,
        'weight' => 'any',
    ];
//    protected $amountRule = [
//        '200' => 1000
////        '24_35' => 230,
////        '36_47' => 1355,
////        '48_59' => 3415,
////        '60_71' => 3415,
////        '72_83' => 1355,
////        '84_96' => 230,
//    ];
    protected $rewardRule = [
        ['productCode' => '4', 'productName' => '悦享盈'],
//        ['productCode' => '3', 'productName' => '悦月盈'],
    ];

    protected $checkinNum = 1;
    static $maxNum = 7;//最大签到次数
    static $maxAmount = 200;//固定金额

    public $userId;//用户ID
    public $UserModel;//用户Model
    public $historyList;//本轮签到历史
    public $rewardNums;//奖励次数
    public $lastRewardDate = 0;//最后一次奖励日期
    public $thisReward;//本次奖励内容

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->UserModel = \Prj\Model\User::getCopy($userId);
        $this->UserModel->load();
        if ($this->UserModel->exists()) {
            $this->getHistoryList();
        } else {
            throw new \Exception('user model isnt exists!');
        }
    }

    public function configGetter($name)
    {
        switch ($name) {
            case 'rewardRule':
                $tmpProductName = \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_coupon_type_name');
                $tmpProductCode = \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_coupon_type_code');
                $arrProductName = explode(',', $tmpProductName);
                $arrProductCode = explode(',', $tmpProductCode);
                $_tmpReward = [];
                for ($i = 0; $i < count($arrProductName); $i++) {
                    $_tmpReward[] = [
                        'productCode' => $arrProductCode[$i],
                        'productName' => $arrProductName[$i],
                    ];
                }
                return $_tmpReward;
            case 'rewardRuleLast':
                $tmpProductName = \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_coupon_type_name_last');
                $tmpProductCode = \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_coupon_type_code_last');
                $arrProductName = explode(',', $tmpProductName);
                $arrProductCode = explode(',', $tmpProductCode);
                $_tmpReward = [];
                for ($i = 0; $i < count($arrProductName); $i++) {
                    $_tmpReward[] = [
                        'productCode' => $arrProductCode[$i],
                        'productName' => $arrProductName[$i],
                    ];
                }
                return $_tmpReward;

//                ];
//                    'productName' => \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_coupon_type_name'),
//                    'productCode' => \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_coupon_type_code'),
//                return [
            case 'name':
                return \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_coupon_name');
            case 'maxAmount':
                return \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_amount_final') * 100;
            case 'investAmount':
                return \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_amount_invest');
            case 'investAmountLast':
                return \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_amount_invest_last');
            case 'amountRule':
                return json_decode(\Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_amount_rand'), true);
            default :
                return null;
        }
    }

    /**
     * 获取奖励的历史列表
     * @author lingtima@gmail.com
     */
    public function getHistoryList()
    {
        $checkInBook = $this->UserModel->getField('checkinBook');
        $this->historyList = $checkInBook;
        $this->parseHistory();
        return $this->historyList;
    }

    /**
     * 解析奖励的历史列表
     * @author lingtima@gmail.com
     */
    protected function parseHistory()
    {
        $list = $this->historyList;

        if (!$list) {
            $this->lastRewardDate = null;
            $this->rewardNums = 0;
        } else {
            $this->lastRewardDate = $list['lastRewardDate'];
            $this->rewardNums = count($list['reward']);
        }
    }

    /**
     * 生成本次的奖励内容
     * @author lingtima@gmail.com
     */
    public function produceReward()
    {
        $nowNums = $this->rewardNums + 1;
        if ($nowNums == self::$maxNum) {
            $this->thisReward = [
                'amount' => $this->configGetter('maxAmount'),
                'reward' => $this->configGetter('rewardRuleLast'),
            ];
        } else {
            $this->thisReward = [
                'amount' => \Prj\Tool\Random::getInstance()->randomInScopeAsArray($this->configGetter('amountRule')),
                'reward' => $this->configGetter('rewardRule'),
            ];
        }
        return $this->thisReward;
    }

    /**
     * 生成固定的奖励列表
     * @return array
     * @author lingtima@gmail.com
     */
    public function produceStaticRewardList()
    {
        $list = [];
        for ($i = 0; $i < self::$maxNum; $i++) {
            if ($i < self::$maxNum - 1) {
                $list[] = [
                    'amount' => 0,
                    'reward' => $this->configGetter('rewardRule'),
                ];
            } else {//最后一次签到
                $list[] = [
                    'amount' => $this->configGetter('maxAmount'),
                    'reward' => $this->configGetter('rewardRule'),
                ];
            }
        }

        return $list;
    }

    /**
     * 发放奖励
     * @author lingtima@gmail.com
     */
    public function giveReward()
    {
        $this->produceReward();
        $ret = $this->sendCouponToUser($this->userId, $this->thisReward['amount'], $this->thisReward['reward']);
        if ($ret) {
            //更新已经获取的奖励内容
            $today = time();

            if (!empty($this->historyList) && isset($this->historyList['reward']) && count($this->historyList['reward']) != 7) {
                $nowList = array_merge($this->historyList['reward'], [['ymd' => $today * 1000, 'bonus' => $this->thisReward]]);
            } else {
                $nowList = [['ymd' => $today * 1000, 'bonus' => $this->thisReward]];
            }
            $this->historyList = ['lastRewardDate' => date('Ymd', $today), 'reward' => $nowList];

            $this->UserModel->setField('checkinBook', json_encode($this->historyList));
            $this->UserModel->incField('checkinNum', 1);
            $this->UserModel->incField('checkinAmount', $this->thisReward['amount']);
            $this->UserModel->saveToDB();

            $ModelCheckInRet = \Prj\Model\CheckIn::add($this->userId, date('Ymd', $today), $this->thisReward, count($nowList), $ret['oid']);
            //TODO 写入旧版的签到记录
            SignIn::add($this->userId, $today);

            return true;
        }

        return $ret;
    }

    /**
     * 调用java发放卡券接口
     * @param $userId
     * @param $amount
     * @param $reward
     * @return bool
     * @author lingtima@gmail.com
     */
    protected function sendCouponToUser($userId, $amount, $reward)
    {
        if ($this->rewardNums + 1 == self::$maxNum) {
            $investAmount = $this->configGetter('investAmountLast');
        }  else {
            $investAmount = $this->configGetter('investAmount');
        }

//        \Sooh2\Misc\Loger::getInstance()->app_trace('========开始调用发券接口');
        $sender = \Lib\Services\SendCouponLocal::getInstance();

//        \Sooh2\Misc\Loger::getInstance()->app_trace(\Sooh2\Misc\Ini::getInstance()->getIni('coupon.signin.couponId'));
        $ret = $sender->setCouponId(\Sooh2\Misc\Ini::getInstance()->getIni('coupon.signin.couponId'))
            ->setAmount($amount)
            ->setExpire(0)
            ->setInvestAmount($investAmount)
            ->sendCoupon($userId);

//        \Sooh2\Misc\Loger::getInstance()->app_trace('发券结果');
//        \Sooh2\Misc\Loger::getInstance()->app_trace($ret);
        return $ret['code'] == 10000 ? $ret['data']['info'] : false;
//        $sender->setUserId($userId)
//            ->setReqOid(md5($userId . time()))
//            ->setName($this->configGetter('name'))
//            ->setDesc($this->configGetter('name'))
//            ->setAmount(round($amount / 100, 2))
//            ->setProductList($reward)
//            ->setInvestAmount($investAmount)
//            ->setCouponType('coupon')
//            ->setDisableDate(0);

//        return $sender->sendCouponToUser();
    }

    /**
     * 获取今日签到的券ID
     * @param null $ymd
     * @return null
     * @author lingtima@gmail.com
     */
    public function getTodayConponId($ymd = null)
    {
        if (empty($ymd)) {
            $ymd = date('Ymd');
        }

        $ModelCheckin = \Prj\Model\CheckIn::getCopy($this->userId, $ymd);
        $ModelCheckin->load();
        if ($ModelCheckin->exists()) {
            return $ModelCheckin->getField('couponId');
        }
        return null;
    }
}
