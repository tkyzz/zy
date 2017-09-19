<?php

/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-27 10:49
 */
class UserpubController extends \Prj\Framework\Ctrl
{
    /**
     * @SWG\Post(
     *     path="/platform/Userpub/sendsms",tags={"Userpub"},
     *     summary="发送短信验证码，phone为空时，依次从uid和session中获取手机号",
     *     @SWG\Parameter(name="phone",description="手机号",type="integer"),
     *     @SWG\Parameter(name="uid",description="用户ID",type="string"),
     *     @SWG\Parameter(name="smsType",
     *          description="短信类型，app登录：appLogin；注册：regist；快捷登录：login；找回密码：forgetlogin；修改登录密码：editlogin；修改支付密码：payPwdUpdate",
     *          type="string",enum={"appLogin","regist","login","forgetlogin","editlogin","payPwdUpdate"}),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *              @SWG\Property(property="expiresIn", format="integer", type="integer", description="有效期，单位分", default="120")
     *          )
     *     )
     * )
     */
    public function sendsmsAction()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号')->initChecker(new \Sooh2\Valid\Regex(false, '#^1\d{10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('uid', '', '用户id')->initChecker(new \Sooh2\Valid\Str(false, 10, 40)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('smsType', '', '短信类型')->initChecker(new \Sooh2\Valid\Regex(true, '#^(appLogin|regist|login|forgetlogin|editlogin|payPwdUpdate)$#')));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            \Sooh2\Misc\Loger::getInstance()->app_trace($err);
            return $this->assignCodeAndMessage(current($err), 99998);
        }
        $inputs = $form->getInputs();

        \Sooh2\Misc\Loger::getInstance()->app_trace($inputs);
        //支持已登录用户手机号从session中获取
        if (empty($inputs['phone'])) {
            $uid = !empty($inputs['uid']) ? $inputs['uid'] : $this->getUidInSession();
            if (!empty($uid)) {
                $tmpModelUser = \Prj\Model\User::getCopy($uid);
                $tmpModelUser->load();
                if ($tmpModelUser->exists()) {
                    $phone = $tmpModelUser->getField('userAcc');
                    $inputs['phone'] = $phone;
                }
            }
        }

        if (empty($inputs['phone'])) {
            return $this->assignCodeAndMessage('请求信息有误', 99998);
        }

        //查找是否已经发送
        if (\Prj\Bll\Code::getInstance()->checkSMSCode($inputs['phone'], $inputs['smsType'])) {
            return \Prj\Bll\View::getInstance()->returnMsg($this->_view);
        }

        //过滤特定类型的请求：比如注册验证码必须是未注册用户
        switch ($inputs['smsType']) {
            case 'regist':
                $UserModel = \Prj\Model\User::getCopyByPhone($inputs['phone']);
                $UserModel->load();
                if ($UserModel->exists()) {
                    return $this->assignCodeAndMessage('当前手机号已注册', 10006);
                }
                break;
            case 'login':
            case 'forgetlogin':
            case 'editlogin':
            case 'payPwdUpdate':
                $UserModel = \Prj\Model\User::getCopyByPhone($inputs['phone']);
                $UserModel->load();
                if (!$UserModel->exists()) {
                    return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
                }

                //用户冻结检查
                if (\Prj\Bll\User::getInstance()->checkFreeze($UserModel->getField('oid'))) {
                    return $this->assignCodeAndMessage('您的帐户已被冻结，如有疑问请联系客服', 10103);
                }
                break;
            default:
                break;
        }


        /**
         * 获取发送验证码需要的事件类型
         * @param $smsType
         * @param $phone
         * @return string
         * @author lingtima@gmail.com
         */
        $funcGetTypeForMsg = function ($smsType, $phone) {
            switch ($smsType) {
                case 'regist':
                    return 'register';
                case 'login':
                    return 'quickLogin';
                case 'forgetlogin':
                    return 'pwdFind';
                case 'editlogin':
                    return 'loginPwdUpdate';
                case 'payPwdUpdate':
                    return 'payPwdUpdate';
                case 'appLogin':
                    $tmpUserModel = \Prj\Model\User::getCopyByPhone($phone);
                    $tmpUserModel->load();
                    return $tmpUserModel->exists() ? 'quickLogin' : 'register';
            }
        };

        //发送验证码
        try {
            $codeExpire = 2;//验证码有效期，单位：分钟
            $ip = \Sooh2\Util::remoteIP();
            if ($validCode = \Prj\Redis\Vcode::createVCode($ip, $inputs['phone'], $inputs['smsType'], $codeExpire * 60)) {
                \Prj\EvtMsg\SendSmsByPhone::getInstance()->sendEvtMsg($funcGetTypeForMsg($inputs['smsType'], $inputs['phone']), $inputs['phone'], ['{num1}' => $validCode, '{num2}' => $codeExpire]);
            }
            $this->_view->assign('expiresIn', $codeExpire);
            return $this->assignCodeAndMessage('OK');
        } catch (\Exception $e) {
            \Prj\Loger::out($e->getMessage());
        }
        return $this->assignCodeAndMessage('服务器繁忙,请稍后重试', 99999);
    }

    /**
     * @SWG\Post(
     *     path="/platform/Userpub/checksms",
     *     tags={"Userpub"},
     *     summary="校验短信验证码",
     *     @SWG\Parameter(name="phone",description="手机号",type="integer"),
     *     @SWG\Parameter(name="smsType",description="短信类型，app登录：appLogin；注册：regist；快捷登录：login；找回密码：forgetlogin；修改登录密码：editlogin；修改支付密码：loginPwdUpdate",type="string",enum={"regist","login","forgetlogin","editlogin","payPwdUpdate"}),
     *     @SWG\Parameter(name="veriCode",description="验证码",type="integer"),
     *     @SWG\Response(response=200, description="successful operation")
     * )
     */
    public function checksmsAction()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(null), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号')->initChecker(new \Sooh2\Valid\Regex(true, '#^1\d{10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('smsType', '', '短信类型')->initChecker(new \Sooh2\Valid\Regex(true, '#^(appLogin|regist|login|forgetlogin|editlogin|payPwdUpdate)$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('veriCode', '', '验证码')->initChecker(new \Sooh2\Valid\Regex(true, '#^\d{6}$#')));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            \Prj\Loger::out($err);
            return $this->assignCodeAndMessage(current($err), 99998);
        }

        $inputs = $form->getInputs();

        if (\Prj\Bll\Code::getInstance()->checkSMSCode($inputs['phone'], $inputs['smsType'], $inputs['veriCode'])) {
            return $this->assignCodeAndMessage();
        }

        return $this->assignCodeAndMessage('验证码无效或已过期', 99997);
    }

    /**
     * @SWG\Post(
     *     path="/platform/Userpub/login",
     *     tags={"Userpub"},
     *     summary="注册、快速注册、登录、快速登录，未注册时自动注册并登录",
     *     @SWG\Parameter(name="phone",description="手机号",type="integer"),
     *     @SWG\Parameter(name="veriCode",description="验证码",type="integer"),
     *     @SWG\Parameter(name="userPwd",description="密码",type="string"),
     *     @SWG\Parameter(name="platform",description="平台：app",type="string",enum={"pc","app","wx","PC","APP","WX","Android","IOS","H5","Landing"}),
     *     @SWG\Parameter(name="inviteCode",description="邀请码",type="string"),
     *     @SWG\Parameter(name="contractId",description="渠道ID",type="string"),
     *     @SWG\Parameter(name="tdData",description="TD数据",type="string"),
     *     @SWG\Parameter(name="tdId",description="保留字td ID",type="string"),
     *     @SWG\Parameter(name="otherArgs",description="保留字ID",type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="extendInfo",
     *                 @SWG\Property(property="UserBasicInfo",
     *                     @SWG\Property(property="userId", description="用户ID", type="string"),
     *                     @SWG\Property(property="phone", description="手机号，已脱敏", type="integer"),
     *                     @SWG\Property(property="nickname", description="昵称", type="string"),
     *                     @SWG\Property(property="isCheckin", description="是否签到：1已签到，0未签到", type="string", enum={0,1}),
     *                     @SWG\Property(property="ymdReg", description="注册年月日", type="string"),
     *                     @SWG\Property(property="wallet", description="钱包余额", type="string"),
     *                     @SWG\Property(property="inviteCode", description="我的邀请码", type="string"),
     *                     @SWG\Property(property="isRealVerifiedName", description="是否实名认证：1已实名，0未实名", type="string", enum={0,1}),
     *                     @SWG\Property(property="isBindCard", description="是否绑卡：0未绑卡，1已绑卡", type="string", enum={0,1}),
     *                     @SWG\Property(property="isRecharge", description="是否充值：0未充值，1已充值", type="string", enum={0,1}),
     *                     @SWG\Property(property="isOrder", description="是否购买：0未购买，1已购买", type="string", enum={0,1}),
     *                     @SWG\Property(property="bankCard", description="银行卡号，已脱敏", type="string"),
     *                     @SWG\Property(property="bankId", description="银行code", type="string")
     *                 )
     *             ),
     *             @SWG\Property(property="data", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="isRegister", description="是否新注册：1新注册，0不是新注册", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function loginAction()
    {
        \Prj\Loger::setKv('LOGIN');
        try {
            $inputs = $this->getFormInput();
            $inputs['platform'] = \Prj\Tool\Common::getInstance()->parseOldPlatform($inputs['platform']);

            $isRegister = false;
            $UserBll = new \Prj\Bll\User();
            $UserModel = \Prj\Model\User::getCopyByPhone($inputs['phone']);
            $UserModel->load();
            if (!$UserModel->exists()) {//用户未注册，先执行注册操作
                if (!\Prj\Bll\User::getInstance()->checkSwitchStatus('register')) {
                    return $this->assignCodeAndMessage('当前注册功能暂不可用', 10104);
                }

                //验证码优先,密码、验证码至少有一个有效,代码段会设置pwd
                if (!empty($inputs['veriCode'])) {
                    //快捷注册
                    if (!\Prj\Bll\Code::getInstance()->checkSMSCode($inputs['phone'], 'regist', $inputs['veriCode'])) {
                        if (!\Prj\Bll\Code::getInstance()->checkSMSCode($inputs['phone'], 'appLogin', $inputs['veriCode'])) {
                            return $this->assignCodeAndMessage('验证码无效或已过期', 99997);
                        }
                    }

                    //校验密码
                    $inputs['userPwd'] = $this->_request->get('userPwd');
                    if (empty($inputs['userPwd'])) {
                        unset($inputs['userPwd']);
                    } elseif (!(preg_match('#^[a-zA-Z\d]{6,16}$#', $inputs['userPwd']))) {
                        return $this->assignCodeAndMessage('密码格式不正确,必须为6 - 16位数字、字母（区分大小写）', 10002);
                    }
                } else {
                    return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
                }

                $registerRet = \Prj\Bll\User::getInstance()->newBigRegister($inputs['phone'], $inputs['contractId'], $inputs);
                if ($registerRet) {//本地注册成功
                    //本地登录
                    $uid = $registerRet->pkey()['uid'];
                    $UserBll->localLogin($uid, 0, $inputs['phone'], $inputs['platform'], ['phone' => $inputs['phone']]);
                    $isRegister = true;
                }
            } else {
                if (!\Prj\Bll\User::getInstance()->checkSwitchStatus('login')) {
                    return $this->assignCodeAndMessage('当前登录功能暂不可用', 10105);
                }

                //用户冻结检查
                if ($UserBll->checkFreeze($UserModel->getField('oid'))) {
                    return $this->assignCodeAndMessage('您的帐户已被冻结，如有疑问请联系客服', 10103);
                }

                //验证码优先,密码、验证码至少有一个有效
                if (!empty($inputs['veriCode'])) {
                    //快捷登录
                    if (!\Prj\Bll\Code::getInstance()->checkSMSCode($inputs['phone'], 'login', $inputs['veriCode'])) {
                        if (!\Prj\Bll\Code::getInstance()->checkSMSCode($inputs['phone'], 'appLogin', $inputs['veriCode'])) {
                            return $this->assignCodeAndMessage('验证码无效或已过期', 99997);
                        }
                    }
                } else {
                    //校验密码
                    $inputs['userPwd'] = $this->_request->get('userPwd');
                    if (empty($inputs['userPwd'])) {
                        return $this->assignCodeAndMessage('验证码或者密码不能都为空', 99998);
                    }
                    //检查是否锁定
                    if (\Prj\Redis\LoginFailed::isLocked($inputs['phone'], \Sooh2\Util::remoteIP())) {
                        return $this->assignCodeAndMessage('密码错误次数过多，锁定' . \Prj\Redis\LoginFailed::lockHours . '小时', 10004);
                    }
                    //是否设置过密码
                    if (empty($UserModel->getField('userPwd')) || empty($UserModel->getField('salt'))) {
                        $_return = true;
                    } elseif ($UserModel->getField('userPwd') != $UserBll->encryptPwd($inputs['userPwd'], $UserModel->getField('salt'))) {
                        //验证密码
                        $_return = true;
                    }
                    if (isset($_return) && $_return) {
                        //5次密码错误，锁定24小时，但用户仍可以用短信验证码方式登入改密码
                        if ($loginFailed = \Prj\Redis\LoginFailed::errorOccur(\Sooh2\Util::remoteIP(), $inputs['phone'])) {
                            if ($loginFailed === true) {
                                \Prj\Bll\User::getInstance()->lockUser($UserModel->getField('oid'));
                                return $this->assignCodeAndMessage('密码错误次数过多，锁定' . \Prj\Redis\LoginFailed::lockHours . '小时', 10004);
                            }
                            return $this->assignCodeAndMessage('登录名和密码不匹配，输错5次账号将被锁定' . \Prj\Redis\LoginFailed::lockHours . "小时，剩余{$loginFailed}次机会", 10102);
                        }
                        return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
                    }
                }

                //执行本地登录，保持登录状态
                if (!($ret = $UserBll->localLogin($UserModel->getField('oid'), 0, $inputs['phone'], $inputs['platform'], ['phone' => $inputs['phone']]))) {
                    return $this->assignCodeAndMessage('服务器繁忙,请稍后重试', 99999);
                }
            }

            $this->_view->assign('data', ['isRegister' => $isRegister ? 1 : 0]);
            $this->appendStatusTask('UserBasicInfo');
            return $this->assignCodeAndMessage('OK');
        } catch (\Exception $ex) {
            if (strpos($ex->getMessage(), 'try get field:') === 0) {
                return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
            }
            return $this->assignCodeAndMessage($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @SWG\Post(
     *     path="/platform/Userpub/fakelogin",
     *     tags={"Userpub"},
     *     summary="伪注册，特殊渠道使用。说明：调用伪注册后，只记录手机号-渠道号的关系。待用户真正注册时，优先从当前渠道表中查找contractId取代原先的contractId",
     *     @SWG\Parameter(name="phone",description="手机号",type="string"),
     *     @SWG\Parameter(name="contractId",description="渠道号",type="string"),
     *     @SWG\Parameter(name="contractData",description="保留字",type="string"),
     *     @SWG\Parameter(name="inviteCode",description="邀请码",type="string"),
     *     @SWG\Parameter(name="otherArgs",description="其他参数：app",type="string"),
     *     @SWG\Response(response=200, description="success")
     * )
     */
    public function fakeloginAction()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号')->initChecker(new \Sooh2\Valid\Regex(true, '#^1\d{10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contractId', '', '渠道号')->initChecker(new \Sooh2\Valid\Regex(true, '#^[a-zA-Z0-9-]{6,32}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contractData', '', '保留字'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('inviteCode', '', '邀请码')->initChecker(new \Sooh2\Valid\Regex(false, '#^[a-zA-Z0-9]{1,10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('otherArgs', '', '其他参数'));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            return $this->assignCodeAndMessage(current($err), 99998);
        }
        $inputs = $form->getInputs();
        \Sooh2\Misc\Loger::getInstance()->app_trace($inputs);


        //检测手机号是否已经注册
        $UserModel = \Prj\Model\User::getCopyByPhone($inputs['phone']);
        $UserModel->load();
        if ($UserModel->exists()) {
            return $this->assignCodeAndMessage('当前手机号已注册', 10006);
        }

        $ModelFakePhoneContract = \Prj\Model\FakePhoneContract::getCopy($inputs['phone']);
        $ModelFakePhoneContract->load();
        if ($ModelFakePhoneContract->exists()) {
            return $this->assignCodeAndMessage('当前手机号已注册', 10006);
        } else {
            $ModelFakePhoneContract->setField('contractId', $inputs['contractId']);
            $ModelFakePhoneContract->setField('contractData', $inputs['contractData']);
            $ModelFakePhoneContract->setField('inviteCode', $inputs['inviteCode']);
            $ModelFakePhoneContract->setField('otherArgs', $inputs['otherArgs']);
            if ($ret = $ModelFakePhoneContract->saveToDB()) {
                return $this->assignCodeAndMessage('OK');
            }
        }
        return $this->assignCodeAndMessage('服务器繁忙,请稍后重试', 99999);
    }

    /**
     * @SWG\Post(
     *     path="/platform/Userpub/shengcaireg",
     *     tags={"Userpub"},
     *     summary="生菜网注册专用",
     *     @SWG\Parameter(name="fr",description="来源标识",type="string"),
     *     @SWG\Parameter(name="username",description="手机号",type="string"),
     *     @SWG\Parameter(name="userpwd",description="密码",type="string"),
     *     @SWG\Parameter(name="regmod",description="端来源：app",type="string"),
     *     @SWG\Response(response=200, description="success")
     * )
     */
    public function shengcairegAction()
    {
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        header('Content-type: application/json');
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('fr', 'shengcai18', '来源标识')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('username', '', '手机号')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('userpwd', '', '密码')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('regmod', '', '端来源')->initChecker(new \Sooh2\Valid\Str(true)));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            echo \Sooh2\Util::toJsonSimple(['status' => 0, 'errinfo' => current($err), 'from' => \Sooh2\Misc\Ini::getInstance()->getIni('contract.shengcai.mark')]);
            return 0;
        }
        $inputs = $form->getInputs();

        $ModelUser = \Prj\Model\User::getCopyByPhone($inputs['username']);
        $ModelUser->load();
        if ($ModelUser->exists()) {
            echo \Sooh2\Util::toJsonSimple(['status' => 0, 'errinfo' => '用户已经注册', 'from' => \Sooh2\Misc\Ini::getInstance()->getIni('contract.shengcai.mark')]);
            return 0;
        } else {
            $contractId = \Sooh2\Misc\Ini::getInstance()->getIni('contract.shengcai.contractId');
            $registerRet = \Prj\Bll\User::getInstance()->newBigRegister($inputs['username'], $contractId, ['userPwd' => $inputs['userpwd']], false);
            if ($registerRet) {//本地注册成功
                //本地登录
                $uid = $registerRet->pkey()['uid'];
                echo \Sooh2\Util::toJsonSimple(['status' => 1, 'uid' => $uid, 'createtime' => date('Y-m-d H:i:s'), 'from' => \Sooh2\Misc\Ini::getInstance()->getIni('contract.shengcai.mark')]);
                return 1;
            }
            \Sooh2\Misc\Loger::getInstance()->app_warning('===========生菜用户注册失败！');
            echo \Sooh2\Util::toJsonSimple(['status' => 0, 'errinfo' => '用户注册失败', 'from' => \Sooh2\Misc\Ini::getInstance()->getIni('contract.shengcai.mark')]);
            return 0;
        }
    }

    /**
     * @SWG\Post(
     *     path="/platform/Userpub/logout",
     *     tags={"Userpub"},
     *     summary="登出",
     *     @SWG\Parameter(name="platform",description="平台",type="string"),
     *     @SWG\Response(response=200, description="success")
     * )
     */
    public function logoutAction()
    {
        $platform = $this->_request->get('platform', 'APP');
        $platform = \Prj\Tool\Common::getInstance()->parseOldPlatform($platform);

        $uid = $this->getUidInSession();
        if (empty($uid)) {
            return $this->assignCodeAndMessage('OK');
        }

        $UserBll = \Prj\Bll\User::getInstance();
        if ($ret = $UserBll->localLogout($uid, $platform)) {
            $this->_view->assign('data', ['uid' => $uid]);
            return $this->assignCodeAndMessage('OK');
        } else {
            return $this->assignCodeAndMessage('OK');
        }
    }

    /**
     * @SWG\Post(
     *     path="/platform/userpub/setpwd",
     *     tags={"Userpub"},
     *     summary="设置用户密码(首次设置)",
     *     @SWG\Parameter(name="newPwd",description="新密码",type="string"),
     *     @SWG\Parameter(name="newPwd2",description="新密码",type="string")
     * )
     */
    public function setpwdAction()
    {
        if (($uid = $this->getUidInSession()) == null) {
            return $this->assignCodeAndMessage('登录状态已失效,请重新登录', 10001);
        }

        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('newPwd', '', '新密码')->initChecker(new \Sooh2\Valid\Regex(true, '#^[a-zA-Z\d]{6,16}$#')))
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('veriCode', '', '平台')->initChecker(new \Sooh2\Valid\Regex(true, '#^\d{6}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('newPwd2', '', '新密码')->initChecker(new \Sooh2\Valid\Regex(true, '#^[a-zA-Z\d]{6,16}$#')));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            return $this->assignCodeAndMessage(current($err), 19808);
        }
        $inputs = $form->getInputs();
        if ($inputs['newPwd'] != $inputs['newPwd2']) {
            return $this->assignCodeAndMessage('请输入相同的密码后再试', 19808);
        }

        $ModelUser = \Prj\Model\User::getCopy($uid);
        $ModelUser->load();
        if (!$ModelUser->exists()) {
            return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
        }
        try {
            if (!empty($ModelUser->getField('userPwd'))) {
                return $this->assignCodeAndMessage('已经设置过密码，请不要重复设置', 10101);
            }
        } catch (\Exception $e) {
            //donothing
        }
//        $phone = $ModelUser->getField('userAcc');
//        $ret = \Prj\Redis\Vcode::fetchVCode(\Sooh2\Util::remoteIP(), $phone, 'forgetlogin');
//        if (empty($ret) || $inputs['veriCode'] != $ret) {
//            return $this->assignCodeAndMessage('验证码不正确', 19998);
//        }

        $BllUser = \Prj\Bll\User::getInstance();
//        if (!$BllUser->beforeUpdatePwd($uid, $inputs['newPwd'])) {
//            return $this->assignCodeAndMessage('', 11111);
//        }

        $salt = mt_rand(10000000, 99999999);
        $ModelUser->setField('userPwd', $BllUser->encryptPwd($inputs['newPwd'], $salt));
        $ModelUser->setField('salt', $salt);
        $ModelUser->saveToDB();
        $BllUser->updatePwd($uid);
        return $this->assignCodeAndMessage('OK');
    }

    /**
     * @SWG\Post(
     *     path="/platform/userpub/updpwd",
     *     tags={"Userpub"},
     *     summary="修改用户密码",
     *     @SWG\Parameter(name="oldPwd",description="旧密码",type="string"),
     *     @SWG\Parameter(name="newPwd",description="新密码",type="string"),
     *     @SWG\Parameter(name="newPwd2",description="新密码",type="string")
     * )
     */
    public function updpwdAction()
    {
        if (($uid = $this->getUidInSession()) == null) {
            return $this->assignCodeAndMessage('登录状态已失效,请重新登录', 10001);
        }

        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('newPwd', '', '新密码')->initChecker(new \Sooh2\Valid\Regex(true, '#^[a-zA-Z\d]{6,16}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('oldPwd', '', '旧密码')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('newPwd2', '', '新密码')->initChecker(new \Sooh2\Valid\Regex(true, '#^[a-zA-Z\d]{6,16}$#')));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            return $this->assignCodeAndMessage(current($err), 99998);
        }

        $inputs = $form->getInputs();
        if ($inputs['newPwd'] != $inputs['newPwd2']) {
            return $this->assignCodeAndMessage('请输入相同的密码再试', 99998);
        }
        if ($inputs['newPwd'] == $inputs['oldPwd']) {
            return $this->assignCodeAndMessage('新登录密码与原登录密码不能相同', 10008);
        }

        //检查是否锁定
        if (\Prj\Redis\LoginFailed::isLocked($inputs['phone'], \Sooh2\Util::remoteIP())) {
            return $this->assignCodeAndMessage('密码错误次数过多，锁定' . \Prj\Redis\LoginFailed::lockHours . '小时', 10004);
        }

        $BllUser = new \Prj\Bll\User();
        $ModelUser = \Prj\Model\User::getCopy($uid);
        $ModelUser->load();
        if ($ModelUser->exists()) {
            //是否设置过密码
            if (empty($ModelUser->getField('userPwd')) || empty($ModelUser->getField('salt'))) {
                $_return = true;
            } elseif ($ModelUser->getField('userPwd') != $BllUser->encryptPwd($inputs['oldPwd'], $ModelUser->getField('salt'))) {
                //验证密码
                $_return = true;
            }
            if (isset($_return) && $_return) {
                //5次密码错误，锁定24小时，但用户仍可以用短信验证码方式登入改密码
                if ($loginFailed = \Prj\Redis\LoginFailed::errorOccur(\Sooh2\Util::remoteIP(), $inputs['phone'])) {
                    if ($loginFailed === true) {
                        \Prj\Bll\User::getInstance($ModelUser->getField('oid'));
                        return $this->assignCodeAndMessage('密码错误次数过多，锁定' . \Prj\Redis\LoginFailed::lockHours . '小时', 10004);
                    }
                    return $this->assignCodeAndMessage('登录名和密码不匹配，输错5次账号将被锁定' . \Prj\Redis\LoginFailed::lockHours . "小时，剩余{$loginFailed}次机会", 10102);
                }
                return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
            }

            $salt = mt_rand(10000000, 99999999);
            $ModelUser->setField('userPwd', $BllUser->encryptPwd($inputs['newPwd'], $salt));
            $ModelUser->setField('salt', $salt);
            $ModelUser->saveToDB();
            $BllUser->updatePwd($uid);
            return $this->assignCodeAndMessage('OK');
        } else {
            return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
        }
    }

    /**
     * @SWG\Post(
     *     path="/platform/userpub/forgetpwd",
     *     tags={"Userpub"},
     *     summary="找回用户密码",
     *     @SWG\Parameter(name="phone",description="手机号",type="string"),
     *     @SWG\Parameter(name="veriCode",description="验证码",type="string"),
     *     @SWG\Parameter(name="newPwd",description="新密码",type="string"),
     *     @SWG\Parameter(name="newPwd2",description="新密码",type="string")
     * )
     */
    public function forgetpwdAction()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('newPwd', '', '新密码')->initChecker(new \Sooh2\Valid\Regex(true, '#^[a-zA-Z\d_]{6,16}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号')->initChecker(new \Sooh2\Valid\Regex(true, '#^1\d{10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('veriCode', '', '验证码')->initChecker(new \Sooh2\Valid\Regex(true, '#^\d{6}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('newPwd2', '', '新密码')->initChecker(new \Sooh2\Valid\Regex(true, '#^[a-zA-Z\d_]{6,16}$#')));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            return $this->assignCodeAndMessage(current($err), 99998);
        }
        $inputs = $form->getInputs();
        if ($inputs['newPwd'] != $inputs['newPwd2']) {
            return $this->assignCodeAndMessage('请输入相同的密码后再试', 99998);
        }

        $ModelUser = \Prj\Model\User::getCopyByPhone($inputs['phone']);
        $ModelUser->load();
        if (!$ModelUser->exists()) {
            return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
        }
//        $ret = \Prj\Redis\Vcode::fetchVCode(\Sooh2\Util::remoteIP(), $inputs['phone'], 'forgetlogin');
//        if (empty($ret) || $inputs['veriCode'] != $ret) {
//        }
        if (!\Prj\Bll\Code::getInstance()->checkSMSCode($inputs['phone'], 'forgetlogin', $inputs['veriCode'])) {
            return $this->assignCodeAndMessage('账号不存在或密码错误', 10003);
        }

        $BllUser = new \Prj\Bll\User();

//        if (!$BllUser->beforeUpdatePwd($ModelUser->getField('oid'), $inputs['newPwd'])) {
//            return $this->assignCodeAndMessage('', 11111);
//        }

        $salt = mt_rand(10000000, 99999999);
        $ModelUser->setField('userPwd', $BllUser->encryptPwd($inputs['newPwd'], $salt));
        $ModelUser->setField('salt', $salt);
        $ModelUser->saveToDB();
        $BllUser->updatePwd($ModelUser->getField('oid'));
        return $this->assignCodeAndMessage('OK');
    }

    /**
     * 检查获取用户输入
     * @return array
     * @throws ErrorException
     * @author lingtima@gmail.com
     */
    protected function getFormInput()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号')->initChecker(new \Sooh2\Valid\Regex(true, '#^1\d{10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('veriCode', '', '验证码')->initChecker(new \Sooh2\Valid\Regex(false, '#^\d{6}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('platform', '', '平台')->initChecker(new \Sooh2\Valid\Str(false, 2, 20)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('clientId', '', '设备ID')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('inviteCode', '', '邀请码')->initChecker(new \Sooh2\Valid\Regex(false, '#^[a-zA-Z0-9]{1,10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contractId', '', '渠道号')->initChecker(new \Sooh2\Valid\Regex(false, '#^[a-zA-Z0-9-]{6,32}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contractData', '', '保留字ID')->initChecker(new \Sooh2\Valid\Regex(false, '#^[a-zA-Z0-9]{6,32}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('tdData', '', '设备号')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('tdId', '', '保留字tdId')->initChecker(new \Sooh2\Valid\Regex(false, '#^[a-zA-Z0-9]{6,32}$#')));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            throw new \ErrorException(current($err), 99998);
        }
        $inputs = $form->getInputs();
        empty($inputs['platform']) && $inputs['platform'] = 'app';
        return $inputs;
    }

    /**
     * @SWG\Post(
     *     path="/platform/Userpub/calendar",
     *     tags={"Userpub"},
     *     summary="回款日历",
     *     @SWG\Parameter(name="month",description="月份",type="string",in="formData"),
     *     @SWG\Parameter(name="day",description="日期",type="string",in="formData"),
     * )
     */
    public function calendarAction(){
        \Prj\Loger::setKv(__METHOD__);
        \Prj\Loger::outVal('data' , $this->_request->getParams());
        $userId = $this->getUidInSession();
        if(empty($userId))return $this->assignCodeAndMessage('登录状态已失效,请重新登录' , 10001);
        $params = [
            'userId' => $userId,
            'month' => $this->_request->getParam('month'),
        ];
        $res = \Prj\Bll\Refund::getInstance()->calendar($params);
        $this->assignRes($res);
    }
}