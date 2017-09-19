<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/11
 * Time: 17:04
 */

 class ActivityController extends \Prj\Framework\Ctrl
 {
     /**
      * @SWG\Post(path="/actives/activity/zyHeroRank", tags={"Activity"},
      *   summary="7月掌悦英雄榜",
      *   description="",
      * )
      */
     public function zyHeroRankAction(){
         $res = \Prj\Bll\Tmp\ZyHeroRank0721::getInstance()->getData();
         $this->assignRes($res);
     }

     public function chocolateCallbackAction()
     {
         $uid = $this->getUidInSession();
         $boxOid = $this->_request->get('boxOid');
         $oauthCallbackUrl = \Sooh2\Misc\Ini::getInstance()->getIni('Wechat')['oauth']['callback'];
         if (empty($boxOid)) {
             $targetUrl = '/h5/seventh-activity';
         }else{
             $targetUrl = '/h5/getChocolate77';
         }
         $urlParams = [];

         $chocolateConfigStart = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.Chocolate0828.start');
         $chocolateConfigFinish = \Sooh2\Misc\Ini::getInstance()->getIni('Activity.Chocolate0828.finish');
         $nowDate = date('YmdHis');
         if($nowDate < $chocolateConfigStart) {
             $url = $targetUrl . '?' . http_build_query(array_merge($urlParams, ['code' => 90999, 'message' => urlencode('活动尚未开始')]));
             \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
             header ('Location: ' . $url);
             die();
         }
         if($nowDate > $chocolateConfigFinish) {
             $url = $targetUrl . '?' . http_build_query(array_merge($urlParams, ['code' => 90999, 'message' => urlencode('活动已经结束')]));
             \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
             header ('Location: ' . $url);
             die();
         }

         if (empty($boxOid)) {
             if (!empty($uid)) {
                 if (($boxOid = \Prj\Bll\Tmp\Chocolate0828::getInstance()->giveChocolateBox($uid)) === false) {
                     $url = $targetUrl . '?' . http_build_query(array_merge($urlParams, ['code' => 90999, 'message' => urlencode('发放巧克力失败，请稍后再试')]));
                     \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
                     header ('Location: ' . $url);
                     die();
                 }
             } else {
//                 return $this->assignCodeAndMessage('请登录后再试', 99999);
                 $url = $targetUrl . '?' . http_build_query(array_merge($urlParams, ['code' => 99999, 'message' => '您还未在APP登录，请登录后重试']));
                 \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
                 header ('Location: ' . $url);
                 die();
             }
         } else {
             $ModelChocolate = \Prj\Model\TmpChocolate::getCopy(['boxOid' => $boxOid]);
             $ModelChocolate->load();
             if ($ModelChocolate->exists()) {
                 $fromUserId = $ModelChocolate->getField('fromUserId');

                 $ModelUser = \Prj\Model\User::getCopy($fromUserId);
                 $ModelUser->load();
                 if ($ModelUser->exists()) {
                     try {
                         $inviteCode = $ModelUser->getField('sceneId');
                         $urlParams = array_merge($urlParams, ['inviteCode' => $inviteCode]);
                     } catch (\Exception $e) {
                         \Sooh2\Misc\Loger::getInstance()->app_trace('sceneId is null');
                     }
                 }
             }
         }

         if (\Prj\Bll\Wechat::getInstance()->checkWechatBrowser()) {
             if ($openid = $_COOKIE['wechat_openid']) {
                 $ModelWechatOpenidPhone = \Prj\Model\WechatOpenidPhone::getCopy($openid);
                 $ModelWechatOpenidPhone->load();
                 if ($ModelWechatOpenidPhone->exists()) {
                     $phone = $ModelWechatOpenidPhone->getField('phone');
                     //TODO 上线后将有效期调整为30天
                     setcookie('phone', $phone, time() + 86400 * 30, '/');
                 } else {
                     $ModelWechatUser = \Prj\Model\WechatUser::getCopy($openid);
                     $ModelWechatUser->load();
                     if (!$ModelWechatUser->exists()) {
                         //openid不合法
                         setcookie('wechat_openid', '', time() - 3600, '/');
                         unset($_COOKIE['wechat_openid']);
                         return $this->assignCodeAndMessage('请重新授权', 99999);
                     }
                 }
             } else {
                 //重新授权
                 \Sooh2\Misc\Loger::getInstance()->app_trace('重新授权中......');
                 $url = '/platform/wechat/webauth?callbackUrl=' . urlencode($oauthCallbackUrl . '?targetUrl=' . urlencode( $targetUrl . '?' . http_build_query(array_merge($urlParams, ['boxOid' => $boxOid]))));
                 \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
                 header ('Location: ' . $url);
                 die();
             }
         }

         $urlParams['boxOid'] = $boxOid;
         if (isset($phone)) {
             $urlParams['phone'] = $phone;
         }
         \Sooh2\Misc\Loger::getInstance()->app_trace('跳转到最终的目标地址......');
         $url = $targetUrl . '?' . http_build_query($urlParams);
         \Sooh2\Misc\Loger::getInstance()->app_trace('url:' . $url);
         header ('Location: ' . $url);
         die();
     }

     public function chocolateAccountAction(){
         $userId = $this->getUidInSession();
         if(empty($userId))return $this->assignCodeAndMessage('未登录或登录信息已经过期!', 10001);

         $this->assignRes(\Prj\Bll\Tmp\Chocolate0828::getInstance()->accountInfo($userId));
     }

     public function chocolateExchangeAction(){
         $userId = $this->getUidInSession();
         if(empty($userId))return $this->assignCodeAndMessage('未登录或登录信息已经过期!', 10001);

         $this->assignRes(\Prj\Bll\Tmp\Chocolate0828::getInstance()->getCoupon(['userId' => $userId]));
     }

     public function chocolateGetAction(){
         $userId = $this->getUidInSession();
         if(empty($userId))return $this->assignCodeAndMessage('未登录或登录信息已经过期!', 10001);

         $user = \Prj\Model\User::getCopy($userId);
         $user->load();
         if(!$user->exists())return $this->assignCodeAndMessage('用户信息不存在' , 99999);

         $phone = $user->getField('userAcc');
         $boxOid = $this->_request->get('boxOid');

         $this->assignRes(\Prj\Bll\Tmp\Chocolate0828::getInstance()->getChocolate(['phone' => $phone , 'boxOid' => $boxOid]));
     }

     public function chocolateMyAction(){
         $userId = $this->getUidInSession();
         if(empty($userId))return $this->assignCodeAndMessage('未登录或登录信息已经过期!', 10001);

        $pageSize = $this->_request->get('pageSize' , 10);
        $pageNo = $this->_request->get('pageNo' , 1);

        $this->assignRes(\Prj\Bll\Tmp\Chocolate0828::getInstance()->myList([
            'userId' => $userId,
            'pageSize' => $pageSize,
            'pageNo' => $pageNo,
        ]));
     }

     public function chocolateBoxListAction(){
         $boxOid = $this->_request->get('boxOid');

         $this->assignRes(\Prj\Bll\Tmp\Chocolate0828::getInstance()->boxList([
             'boxOid' => $boxOid
         ]));
     }
 }