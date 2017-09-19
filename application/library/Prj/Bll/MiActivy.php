<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;
use Lib\Misc\ArrayH;
use Lib\Misc\Result;
use Lib\Misc\StringH;
use Prj\EvtMsg\JavaApiPush;
use Prj\Loger;

/**
 * Description of User
 *
 * @author simon.wang
 */
class MiActivy extends _BllBase {

    /**
     * Hand 小米活动
     * @return array|bool|void
     * @throws \Exception
     */
    public function runPushForMi(){
        $this->log('【开始小米活动推送】...');
        if($this->channels){
            foreach($this->channels as $v){
                $this->setCode($v)->groupPush(); //执行推送
            }
        }
        return true;
    }

    /**
     * Hand 获取日期信息
     * @return mixed
     */
    public function getDateInfo(){
        $dateRet['data']['date'] = [
            $this->getIni('start'),
            $this->getIni('finish'),
        ];
        $dateRet['data']['addr_end'] = $this->getIni('addrend');
        if(date('YmdHis') > $dateRet['data']['addr_end']){
            $dateRet['data']['canSet'] = 0;
        }else{
            $dateRet['data']['canSet'] = 1;
        }
        $dateRet['data']['server_time'] = date('YmdHis');
        return $dateRet['data'];
    }

    /**
     * Hand 检查用户是否合法
     * @param $userInfo
     * @return array
     */
    public function checkUser($userInfo){
        if(empty($userInfo))return Result::get(RET_ERR , '用户不存在!');
        $phone = $userInfo['userAcc'];
        $channelId = $userInfo['channelid'];
        //渠道检查
        $channels = $this->channels;
        \Prj\Loger::setPhone($phone);
        \Prj\Loger::out('用户手机号: '.$phone.' 用户渠道号: '.$channelId .' from jz_db.tb_user_0 ');
        foreach ($channels as $code){
            $this->setCode($code);
            $channelIds = $this->channelId;
            $chennelGet = false; //渠道命中
            foreach ($channelIds as $v){
                if(strlen($v) != 4){
                    if($channelId == $v){
                        $chennelGet = true;
                        break;
                    }
                }else{
                    if(substr($channelId , 0 , 4) == $v){
                        $chennelGet = true;
                        break;
                    }
                }
            }

            if($chennelGet){
                \Prj\Loger::out('用户属于渠道: '.$code.' channelId: '.$channelId);
                //注册时间检查
                $registerHis = date('YmdHis' , strtotime($userInfo['createTime']));
                $startHis = $this->getIni('start');
                $finishHis = $this->getIni('finish');
                \Prj\Loger::out('注册时间: '.$registerHis .' '.$code.' 活动时间: '.$startHis.'~'.$finishHis);
                if($registerHis < $startHis || $registerHis > $finishHis){
                    return Result::get(RET_ERR , '仅限参与活动的用户参加~');
                }
                return Result::get(RET_SUCC , 'success' , [
                    'code' => $code
                ]);
            }

        }
        return Result::get(RET_ERR , '仅限参与活动的用户参加~');
    }

    public function echoRewardList(){
        foreach($this->channels as $v){
            echo '【'.$v.'】' . "\n";
            $this->setCode($v);
            \Prj\Loger::out($this->channelId);
            $start = date('Y-m-d H:i:s' , strtotime($this->getIni('start')));
            $finish = date('Y-m-d H:i:s' , strtotime($this->getIni('finish')));
            $list = $this->getUserByChannelId(0 , 9999 , $this->channelId , $start , $finish);
            foreach ($list as $vv){
                if($this->canGetReward($vv['oid'])){
                    echo $vv['oid'].','.$vv['userAcc'].','.json_encode($this->rewards , 256)."\n";
                }
            }
        }

    }

