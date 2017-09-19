<?php

use Prj\Tool\TimeTool;

/**
 * 签到活动
 * @author simon.wang
 */
class DaysignController extends \Prj\Framework\Ctrl
{
    /**
     * @SWG\Post(path="/actives/daysign/dosign", tags={"Daysign"},
     *     summary="签到",description="签到",
     *     @SWG\Parameter(name="getList",description="是否返回签到历史：1获取",type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="data",
     *                 @SWG\Property(property="daySignResult", description="结果：1签到成功，0签到失败", type="string"),
     *                 @SWG\Property(property="versionFlag", description="邀请码", type="string"),
     *                 @SWG\Property(property="serverTime", description="服务器时间", type="integer"),
     *                 @SWG\Property(property="daySignHistory", description="本周期内已签奖励列表",type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="ymd",description="签到时间-时间戳",type="integer"),
     *                          @SWG\Property(property="bonus",description="奖励内容",
     *                              @SWG\Property(property="amount", description="金额，0表示随机金额", type="string"),
     *                              @SWG\Property(property="type", description="类型", type="integer"),
     *                              @SWG\Property(property="name", description="名称", type="string")
     *                          )
     *                      )
     *                  ),
     *                 @SWG\Property(property="rewardList", description="预计奖励列表",type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="amount", description="金额，0表示随机金额", type="string"),
     *                          @SWG\Property(property="type", description="类型", type="integer"),
     *                          @SWG\Property(property="name", description="名称", type="string")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function dosignAction()
    {
        $getListFlag = $this->_request->get('getList');
        $userId = $this->getUidInSession();
        if (!$userId) {
            return $this->assignCodeAndMessage('not login', 10001);
        }

        try {
            $CheckInBook = new \Lib\Services\CheckinBook($userId);
        } catch (Exception $e) {
            \Prj\Loger::out($e->getMessage(), LOG_ERR);
            return $this->assignCodeAndMessage('登录超时', 19997);
        }

        $CheckInModel = \Prj\Model\CheckIn::getCopy($userId);
        $CheckInModel->load();
        if ($CheckInModel->exists()) {
            $ret = false;
        } else {
//            //TODO 检查旧版签到记录
            if (\Prj\Model\SignIn::checkSign($userId)) {
                $ret = false;
            } else {
                $ret = $CheckInBook->giveReward();
                \Prj\Loger::out(var_export($ret, true));
            }
        }
        $data['daySignResult'] = $ret ? 1 : 0;

        if ($getListFlag == 1) {//获取历史列表和完整奖励列表
            $historyList = $CheckInBook->historyList;
            $rewardList = $CheckInBook->produceStaticRewardList();
            $data['daySignHistory'] = empty($historyList) || !isset($historyList['reward']) ? "" : $this->formatHistoryList($historyList['reward']);
            $data['rewardList'] = $this->formatRewardList($rewardList);
        }
        $this->_view->assign('data', $data);
        $this->assignCodeAndMessage('success');
    }

    /**
     * @SWG\Post(path="/actives/daysign/newdosign", tags={"Daysign"},
     *     summary="签到-新版使用",description="签到",
     *     @SWG\Parameter(name="getList",description="是否返回签到历史：1获取",type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="data",
     *                 @SWG\Property(property="daySignResult", description="结果：1签到成功，0签到失败", type="string"),
     *                 @SWG\Property(property="todayCouponId", description="今日签到的券ID", type="integer"),
     *                 @SWG\Property(property="todayCouponStatus", description="卡券状态 NOTUSED-未使用 LOCKED-已锁定 EXPIRED-已过期  USED-已使用", type="string"),
     *                 @SWG\Property(property="daySignHistory", description="本周期内已签奖励列表",type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="ymd",description="签到时间-时间戳",type="integer"),
     *                          @SWG\Property(property="bonus",description="奖励内容",
     *                              @SWG\Property(property="amount", description="金额，0表示随机金额", type="string"),
     *                              @SWG\Property(property="type", description="类型", type="integer"),
     *                              @SWG\Property(property="name", description="名称", type="string")
     *                          )
     *                      )
     *                  ),
     *                 @SWG\Property(property="rewardList", description="预计奖励列表",type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="amount", description="金额，0表示随机金额", type="string"),
     *                          @SWG\Property(property="type", description="类型", type="integer"),
     *                          @SWG\Property(property="name", description="名称", type="string")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function newdosignAction()
    {
        $getListFlag = $this->_request->get('getList');
        $userId = $this->getUidInSession();
        if (!$userId) {
            return $this->assignCodeAndMessage('登录状态已失效,请重新登录', 10001);
        }

        try {
            $CheckInBook = new \Lib\Services\CheckinBook($userId);
        } catch (Exception $e) {
            \Sooh2\Misc\Loger::getInstance()->app_trace($e->getMessage());;
            return $this->assignCodeAndMessage('登录状态已失效,请重新登录', 10001);
        }

        $CheckInModel = \Prj\Model\CheckIn::getCopy($userId);
        $CheckInModel->load();
        if ($CheckInModel->exists()) {
            $ret = false;
        } else {
//            //TODO 检查旧版签到记录
            $ret = $CheckInBook->giveReward();
            \Sooh2\Misc\Loger::getInstance()->app_trace($ret);;
        }
        $data['daySignResult'] = $ret ? 1 : 0;
//        $data['todayCouponId'] = $CheckInBook->getTodayConponId();
        if ($data['todayCouponId'] = $CheckInBook->getTodayConponId()) {
            $tmpCouponRecord = \Prj\Model\ZyBusiness\UserCoupon::getRecord('couponStatus', ['ucId' => $data['todayCouponId']]);
            if ($tmpCouponRecord) {
                $data['todayCouponStatus'] = $tmpCouponRecord['couponStatus'];
            } else {
                $data['todayCouponStatus'] = null;
            }
        } else {
            $data['todayCouponStatus'] = null;
        }

        if ($getListFlag == 1) {//获取历史列表和完整奖励列表
            $historyList = $CheckInBook->historyList;
            $rewardList = $CheckInBook->produceStaticRewardList();
            $data['daySignHistory'] = empty($historyList) || !isset($historyList['reward']) ? [] : $this->formatHistoryList($historyList['reward'], 1);
            $data['rewardList'] = $this->formatRewardList($rewardList, 1);
        }
        $this->_view->assign('data', $data);
        $this->assignCodeAndMessage('success');
    }

    /**
     *@SWG\Post(path="/actives/daysign/history", tags={"Daysign"},
     *    summary="获取签到记录",description="获取签到记录",
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="data",
     *                 @SWG\Property(property="checkIn", description="今天是否已签到：1已签到，2未签到", type="string"),
     *                 @SWG\Property(property="versionFlag", description="邀请码", type="string"),
     *                 @SWG\Property(property="serverTime", description="服务器时间", type="integer"),
     *                 @SWG\Property(property="daySignHistory", description="本周期内已签奖励列表", type="integer"),
     *                 @SWG\Property(property="rewardList", description="预计奖励列表",type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="amount", description="金额，0表示随机金额", type="string"),
     *                          @SWG\Property(property="type", description="类型", type="integer"),
     *                          @SWG\Property(property="name", description="名称", type="string")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function historyAction()
    {
        $userId = $this->getUidInSession();
        if (!$userId) {
            return $this->assignCodeAndMessage('not login', 10001);
        }

        try {
            $CheckInBook = new \Lib\Services\CheckinBook($userId);
        } catch (Exception $e) {
            \Prj\Loger::out($e->getMessage(), LOG_ERR);
            return $this->assignCodeAndMessage('登录超时', 19997);
        }

        $historyList = $CheckInBook->historyList;
        $rewardList = $CheckInBook->produceStaticRewardList();
        !empty($historyList) AND \Prj\Loger::out('user checkinBook lastReward Date' . $historyList['lastRewardDate']);
        $data = [];
        if (!empty($historyList) && $historyList['lastRewardDate'] == date('Ymd', time())) {
            $data['checkIn'] = 1;
        } else {
            if (\Prj\Model\SignIn::checkSign($userId)) {
                $data['checkIn'] = 1;
            } else {
                $data['checkIn'] = 2;
            }
        }

        $switchVersionTime = \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_switch_version_time');//获取版本切换时间戳
        if (time() >= $switchVersionTime) {
            $data['versionFlag'] = 2;
        } else {
            $data['versionFlag'] = 1;
        }

        $data['serverTime'] = time();
        $data['daySignHistory'] = empty($historyList) || !isset($historyList['reward']) ? "" : $this->formatHistoryList($historyList['reward']);
        $data['rewardList'] = $this->formatRewardList($rewardList);
        \Prj\Loger::out('daysign history for uid:' . $userId);
        \Prj\Loger::out($data);
        $this->_view->assign('data', $data);
        $this->assignCodeAndMessage('success');
    }

    /**
     * @SWG\Post(path="/actives/daysign/newhistory", tags={"Daysign"},
     *    summary="获取签到记录-新版使用",description="获取签到记录",
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="data",
     *                 @SWG\Property(property="checkIn", description="今天是否已签到：1已签到，2未签到", type="string"),
     *                 @SWG\Property(property="versionFlag", description="邀请码", type="string"),
     *                 @SWG\Property(property="serverTime", description="服务器时间", type="integer"),
     *                 @SWG\Property(property="todayCouponId", description="今日签到的券ID", type="integer"),
     *                 @SWG\Property(property="todayCouponStatus", description="卡券状态 NOTUSED-未使用 LOCKED-已锁定 EXPIRED-已过期  USED-已使用", type="string"),
     *                 @SWG\Property(property="daySignHistory", description="本周期内已签奖励列表",type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="ymd",description="签到时间-时间戳",type="integer"),
     *                          @SWG\Property(property="bonus",description="奖励内容",
     *                              @SWG\Property(property="amount", description="金额，0表示随机金额", type="string"),
     *                              @SWG\Property(property="type", description="类型", type="integer"),
     *                              @SWG\Property(property="name", description="名称", type="string")
     *                          )
     *                      )
     *                  ),
     *                 @SWG\Property(property="rewardList", description="预计奖励列表",type="array",
     *                      @SWG\Items(
     *                          @SWG\Property(property="amount", description="金额，0表示随机金额", type="string"),
     *                          @SWG\Property(property="type", description="类型", type="integer"),
     *                          @SWG\Property(property="name", description="名称", type="string")
     *                      )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function newhistoryAction()
    {
        $userId = $this->getUidInSession();
        if (!$userId) {
            return $this->assignCodeAndMessage('登录状态已失效,请重新登录', 10001);
        }
        try {
            $CheckInBook = new \Lib\Services\CheckinBook($userId);
        } catch (Exception $e) {
            \Sooh2\Misc\Loger::getInstance()->app_trace($e->getMessage());;
            return $this->assignCodeAndMessage('登录状态已失效,请重新登录', 10001);
        }

        $historyList = $CheckInBook->historyList;
        $rewardList = $CheckInBook->produceStaticRewardList();
        !empty($historyList) AND \Sooh2\Misc\Loger::getInstance()->app_trace('user checkinBook lastReward Date' . $historyList['lastRewardDate']);;

        $data = [];
        if (!empty($historyList) && $historyList['lastRewardDate'] == date('Ymd', time())) {
            $data['checkIn'] = 1;
        } else {
            $data['checkIn'] = 2;
        }

        $switchVersionTime = \Prj\Bll\ActivityConfig::getInstance()->getConfig('签到', 'signin_switch_version_time');//获取版本切换时间戳
        if (time() >= $switchVersionTime) {
            $data['versionFlag'] = 2;
        } else {
            $data['versionFlag'] = 1;
        }

        $data['serverTime'] = time() * 1000;
        if ($data['todayCouponId'] = $CheckInBook->getTodayConponId()) {
            $tmpCouponRecord = \Prj\Model\ZyBusiness\UserCoupon::getRecord('couponStatus', ['ucId' => $data['todayCouponId']]);
            if ($tmpCouponRecord) {
                $data['todayCouponStatus'] = $tmpCouponRecord['couponStatus'];
            } else {
                $data['todayCouponStatus'] = null;
            }
        } else {
            $data['todayCouponStatus'] = null;
        }
        $data['daySignHistory'] = empty($historyList) || !isset($historyList['reward']) ? [] : $this->formatHistoryList($historyList['reward'], 1);
        $data['rewardList'] = $this->formatRewardList($rewardList, 1);
        \Sooh2\Misc\Loger::getInstance()->app_trace('daysign history for uid:' . $userId);
        \Sooh2\Misc\Loger::getInstance()->app_trace($data);
        $this->_view->assign('data', $data);
        $this->assignCodeAndMessage('success');
    }

    /**
     * @SWG\Post(
     *     path="/actives/daysign/receivelist",tags={"Daysign"},summary="签到-领取明细",
     *     @SWG\Parameter(name="pageInfo",description="分页信息JSON：{pageSize:10,pageNo:1}",type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="list", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="num", description="本周期内第几次签到", type="string"),
     *                     @SWG\Property(property="time", description="时间", type="string"),
     *                     @SWG\Property(property="amount", description="金额", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function receivelistAction()
    {
        if($this->_pager == null){
            return $this->assignCodeAndMessage('分页参数不能为空' , 99999);
        }
        $pageInfo = [
            'pageSize' => $this->_pager->page_size ? : 10,
            'pageNo' => $this->_pager->pageid() ? : 1,
        ];
        $userId = $this->getUidInSession();
        if (!$userId) {
            return $this->assignCodeAndMessage('not login', 10001);
        }
        $ModelUser = \Prj\Model\User::getCopy($userId);
        $ModelUser->load();
        if ($ModelUser->exists()) {
            $data['total'] = sprintf('%.2f',  $ModelUser->getField('checkinAmount') / 100);
            $Model = \Prj\Model\CheckIn::getCopy('');
            $brokerDb = $Model->dbWithTablename();
            $where = [
                'userid' => $userId,
            ];
            $totalSize = $brokerDb->getRecordCount($brokerDb->kvobjTable(), $where);
            if ($totalSize) {
                $list = $brokerDb->getRecords($brokerDb->kvobjTable(), '*', $where, 'rsort date', $pageInfo['pageSize'], ($pageInfo['pageNo'] - 1) * $pageInfo['pageSize']);
                if ($list) {
                    foreach ($list as $k => $v) {
                        $data['list'][] = [
                            'num' => $v['number'],
                            'time' => intval($v['date'] * 1000),
                            'amount' => sprintf('%.2f', $v['amount'] / 100),
                        ];
                    }
                } else {
                    $data['list'] = [];
                }
            } else {
                $data['list'] = [];
            }
            $data['pageInfo'] = [
                'pageNo' => $pageInfo['pageNo'],
                'pageSize' => $pageInfo['pageSize'],
                'totalSize' => $totalSize,
                'totalPage' => ceil($totalSize / $pageInfo['pageSize']),
            ];

            $this->_view->assign('data', $data);
            return $this->assignCodeAndMessage('success', 10000);

        } else {
            return $this->assignCodeAndMessage('用户不存在，请稍后再试', 19998);
        }
    }

    protected function formatHistoryList($list, $apiVersion = 0.1)
    {
        if (empty($list)) {
            return $list;
        } else {
            $ret = [];
            foreach ($list as $k => $v) {
                if ($v['ymd'] < 10000000000) {
                    $ymd = $v['ymd'] * 1000;
                } else {
                    $ymd = $v['ymd'];
                }

                $ret[] = [
                    'ymd' => $ymd,
                    'bonus' => [
                        'amount' => $apiVersion >= 1 ? sprintf('%.2f', $v['bonus']['amount'] / 100) : $v['bonus']['amount'],
                        'type' => 1,
                        'name' => '代金券',
                    ],
                ];
            }
            return $ret;
        }
    }

    protected function formatRewardList($list, $apiVersion = 0.1)
    {
        if (empty($list)) {
            return $list;
        } else {
            $ret = [];
            foreach ($list as $k => $v) {
                $ret[] = [
                    'amount' => $apiVersion >= 1 ? sprintf('%.2f', $v['amount'] / 100) : $v['amount'],
                    'type' => 1,
                    'name' => '代金券',
                ];
            }
            return $ret;
        }
    }
}
