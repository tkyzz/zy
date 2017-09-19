<?php

/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-27 18:04
 */
class MemberController extends \Prj\Framework\UserCtrl
{
    /**
     * @SWG\Post(
     *     path="/platform/Member/info",
     *     tags={"Member"},
     *     summary="用户基础信息",
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="UserBasicInfo",
 *                     @SWG\Property(property="login", description="是否登录：1已登录，0未登录", type="string"),
 *                     @SWG\Property(property="userId", description="用户ID", type="string"),
 *                     @SWG\Property(property="phone", description="手机号", type="integer"),
 *                     @SWG\Property(property="nickname", description="昵称", type="string"),
 *                     @SWG\Property(property="isCheckin", description="是否签到：1已签到，0未签到", type="string"),
 *                     @SWG\Property(property="ymdReg", description="注册时间", type="integer"),
 *                     @SWG\Property(property="wallet", description="余额", type="integer"),
 *                     @SWG\Property(property="isPwd", description="是否设置密码：1已设置，0未设置", type="integer"),
 *                     @SWG\Property(property="isPayPwd", description="是否设置支付密码：1已设置，0未设置", type="integer"),
 *                     @SWG\Property(property="inviteCode", description="我的邀请码", type="integer"),
 *                     @SWG\Property(property="isRealVerifiedName", description="是否实名：1已实名，0未实名", type="integer"),
 *                     @SWG\Property(property="isBindCard", description="是否绑卡：1已绑卡，0未绑卡", type="integer"),
 *                     @SWG\Property(property="isRecharge", description="是否充值：1已充值，0未充值", type="integer"),
 *                     @SWG\Property(property="isOrder", description="是否购买：1已购买，0未购买", type="integer"),
 *                     @SWG\Property(property="bankCard", description="银行卡号", type="integer"),
 *                     @SWG\Property(property="bankCode", description="银行代号", type="integer"),
 *                     @SWG\Property(property="isTiro", description="是否新手：1是新手，0不是新手", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function infoAction()
    {
//        \Sooh2\Misc\ViewExt::getInstance()->appendStatusTask('\\Prj\\RefreshStatus\\UserBasicInfo');
        $this->appendStatusTask('UserBasicInfo');
        return $this->assignCodeAndMessage('success');
    }

    /**
     * @SWG\Post(
     *     path="/platform/Member/saveClientTrans",
     *     tags={"Member"},
     *     summary="上报td返填信息",
     *     @SWG\Parameter(name="tdId",description="TDID",type="string"),
     *     @SWG\Response(response=200, description="successful operation")
     * )
     */
    public function saveClientTransAction()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('tdId', '', 'TDID')->initChecker(new \Sooh2\Valid\Str(true, 6, 32)));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            return $this->assignCodeAndMessage(current($err), 19998);
        }
        $inputs = $form->getInputs();
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($this->userId);
        $ModelUserFinal->load();
        if ($ModelUserFinal->getField('tdId')) {
            return $this->assignCodeAndMessage('TDID不能上报', 19998);
        }
        $ModelUserFinal->setField('tdId', $inputs['tdId']);
        $ModelUserFinal->saveToDB();
        return $this->assignCodeAndMessage('success');
    }

    /**
     * @SWG\Post(
     *     path="/platform/Member/inviteInfo",
     *     tags={"Member"},
     *     summary="获取我的邀请统计",
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="param",
     *                 @SWG\Property(property="canInvite", description="是否具有邀请资格:0不具有，1具有", type="string"),
     *                 @SWG\Property(property="inviteCode", description="邀请码", type="string"),
     *                 @SWG\Property(property="inviteNum", description="累积邀请人数", type="integer"),
     *                 @SWG\Property(property="rebateAmount", description="累积返利", type="integer"),
     *                 @SWG\Property(property="rebateNum", description="累积返利次数", type="integer"),
     *                 @SWG\Property(property="waitRebateNum", description="累积待返次数", type="integer"),
     *                 @SWG\Property(property="waitRebateAmount", description="累积待返金额", type="integer"),
     *                 @SWG\Property(property="inviteBannerImgUrl", description="邀请Banner页图片地址", type="integer"),
     *                 @SWG\Property(property="inviteBannerJumpUrl", description="邀请Banner页跳转地址", type="integer"),
     *                 @SWG\Property(property="inviteTitle", description="一句话标题", type="integer"),
     *                 @SWG\Property(property="inviteDescript", description="一句话简介", type="integer"),
     *                 @SWG\Property(property="inviteJumpUrl", description="邀请好友的跳转URL", type="integer"),
     *                 @SWG\Property(property="inviterIntroductionUrl", description="邀请的攻略页面URL", type="integer"),
     *                 @SWG\Property(property="qrcodeUrl", description="二维码图片地址", type="integer"),
     *                 @SWG\Property(property="inviteConfig",
     *                     @SWG\Property(property="title", description="分享链接-标题", type="integer"),
     *                     @SWG\Property(property="imageUrl", description="分享链接-图标", type="integer"),
     *                     @SWG\Property(property="content", description="分享链接-内容", type="integer")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function inviteInfoAction()
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($this->userId);
        $ModelUserFinal->load();
        if (!$ModelUserFinal->exists()) {
            return $this->assignCodeAndMessage('用户不存在或未登录', 19809);
        }

        $funcBuildJumpUrl = function ($url, $amount) use ($ModelUserFinal) {
            if (empty($name = $ModelUserFinal->getField('nickname'))) {
                $name = substr_replace($ModelUserFinal->getField('phone'), '****', 3, 4);
            }
            return $url . '?' . http_build_query(['n' => $name, 'a' => $amount, 'c' => $ModelUserFinal->getField('inviteCode')]);
        };

        $data['canInvite'] = $ModelUserFinal->getField('canInvite');
        $data['inviteCode'] = $ModelUserFinal->getField('inviteCode');
        $data['inviteNum'] = $ModelUserFinal->dbWithTablename()->getRecordCount($ModelUserFinal->dbWithTablename()->kvobjTable(), ['inviter' => $this->userId]);
        $data['rebateAmount'] = sprintf('%.2f', $ModelUserFinal->getField('rebateAmount') / 100);
        $data['rebateNum'] = $ModelUserFinal->getField('rebateNum');
        $data['waitRebateNum'] = $ModelUserFinal->getField('waitRebateNum');
        $data['waitRebateAmount'] = sprintf('%.2f', $ModelUserFinal->getField('waitRebateAmount') / 100);
        $BllActivityConfig = \Prj\Bll\ActivityConfig::getInstance();
        $data['inviteBannerImgUrl'] = $BllActivityConfig->getConfig('邀请配置', 'invite_banner_img_url');;
        $data['inviteBannerJumpUrl'] = $BllActivityConfig->getConfig('邀请配置', 'invite_banner_jump_url');
        $data['inviteTitle'] = $BllActivityConfig->getConfig('邀请配置', 'invite_title');
        $data['inviteDescript'] = $BllActivityConfig->getConfig('邀请配置', 'invite_descript');
        $data['inviteJumpUrl'] = \Prj\Bll\Invite::getInstance()->buildJumpUrl($this->userId);
        $data['inviteIntroductionUrl'] = $BllActivityConfig->getConfig('邀请配置', 'invite_introduction_url') . '?canInvite=' . $data['canInvite'];

        if (!\Prj\Bll\Invite::getInstance()->checkQrcodeFile($ModelUserFinal->getField('inviteCode') . '.png')) {
            \Prj\Bll\Invite::getInstance()->buildQrcode($ModelUserFinal->getField('inviteCode'), \Prj\Bll\Invite::getInstance()->buildJumpUrl($this->userId));
        }
        $data['qrcodeUrl'] = \Prj\Bll\Invite::getInstance()->getQrcode($ModelUserFinal->getField('inviteCode'));
        $data['inviteConfig'] = [
            'title' => $BllActivityConfig->getConfig('邀请配置', 'invite_share_title'),
            'imageUrl' => $BllActivityConfig->getConfig('邀请配置', 'invite_share_image_url'),
            'content' => $BllActivityConfig->getConfig('邀请配置', 'invite_share_content'),
        ];

        $this->_view->assign('data', $data);
        return $this->assignCodeAndMessage('success');
    }

    /**
     * @SWG\Post(
     *     path="/platform/Member/inviteRebate",
     *     tags={"Member"},
     *     summary="获取我邀请的好友详细返利-邀请好友页面",
     *     @SWG\Parameter(name="pageInfo",description="分页信息JSON：{pageSize:10,pageNo:1}",type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="inviteRebateList", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="uid", description="userId", type="string"),
     *                     @SWG\Property(property="phone", description="手机号", type="integer"),
     *                     @SWG\Property(property="nickname", description="昵称", type="string"),
     *                     @SWG\Property(property="amount", description="金额", type="integer"),
     *                     @SWG\Property(property="status", description="状态位：0未返，1已返，8首购未返，9首购已返", type="integer"),
     *                     @SWG\Property(property="rebateTime", description="时间", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function inviteRebateAction()
    {
        if($this->_pager == null){
            return $this->assignCodeAndMessage('分页参数不能为空' , 99999);
        }

        $pageInfo = [
            'pageSize' => $this->_pager->page_size ? : 10,
            'pageNo' => $this->_pager->pageid() ? : 1,
        ];

        $Model = \Prj\Model\InviteRebateInfo::getCopy('');
        $kvobjDB = $Model->dbWithTablename();

        $where = [
            'uid' => $this->userId,
            '!formUid' => $this->userId,
            'status' => [0, 1],
        ];
        $totalSize = $kvobjDB->getRecordCount($kvobjDB->kvobjTable(), $where);
        if ($totalSize) {
            $list = $kvobjDB->getRecords($kvobjDB->kvobjTable(), '*', $where, 'rsort createTime', $pageInfo['pageSize'], ($pageInfo['pageNo'] - 1) * $pageInfo['pageSize']);
            $parseStatus = function ($v) {
                if ($v['isFirstBuy']) {
                    return 8 + $v['status'];//8首购未返，9首购已返
                } else {
                    return $v['status'];//0未返，1已返
                }
            };
            if ($list) {
                foreach ($list as $k => $v) {
                    $data['inviteRebateList'][] = [
                        'uid' => $v['formUid'],
                        'phone' => substr_replace($v['formUserPhone'], '****', 3, 4),
                        'nickname' => mb_substr($v['formUserName'], 0, 1) . str_repeat('*', mb_strlen($v['formUserName']) - 1),
                        'amount' => sprintf('%.2f', $v['amount'] / 100),
                        'status' => $parseStatus($v),
                        'rebateTime' => strtotime($v['updateTime']) * 1000,
                    ];
                }
            } else {
                $data['inviteRebateList'] = [];
            }
        } else {
            $data['inviteRebateList'] = [];
        }
        $data['pageInfo'] = [
            'pageNo' => $pageInfo['pageNo'],
            'pageSize' => $pageInfo['pageSize'],
            'totalSize' => $totalSize,
            'totalPage' => ceil($totalSize / $pageInfo['pageSize']),
        ];

        $this->_view->assign('data', $data);
        return $this->assignCodeAndMessage('success');
    }

    /**
     * @SWG\Post(
     *     path="/platform/Member/friendRebate",
     *     tags={"Member"},
     *     summary="获取我邀请的好友名单-好友名单页面",
     *     @SWG\Parameter(name="pageInfo",description="分页信息JSON：{pageSize:10,pageNo:1}",type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="friendRebateList", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="uid", description="userId", type="string"),
     *                     @SWG\Property(property="phone", description="手机号", type="string"),
     *                     @SWG\Property(property="nickname", description="昵称", type="string"),
     *                     @SWG\Property(property="rebateNum", description="已返次数", type="integer"),
     *                     @SWG\Property(property="rebateAmount", description="已返金额", type="integer"),
     *                     @SWG\Property(property="rebateWaitNum", description="待返次数", type="integer"),
     *                     @SWG\Property(property="rebateWaitAmount", description="待返金额", type="integer"),
     *                     @SWG\Property(property="lastAmount", description="最后一次返利金额", type="integer"),
     *                     @SWG\Property(property="lastRebateTime", description="时间", type="integer"),
     *                     @SWG\Property(property="registerTime", description="用户注册时间", type="integer")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function friendRebateAction()
    {
        if($this->_pager == null){
            return $this->assignCodeAndMessage('分页参数不能为空' , 99999);
        }
        $pageInfo = [
            'pageSize' => $this->_pager->page_size ? : 10,
            'pageNo' => $this->_pager->pageid() ? : 1,
        ];

        $Model = \Prj\Model\InviteFinal::getCopy('');
        $kvobjDB = $Model->dbWithTablename();
        $where = [
            'uid' => $this->userId,
            '!formUid' => $this->userId,
        ];
        $totalSize = $kvobjDB->getRecordCount($kvobjDB->kvobjTable(), $where);
        if ($totalSize) {
            $list = $kvobjDB->getRecords($kvobjDB->kvobjTable(), '*', $where, 'rsort createTime', $pageInfo['pageSize'], ($pageInfo['pageNo'] - 1) * $pageInfo['pageSize']);
            $parseStatus = function ($v) {
                if ($v['isFirstBuy']) {
                    return 8 + $v['status'];//8首购未返，9首购已返
                } else {
                    return $v['status'];//0未返，1已返
                }
            };
            if ($list) {
                foreach ($list as $k => $v) {
                    $data['friendRebateList'][] = [
                        'uid' => $v['formUid'],
                        'phone' => substr_replace($v['formUserPhone'], '****', 3, 4),
                        'nickname' => $v['formUserName'] ? mb_substr($v['formUserName'], 0, 1) . str_repeat('*', mb_strlen($v['formUserName']) - 1) : '',
                        'rebateNum' => $v['rebateNum'],
                        'rebateAmount' => sprintf('%.2f', $v['rebateAmount'] / 100),
                        'rebateWaitNum' => $v['rebateWaitNum'],
                        'rebateWaitAmount' => sprintf('%.2f', $v['rebateWaitAmount'] / 100),
                        'lastAmount' => sprintf('%.2f', $v['lastAmount'] / 100),
//                        'lastRebateTime' => strtotime($v['lastRebateTime']) * 1000,
//                        'lastRebateTime' => (strtotime($v['lastRebateTime'])),
                        'lastRebateTime' => $v['lastRebateTime'] == '0000-00-00 00:00:00' ? 0 : strtotime($v['lastRebateTime']) * 1000,
//                        'status' => $parseStatus($v),
//                        'rebateTime' => $v['updateTime'],
                        'registerTime' => $v['fromUserRegTime'] == '0000-00-00 00:00:00' ? 0 : strtotime($v['fromUserRegTime']) * 1000,
                    ];
                }
            } else {
                $data['friendRebateList'] = [];
            }
        } else {
            $data['friendRebateList'] = [];
        }
        $data['pageInfo'] = [
            'pageNo' => $pageInfo['pageNo'],
            'pageSize' => $pageInfo['pageSize'],
            'totalSize' => $totalSize,
            'totalPage' => ceil($totalSize / $pageInfo['pageSize']),
        ];

        $this->_view->assign('data', $data);
        return $this->assignCodeAndMessage('success');
    }

    /**
     * @SWG\Post(
     *     path="/platform/Member/friendRebateDetail",
     *     tags={"Member"},
     *     summary="获取我邀请的好友名单-好友名单页面",
     *     @SWG\Parameter(name="pageInfo",description="分页信息JSON：{pageSize:10,pageNo:1}",type="string"),
     *     @SWG\Parameter(name="uid",description="用户的ID",type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="info", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="uid", description="userId", type="string"),
     *                     @SWG\Property(property="phone", description="手机号", type="string"),
     *                     @SWG\Property(property="nickname", description="昵称", type="string"),
     *                     @SWG\Property(property="rebateNum", description="已返次数", type="integer"),
     *                     @SWG\Property(property="rebateAmount", description="已返金额", type="integer"),
     *                     @SWG\Property(property="rebateWaitNum", description="待返次数", type="integer"),
     *                     @SWG\Property(property="rebateWaitAmount", description="待返金额", type="integer"),
     *                     @SWG\Property(property="lastAmount", description="最后一次返利金额", type="integer"),
     *                     @SWG\Property(property="lastRebateTime", description="时间", type="integer")
     *                 )
     *             ),
     *             @SWG\Property(property="inviteRebateDetailList", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="amount", description="金额", type="integer"),
     *                     @SWG\Property(property="status", description="状态位：0未返，1已返，8首购未返，9首购已返", type="integer"),
     *                     @SWG\Property(property="rebateTime", description="时间", type="integer")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function friendRebateDetailAction()
    {

        if($this->_pager == null){
            return $this->assignCodeAndMessage('分页参数不能为空' , 99999);
        }

        $formUid = $this->_request->get('uid', '');
        $pageInfo = [
            'pageSize' => $this->_pager->page_size ? : 10,
            'pageNo' => $this->_pager->pageid() ? : 1,
        ];

        $ModelInviteFinal = \Prj\Model\InviteFinal::getCopy($this->userId, $formUid);
        $ModelInviteFinal->load();
        if ($ModelInviteFinal->exists()) {
            $data['info'] = [
                'uid' => $ModelInviteFinal->getField('formUid'),
                'phone' => \Prj\Tool\Common::getInstance()->getUnsentitiveNameByPhone($ModelInviteFinal->getField('formUserPhone')),
                'nickname' => \Prj\Tool\Common::getInstance()->getUnsentitiveName($ModelInviteFinal->getField('formUserName') ?? $ModelInviteFinal->getField('formUserPhone')),
                'rebateNum' => $ModelInviteFinal->getField('rebateNum'),
                'rebateAmount' => sprintf('%.2f', $ModelInviteFinal->getField('rebateAmount') / 100),
                'rebateWaitNum' => $ModelInviteFinal->getField('rebateWaitNum'),
                'rebateWaitAmount' => sprintf('%.2f', $ModelInviteFinal->getField('rebateWaitAmount') / 100),
                'lastAmount' => sprintf('%.2f', $ModelInviteFinal->getField('lastAmount') / 100),
                'lastRebateTime' => (\Prj\Tool\TimeTool::getInstance()->getTimestamp($ModelInviteFinal->getField('lastRebateTime')) ?? 0) * 1000,
                'registerTime' => strtotime($ModelInviteFinal->getField('fromUserRegTime')) * 1000,
            ];
        } else {
            $data['info'] = $data['inviteRebateDetailList'] = [];
            $data['pageInfo'] = [
                'pageNo' => $pageInfo['pageNo'],
                'pageSize' => $pageInfo['pageSize'],
                'totalSize' => 0,
                'totalPage' => 0,
            ];
            $this->_view->assign('data', $data);
            return $this->assignCodeAndMessage('success');
        }

        $Model = \Prj\Model\InviteRebateInfo::getCopy('');
        $kvobjDB = $Model->dbWithTablename();
        $where = [
            'uid' => $this->userId,
            '!formUid' => $this->userId,
            'status' => [0,1],
        ];
        !empty($formUid) && $where['formUid'] = $formUid;
        $totalSize = $kvobjDB->getRecordCount($kvobjDB->kvobjTable(), $where);
        if ($totalSize) {
            $list = $kvobjDB->getRecords($kvobjDB->kvobjTable(), '*', $where, 'rsort createTime', $pageInfo['pageSize'], ($pageInfo['pageNo'] - 1) * $pageInfo['pageSize']);
            $parseStatus = function ($v) {
                if ($v['isFirstBuy']) {
                    return 8 + $v['status'];//8首购未返，9首购已返
                } else {
                    return $v['status'];//0未返，1已返
                }
            };
            if ($list) {
                foreach ($list as $k => $v) {
                    $data['inviteRebateDetailList'][] = [
                        'amount' => sprintf('%.2f', $v['amount'] / 100),
                        'status' => $parseStatus($v),
                        'rebateTime' => strtotime($v['updateTime']) * 1000,
                    ];
                }
            } else {
                $data['inviteRebateDetailList'] = [];
            }
        } else {
            $data['inviteRebateDetailList'] = [];
        }
        $data['pageInfo'] = [
            'pageNo' => $pageInfo['pageNo'],
            'pageSize' => $pageInfo['pageSize'],
            'totalSize' => $totalSize,
            'totalPage' => ceil($totalSize / $pageInfo['pageSize']),
        ];

        $this->_view->assign('data', $data);
        return $this->assignCodeAndMessage('success');
    }
}