    /**
     * Hand 发送欢迎短信
     * @param array $userInfo
     * @return array
     */
    public function sendRigisterSMS($userInfo = []){
        $res = $this->checkUser($userInfo);
        if(!$this->checkRes($res)){
            return $res;
        }
        //发送注册短信
        $msgArr = $this->getIni('registerSMS');
        if(is_array($msgArr)){
            $msg = $msgArr[$userInfo['channelid']] ?: $msgArr[substr($userInfo['channelid'] , 0 , 4)];
        }else{
            $msg = $msgArr;
        }

        if(empty($msg)){
            $this->log('channelId: '.$userInfo['channelid']);
            \Prj\Loger::outVal('msgArr', $msgArr);
            return $this->resultError('没有可以匹配的短信内容');
        }
        \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($this->code,
            //短链
            $msg,
            $userInfo['oid'] , array('smsnotice'), $this->code);
        return $this->resultOK();
    }


//=============================================================================

    protected $rewards; //默认奖品列表

    protected $channels; //所有的活动合集

    protected $channelId; //渠道ID

    protected $code; //活动代号

    protected $productIds; //活动产品

    protected $rule; //奖品配置

    protected $newRules; //奖品配置2.0

    protected $config = [
        'channelInfos' => [],
        'durationPeriodDays' => 120,
    ];

    /**
     * Hand 初始化
     * @return bool
     */
    protected function init(){
        \Prj\Loger::$prefix = '[MiActivy]';
        \Prj\Tool\Debug::forcePro();
        parent::init();
        $arr = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.miActivityGroup.arr');
        $this->channels = $arr ? $arr : [];
        return true;
    }

    /**
     * Hand 设置活动代号
     * @param $code
     * @return $this
     */
    protected function setCode($code){
        \Prj\Loger::setKv('MiCode' , $code);
        $this->code = $code;
        $this->rewardsInit();

        $channelId = is_array($this->getIni('channel')) ?
            $this->getIni('channel') : explode(',' , $this->getIni('channel'));
        if($channelId){
            foreach ($channelId as &$v){
                $v = trim($v);
            }
        }
        $this->channelId = $channelId;

        return $this;
    }

    /**
     * Hand 奖品列表初始化
     * @return bool
     */
    protected function rewardsInit(){
        try{
        $rule = $this->getIni('rule');
        krsort($rule);
        }catch (\Exception $e){
            $newRules = $this->getIni('newRules');
            foreach ($newRules as $k => $v){
                $arr = json_decode($v , true);
                if(empty($arr))$this->fatalErr('配置参数不合法!!!');
                $this->newRules[$k] = array_merge((array)$this->newRules[$k] , $arr);
            }
        }

        if($this->rule){
        $this->rule = $rule;
        foreach ($rule as $v){
            $this->rewards[$v] = 0;
        }
        }else if($this->newRules){
            foreach ($this->newRules as $v){
                foreach ($v as $vv){
                    $this->rewards[$vv] = 0;
                }
            }
        }else{
            $this->fatalErr('非法的奖品配置!!!');
        }

        \Prj\Loger::outVal('rewards' , $this->rewards);
        return true;
    }

    /**
     * Hand 读取对应配置
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    protected function getIni($key){
        $code = $this->code;
        $k = 'Activity.'.$code.'.'.$key;
        $val = \Sooh2\Misc\Ini::getInstance()->getIni($k);
        if($val === null)throw new \Exception('配置缺失#'.$k);
        return $val;
    }
    /**
     * Hand 获取渠道配置信息
     * @return array
     */
    public function getChannelInfos(){
        foreach ($this->channels as $v){
            $this->config['channelInfos'][$v] = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.' . $v);
            if(empty($this->config['channelInfos'][$v])){
                return Result::get(RET_ERR , '缺少渠道配置['. $v .']');
            }
        }
        return Result::get(RET_SUCC , '' , [
            'list' => $this->config['channelInfos']
        ]);
    }

    /**
     * 获取小米过来的用户列表
     * @param int $limit
     * @param int $len
     * @return bool|\mysqli_result
     */
    protected function getMiUserList($limit = 0 , $len = 9999){
        $ret = \Prj\Model\UcUser::getRecords('*',[
            'left(channelid , 4)' => $this->config['channelId']
        ],'rsort createTime',$len , $limit);
        return $ret;
    }

    /**
     * 根据渠道ID和注册时间范围 获取用户列表
     * @param int $limit
     * @param int $len
     * @param $channelId
     * @param $start
     * @param $finish
     * @return array
     */
    protected function getUserByChannelId($limit = 0 , $len = 9999 , $channelId , $start , $finish){
        if(strlen(reset($channelId)) == 4){
            if(\Prj\Tool\Debug::isTestEnv()){
                $channelId = 9999;
                $start = 0;
            }
            $where = [
                'LEFT(channelid , 4)' => $channelId,
                ']createTime' => $start,
                '[createTime' => $finish,
            ];
            $ret = \Prj\Model\UcUser::getRecords('*',$where,'rsort createTime',$len , $limit);
        }else{
        $ret = \Prj\Model\UcUser::getRecords('*',[
            'channelid' => $channelId,
            ']createTime' => $start,
            '[createTime' => $finish,
        ],'rsort createTime',$len , $limit);
        }
        \Prj\Loger::out(\Prj\Model\UcUser::db()->lastCmd());
        return $ret;
    }

    /**
     * 获取120天及以上的产品ID列表
     * @return bool|\mysqli_result
     */
    protected function getProductIdsForMi(){
        $durationPeriodDays = $this->config['durationPeriodDays']; //指定产品期限
        if($this->productIds !== null)return $this->productIds;
        $ret = \Prj\Model\MimosaProduct::getRecords('*', [
            ']durationPeriodDays' => $durationPeriodDays
        ]);

        if($this->debug(1)){
            $tmp = ['abc'];
            $this->productIds = $tmp;
            return $tmp;
        }
        if(empty($ret))return [];
        $tmp = [];
        foreach ($ret as $v){
            $tmp[] = $v['oid'];
        }
        Loger::out('大于'.$durationPeriodDays.'天的产品ID: '.json_encode($tmp));
        $this->productIds = $tmp;
        return $tmp;
    }

    public function getActDate(){
        $startDate = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.mi.start');
        $endDate = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.mi.finish');
        if(empty($startDate) || empty($endDate)){
            return Result::get(RET_ERR , '未配置活动时间!');
        }
        return Result::get(RET_SUCC , '' , [
            'date' => [$startDate , $endDate]
        ]);
    }

    /**
     * 根据配置里的渠道代码获取活动时间
     * @param $code
     * @return array
     * @throws \ErrorException
     */
    public function getActDateByChannelCode($code){
        $startDate = $this->getIni('start');
        $endDate = $this->getIni('finish');

        if(empty($startDate) || empty($endDate)){
            return Result::get(RET_ERR , '未配置活动时间!');
        }
        return Result::get(RET_SUCC , '' , [
            'date' => [$startDate , $endDate]
        ]);
    }

    /**
     * 获取用户符合要求的订单
     * @param $investorOid
     * @param array $productIds
     * @return array
     */
    protected function getUserOrderListForMi($investorOid , $productIds = [] , $code = ''){
        \Prj\Loger::out('用户渠道: '.$code);
        if(empty($productIds))return Result::get(RET_ERR , '参数错误[productIds]');
        $dateRet = $this->getActDateByChannelCode($code);
        if(!Result::check($dateRet)){
            return Result::get(RET_SUCC , $dateRet['message']);
        }
        list($startDate) = $dateRet['data']['date'];
        $endDate = $this->getIni('orderFinish');
        if(empty($startDate) || empty($endDate))return Result::get(RET_ERR , '活动时间设置异常!');
        $startDate = date('Y-m-d H:i:s' , strtotime($startDate));
        $endDate = date('Y-m-d H:i:s' , strtotime($endDate));

        $ret = \Prj\Model\MimosaTradeOrder::getRecords('*' , [
            'orderType' => 'invest',
            'orderStatus' => ['paySuccess','accepted','confirmed','done'],
            ']createTime' => $startDate,
            '[createTime' => $endDate,
            'productOid' => $productIds,
            // ']orderAmount' => 2180,
            'investorOid' => $investorOid
        ] , 'rsort orderAmount');
        \Prj\Loger::out(\Prj\Model\MimosaTradeOrder::db()->lastCmd());
        return Result::get(RET_SUCC , ''  , [
            'list' => $ret,
        ]);
    }
    /**
     * 根据订单列表获取能拿到的奖品列表
     * @param array $orderList
     * @return array
     */
    protected function getRewardByOrderList($orderList = []){
        $rewards = $this->rewards;
        $tmp = $orderList;
        $tmp = \Lib\Misc\ArrayH::rdsort2d($tmp , 'orderAmount');
        foreach ($tmp as $v){
            // \Prj\Loger::outVal('check order' , $v['oid'] .' '.$v['orderAmount']);
            foreach ($this->rule as $amount => $gift){
                if($v['orderAmount'] >= $amount){
                    if(!$rewards[$gift]){
                        \Prj\Loger::out('订单号: '.$v['oid'].' 订单金额: '.$v['orderAmount'].' 获得 '.$gift);
                        $rewards[$gift] = 1;
                        break;
                    }else{
                        continue;
                    }
                }
            }
        }

        return $rewards;
    }

    /**
     * Hand 获取对应的推送消息内容
     * @return array|int|mixed
     */
    protected function getPusnConForMi(){
        $date = date('Ymd');
        $dayPush = $this->getIni('dayPush');
        $start = $this->getIni('start');
        $dayPushArr = $dayPush ? explode(',',$dayPush) : [];
        $map = [];
        $num = 0;
        foreach ($dayPushArr as $v){
            $day = date('Ymd' , strtotime('+'.$num.' days' , strtotime($start)));
            $map[$day] = $v;
            $num++;
        }
        \Prj\Loger::outVal('推送模板' , $map);
        if(\Prj\Tool\Debug::isTestEnv())return reset($map);
        return isset($map[$date]) ? $map[$date] : null;
    }

    protected function log($msg , $level = LOG_INFO){
        \Prj\Loger::out($msg , $level);
        return false;
    }

    protected function debug($num){
        if(defined('DEBUG_'.$num)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Hand 判断用户有没有获取奖品
     * @param $ucUid
     * @return bool
     */
    protected function canGetReward($ucUid){
        $rewards = $this->getRewardByUcUid($ucUid);
        $this->rewards = $rewards;
        if(empty($rewards))return false;
        foreach ($rewards as $k => $v){
            if($v){
                $this->log('该用户获得了 '.$k);
                return true;
            }
        }
        return false;
    }

    /**
     * Hand 获取用户的奖品
     * @param $ucUid
     * @return array
     * @throws \Exception
     */
    public function getRewardByUcUid($ucUid){
        $this->rewardsInit();
        if(is_array($ucUid)){
            $userInfo = $ucUid;
            $copartnerAll = $userInfo['channelid'];
        }else{
        $user = \Prj\Model\User::getCopy($ucUid);
        $user->load();
        $copartnerAll = $user->getField('channelid');
            $userInfo = $user->dump();
        }

        $this->log('channelId: '.$copartnerAll);
        $res = $this->checkUser($userInfo);
        if(!$this->checkRes($res)){
            return $this->rewards;
        }
        $investorInfo = \Prj\Model\MimosaUser::getUserByUcUserId($ucUid);
        if(empty($investorInfo))$this->fatalErr('投资者信息不存在!!!');
        if($this->rule){
            $rewards = $this->getRewardByRules($investorInfo);
        }else if($this->newRules){
            $rewards = $this->getRewardByNewRules($investorInfo);
        }

        return $rewards;
    }

    protected function getRewardByRules($investorInfo){
                $productIds = $this->getProductIdsForMi();
                if(empty($productIds)){
                    $this->log('没有大于180天的产品存在!');
                    return $this->rewards;
                }
        if(empty($investorInfo) || empty($investorInfo['userOid'])){
            $this->log('mimosa info: '.json_encode($investorInfo));
                    $this->log('查不到此用户的mimosa id');
                    return $this->rewards;
                }
        $investorId = $investorInfo['oid'];

        $orderListRet = $this->getUserOrderListForMi($investorId , $productIds);
                if(!Result::check($orderListRet)){
                    $this->log($orderListRet['message']);
                    return $this->rewards;
                }
                $orderList = $orderListRet['data']['list'];
                if(empty($orderList)){
                    $this->log('此用户没有符合要求的订单!');
                    return $this->rewards;
                }
                $rewards = $this->getRewardByOrderList($orderList);
                return $rewards;
            }

    protected function getRewardByNewRules($investorInfo = []){
        $orderStart = $this->getIni('start');
        $orderFinish = $this->getIni('orderFinish');
        $investorId = $investorInfo['oid'];
        foreach ($this->newRules as $k => $v){
            //首投订单
            $firstOrder = $this->getFirstOrder($investorId);
            if(empty($firstOrder)){
                $this->log('查无首投订单!');
                continue;
            }else{
                $this->log('首投订单 oid: '.$firstOrder['oid'] . ' amount:' .
                    $firstOrder['orderAmount'].' days:' . $firstOrder['durationPeriodDays'].
                    ' createTime:' . $firstOrder['createTime']);
            }
            if($k == 'first'){
                $orderTime = date('YmdHis' , strtotime($firstOrder['createTime']));
                if($orderTime < $orderStart || $orderTime > $orderFinish){
                    $this->log('订单日期不在活动期间!'.$orderTime.' '.$orderStart.'~'.$orderFinish);
                    continue;
                }
                foreach ($v as $kk => $vv){
                    $tmp = explode('_' , $kk);
                    if($firstOrder['durationPeriodDays'] >= $tmp[0] && $firstOrder['orderAmount'] >= $tmp[1]){
                        $this->rewards[$vv] = 1;
                        break;
                    }
                }
            }
            if($k == 'second'){
                foreach ($v as $kk => $vv){
                    $tmp = explode('_' , $kk);
                    $reOrder = $this->getReOrder($investorId , $tmp[0] , $tmp[1] , $firstOrder['oid']);
                    if(!empty($reOrder)){
                        $this->rewards[$vv] = 1;
                        break;
                    }
                }
            }
        }
        return $this->rewards;
    }

    protected function getFirstOrder($investorOid){
        $sql = <<<sql
select pro.durationPeriodDays ,pro.type , trade.*  from t_money_investor_tradeorder trade
LEFT JOIN t_gam_product pro
ON trade.productOid = pro.oid
where trade.investorOid = '$investorOid'
and orderStatus in ('paySuccess','accepted','confirmed','done')
and pro.type = 'PRODUCTTYPE_01'
ORDER BY trade.createTime asc LIMIT 1;
sql;
        if(\Prj\Tool\Debug::isTestEnv())return \Prj\Tool\Debug::getData('first');
        return \Prj\Model\User::query($sql)[0];
    }

    protected function getReOrder($investorOid , $day , $amount = 0 , $firstOrderId){
        $orderStart = date('Y-m-d H:i:s' , strtotime($this->getIni('start')));
        $orderFinish = date('Y-m-d H:i:s' , strtotime($this->getIni('finish')));
        $sql = <<<sql
select pro.durationPeriodDays ,pro.type , trade.*  from t_money_investor_tradeorder trade
LEFT JOIN t_gam_product pro
ON trade.productOid = pro.oid
where trade.investorOid = '$investorOid'
and orderStatus in ('paySuccess','accepted','confirmed','done')
and pro.type = 'PRODUCTTYPE_01'
and trade.oid <> '$firstOrderId'
and trade.orderAmount >= $amount
and pro.durationPeriodDays >= $day
and trade.createTime > '$orderStart'
and trade.createTime < '$orderFinish'
ORDER BY trade.createTime asc LIMIT 1;        
sql;
        if(\Prj\Tool\Debug::isTestEnv())return \Prj\Tool\Debug::getData('second');
        return \Prj\Model\User::query($sql)[0];
    }

    /**
     * Hand 根据渠道配置开始推送
     * @return bool|void
     */
    protected function groupPush(){
        $code = $this->code;
        $start = $this->getIni('start');
        $finish = $this->getIni('finish');
        $channelId = $this->channelId;

        if(!\Prj\Tool\Debug::isTestEnv()){
            Loger::out('日期: '.date('YmdHis').' ('.$start.' ~ '.$finish.')');
            if(date('YmdHis') < $start || date('YmdHis') > $finish){
                return $this->log('非有效时间内!');
            }
        }

        $max = 300;
        $page = 0;
        $pageSize = 1000;
        $tmp = [];

        $pushContent = $this->getPusnConForMi(); //获取推送模板
        if(empty($pushContent)){
            return $this->log('无匹配的推送内容!');
        }
        \Prj\EvtMsg\JavaApiPush::getInstance('')->setTemplateId($pushContent);
        while (true){
            $list = $this->getUserByChannelId($page * $pageSize , 1000 , $channelId , $start , $finish);
            if(!count($list))break;
            foreach ($list as $v){
                if(!$this->canGetReward($v['oid'])){
                    $this->log('--------------------');
                    $this->log('需要推送的用户 ucUid: '.$v['oid'].' phone: '.$v['userAcc']);
                    $tmp[] = $v['oid'];
                }else{
                    $this->log('不需要推送的用户 ucUid: '.$v['oid']);
                }
                if(count($tmp) >= 10){
                    $this->log('发送推送 '.json_encode($tmp));
                    \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($pushContent , $pushContent , $tmp , ['push'] , 'custom');
                    $tmp = [];
                    usleep(100000);//等待
                }
            }
            $page ++;
            $max --;
            if($max < 0)break;
        }
        if(!empty($tmp)){
            $this->log('发送推送 '.json_encode($tmp));
            \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($pushContent , $pushContent , $tmp , ['push']);
        }
        $this->log('['. $code .']活动推送结束!');
    }

    public function test(){
        \Prj\Tool\Debug::forceProDisable();
        function out_put($str , $exp = ''){
            if(is_array($str))$str = json_encode($str , 256);
            if($exp){
                if($str == $exp){
                    $str .= ' ✔✔✔';
                }else{
                    $str .= ' ×××××××××××××××××××××';
                }
            }
            echo $str . "\n";
        }
        out_put('【小米活动测试】');
        $user = \Prj\Model\UcUser::getRecord(null , ['userAcc' => '13262798028'] , 'rsort createTime');
        //0908 ~ 0916
        $data = [
            [
                'id' => 1,
                'channelid' => '100620170717234585',
                'createTime' => '2017-09-07 23:59:59',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":99999,"message":"仅限参与活动的用户参加~","data":[]}',
                    '{"code":99999,"message":"仅限参与活动的用户参加~","data":[]}',
                ]
            ],
            [
                'id' => 2,
                'channelid' => '100620170717234585',
                'createTime' => '2017-09-08 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":10000,"message":"success","data":{"code":"mi0908"}}',
                    '{"code":10000,"message":"success","data":[]}',
                ]
            ],
            [
                'id' => 3,
                'channelid' => '100620170717234585',
                'createTime' => '2017-09-17 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":99999,"message":"仅限参与活动的用户参加~","data":[]}',
                    '{"code":99999,"message":"仅限参与活动的用户参加~","data":[]}',
                ]
            ],
            [
                'id' => 4,
                'channelid' => '100620170717234585',
                'createTime' => '2017-09-16 23:59:59',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":10000,"message":"success","data":{"code":"mi0908"}}',
                    '{"code":10000,"message":"success","data":[]}',
                ]
            ],
            //1006,1208,1209
            //错误的渠道测试
            [
                'id' => 5,
                'channelid' => '100720170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":99999,"message":"仅限参与活动的用户参加~","data":[]}',
                    '{"code":99999,"message":"仅限参与活动的用户参加~","data":[]}',
                ]
            ],
            [
                'id' => 6,
                'channelid' => '',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":99999,"message":"仅限参与活动的用户参加~","data":[]}',
                    '{"code":99999,"message":"仅限参与活动的用户参加~","data":[]}',
                ]
            ],
            [
                'id' => 7,
                'channelid' => '100620170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":10000,"message":"success","data":{"code":"mi0908"}}',
                    '{"code":10000,"message":"success","data":[]}',
                ]
            ],
            [
                'id' => 8,
                'channelid' => '120820170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":10000,"message":"success","data":{"code":"mi0908"}}',
                    '{"code":10000,"message":"success","data":[]}',
                ]
            ],
            [
                'id' => 9,
                'channelid' => '120920170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    '{"code":10000,"message":"success","data":{"code":"mi0908"}}',
                    '{"code":10000,"message":"success","data":[]}',
                ]
            ],
            //奖品测试
            [
                'id' => 10,
                'channelid' => '120920170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1280',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    null,
                    null,
                    '{"a":0,"b":0,"c":0,"d":0,"e":1,"f":1}'
                ]
            ],
            //newRules[first] = '{"180_80000":"a","180_36800":"b","180_12800":"c","180_4500":"d","180_1280":"e"}'
            //newRules[second] = '{"90_5000":"f"}'
            [
                'id' => 11,
                'channelid' => '120920170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '1279',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    null,
                    null,
                    '{"a":0,"b":0,"c":0,"d":0,"e":0,"f":1}'
                ]
            ],
            [
                'id' => 12,
                'channelid' => '120920170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '4500',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    null,
                    null,
                    '{"a":0,"b":0,"c":0,"d":1,"e":0,"f":1}'
                ]
            ],
            [
                'id' => 13,
                'channelid' => '120920170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '12800',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    null,
                    null,
                    '{"a":0,"b":0,"c":1,"d":0,"e":0,"f":1}'
                ]
            ],
            [
                'id' => 14,
                'channelid' => '120920170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '36800',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    null,
                    null,
                    '{"a":0,"b":1,"c":0,"d":0,"e":0,"f":1}'
                ]
            ],
            [
                'id' => 15,
                'channelid' => '120920170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '80000',
                        'durationPeriodDays' => 180,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [
                        'oid' => '2',
                    ]
                ],
                'result' => [
                    null,
                    null,
                    '{"a":1,"b":0,"c":0,"d":0,"e":0,"f":1}'
                ]
            ],
            [
                'id' => 16,
                'channelid' => '120920170717234585',
                'createTime' => '2017-09-10 00:00:00',
                'orders' => [
                    'first' => [
                        'oid' => '1',
                        'orderAmount' => '80000',
                        'durationPeriodDays' => 179,
                        'createTime' => '2017-09-08 01:00:00',
                    ],
                    'second' => [

                    ]
                ],
                'result' => [
                    null,
                    null,
                    '{"a":0,"b":0,"c":0,"d":0,"e":0,"f":0}'
                ]
            ],
        ];
        foreach ($data as $v){
            out_put($v['id']);
            $user['channelid'] = $v['channelid'];
            $user['createTime'] = $v['createTime'];
            \Prj\Tool\Debug::setData($v['orders']);
            $result = $v['result'];
            if($result[0])out_put($this->checkUser($user) , $result[0]);
            if($result[1])out_put($this->sendRigisterSMS($user) , $result[1]);
            if($result[2])out_put($this->getRewardByUcUid($user) , $result[2]);
        }

        out_put($this->runPushForMi());
    }
}
