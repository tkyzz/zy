<?php
/**
 *
swagger ： http://106.14.236.8/phpswagger/swagger-ui/#!/Coupon/post_actives_coupon_proCoupons



 *      ----------------------------------------  汤高航 
/jzucapp/getMyData  平台数据查询接获取未读站内信数量，可使用优惠券数量 - ok
/jzucapp/convertRedPackets 兑换现金红包   - 涉及给用户加钱
/jzucapp/getPlatformData 首页统计数据查询  - ok
/jzucapp/getRequestIp                     -
/jzucapp/getMyCoupon 获取用户优惠券       - ok
/cms/app/home?channelOid=000000005a83152e015a894dfa380001  -ok
/jzucapp/getMyUserRebateDetail 获取我邀请的好友详细返利  - ok
/jzucapp/user/getUserInfo 获取用户信息--用于更新用户是否是新手   {"idNumb":"****3817","userOid":"ff8080815ca57d25015ce2a0f519266c","fullBankCardNum":"6217001210094644661","fullIdNumb":"370602194309063817","errorMessage":"","userPwd":true,"errorCode":0,"fullName":"王壮","bankName":"中国建设银行","source":"frontEnd","userAcc":"18916671445","islogin":true,"bankPhone":"189****1445","createTime":"2017-06-26 12:20:18","sceneid":1039815,"name":"王*","bankCardNum":"****4661","paypwd":true,"channelid":"117120170615234525","memberId":"117UID2017062600000790","status":"normal"}

 *  *  *  * *  -----------------------------------------梁言庆
 ---------老的注册登入接口，全部改用之前宝宝树的那个
/jzucapp/getMyUserRebate 获取我邀请的好友  
/jzucapp/getMyUserRefereInfo 获取我邀请的好友统计
 *  
-----------------------------------------------------------这两个可以再缓一下
/jzucapp/saveClientTrans 上报td返填信息    
/jzucapp/weChart/jsApiTicket/v2 微信js临时票据  

 * 改下trigger, user on create 的时候 insert ignore
10003,"登录失败，请稍后重试！","国槐登录失败"
20001,"服务器繁忙，请稍后重试！","系统报错了"
20002,"数据格式错误","json格式错误"
12000,站内信不存在
12001,
-1,"请求失败","国槐反馈为错误"
30002,"手机号已注册！"
30003,"手机号未注册"
30007,"账号异常！请联系客服" --多个手机号码相同账号
30004,"未设置固定密码" --密码登录
 * 
 * 需要替换的接口，需要在nginx里配置：
 * --  只换一个命令的情况
        location /jzucapp/tmp/register {
            root    html/php/html;
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  oldapi.php;
            fastcgi_param  SCRIPT_FILENAME   $document_root/oldapi.php;
            include        fastcgi_params;
        }
 * --  换一批，剩下的转发的模式：
        location /jzucapp/{
            root    html/php/html;
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  oldapi.php;
            fastcgi_param  SCRIPT_FILENAME   $document_root/oldapi.php;
            include        fastcgi_params;
        }
 * @author simon.wang
 */
class OldverController extends \Prj\Framework\OldApiCtrl
{
    public function testAction()
{
	$_COOKIE['GH-SESSION'] = '25fd3ad7-789c-42aa-8853-21f7d8f50764';
	$uid = $this->getUidInSession();
echo $uid;
	exit;
}
    public function dispatchAction()
    {
        $req = trim($this->_request->get('reqcmdoldver'),'/');
//        error_log("wangning enter dispatch:".$req);
        
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        \Prj\Loger::setTag('Oldver');
        \Prj\Loger::out($req);
        $tail = '';
        switch (strtolower($req)){
            // case 'jzucapp/tmp/register':        return $this->tempregAction();  
            //case 'wfduc/client/user/login':
            case 'jzucapp/login':
                 return $this->loginAction();
            case 'wfduc/client/sms/sendvc':
            case 'jzucapp/sendvc':              return $this->sendvcAction();
            case 'wfduc/client/sms/checkvc':    return $this->checkvcAction();
            case 'wfduc/client/user/register':
            case 'jzucapp/register':            return $this->registerAction();
            case 'mimosa/client/switch/find':   return $this->oldCheck();
            case 'jzucapp/getRequestIp':        return $this->oldGetRequestIp();
            case 'jzucapp/getmyuserrebate':     return $this->getMyUserRebate();
            case 'jzucapp/getmyuserrefereinfo': return $this->getMyUserRefereInfo();
//            case 'jzucapp/getUpdateApp':
//                error_log("wangning trace getUpdateApp replaced");
//                $curl = \Sooh2\Curl::factory();
//                $host = 'http://'.\Sooh2\Misc\Ini::getInstance()->getIni('application.serverip.jzucapp');
//                $arg = file_get_contents('php://input');
//                \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
//                $ret = $curl->httpPost($host.'/jzucapp/getUpdateApp', $arg);
//                error_log("wangning trace getUpdateApp replaced:$ret");
//                error_log("wangning trace getUpdateApp replaced:".var_export($curl->cookies,true));
//                header('Content-type: application/json');
//                echo $ret;
//                exit;
            case 'old/userpub/login2':          return $this->login2Action(); 
            case 'cms/client/mail/noreadnum':   return $this->letterNotReadAction();
            case 'cms/client/mail/detail':      return $this->letterDetailAction();
            case 'cms/client/mail/allread':     return $this->letterallReadAction();
            case 'jzucapp/getUserMail':         return $this->letterlistAction();
            case 'jzucapp/getMailType':         return $this->lettertypeAction();
            case 'jzucapp'.$tail.'/getmycoupon':return $this->getMyCouponAction(); //获取我的已使用优惠券列表
            case 'mimosa'.$tail.'/client/tulip/myallcoupon':return $this->myallcouponAction(); //获取我的未使用或过期优惠券列表
            case 'jzucapp'.$tail.'/getplatformdata':return $this->getPlatformDataAction(); //app首页统计
            case 'jzucapp'.$tail.'/getmydata':  return $this->getMyDataAction(); //获取我的未使用优惠券+未读信息
            case 'cms'.$tail.'/app/home':       return $this->cmsHomeAction(); //banner
            case 'jzucapp/getmyuserrebatedetail';return $this->getMyUserRebateDetailAction();

            default:
                $ret = $this->callGHUC('/wfduc/client/user/register', file_get_contents('php://input'));
                header('Content-type: application/json');
                echo $ret;
                exit;
        }
    }
    public function oldCheck()
    {
        $this->_view->assign('code',$this->_request->get('code'));
        $this->_view->assign('status','enable');
        $this->_view->assign('type','switch');
        $this->_view->assign('content','');
        $this->renderWithCodeMsg();
    }
    public function oldGetRequestIp()
    {
        $this->_view->assign('datas',array('getTime'=>time().'000','ip'=>\Sooh2\Util::remoteIP()));
        $this->renderWithCodeMsg();
    }
    
    /**
     * 未读短信数量：替换 //cms/client/mail/noreadnum
     * 返回{"errorCode":0,"errorMessage":null,"num":15}
     */
    public function letterNotReadAction()
    {
        $uid = $this->getUidInSession();
        if(empty($uid)){
            return $this->renderWithCodeMsg('10002','用户末登录或会话已超时');
        }
        $this->_view->assign('num', \Prj\Model\Letter::getNumOfUnread($uid));
        $this->renderWithCodeMsg();

    }
    /**
     * 全部设置为已读：替换 cms/client/mail/allread
     * 返回{"errorCode":0,"errorMessage":null}
     */
    public function letterallReadAction()
    {
        $uid = $this->getUidInSession();
        if(empty($uid)){
            return $this->renderWithCodeMsg('10002','用户末登录或会话已超时');
        }
        \Prj\Model\Letter::markAllRead($uid);
        $this->renderWithCodeMsg();

    }    
    /**
     * 短信内容：替换 cms/client/mail/detail
     * 返回{"errorCode":0,"errorMessage":null,"oid":"ff8080815ccfd8a7015cea4c5608411a","mailType":"person","mesType":"system",
     * "mesTitle":"计息提醒","mesContent":"您投资的悦享盈221期理财产品开始计息！详情请查看我的收益。","isRead":null,"updateTime":"2017-06-28 00:04:50"}
     */    
    public function letterDetailAction()
    {
        $uid = $this->getUidInSession();
        if(empty($uid)){
            return $this->renderWithCodeMsg('10002','用户末登录或会话已超时');
        }
        $mailOid = $this->_request->get('mailOid');
        $obj = \Prj\Model\Letter::getCopy($mailOid);
        $obj->load();
        if(!$obj->exists() ){
            return $this->renderWithCodeMsg('12000','站内信不存在');
        }elseif($obj->getField('userOid'!=$uid)){
            return $this->renderWithCodeMsg('12001','没有权限查看该站内信！');
        }
        
        $this->_view->assign('oid', $mailOid);
        $this->_view->assign('mailType', 'person');
        $this->_view->assign('mesType', 'system');
        $this->_view->assign('mesTitle', $obj->getField('mesTitle'));
        $this->_view->assign('mesContent', $obj->getField('mesContent'));
        $this->_view->assign('isRead', $obj->getField('isRead'));
        $this->_view->assign('updateTime', $obj->getField('updateTime'));
        try{
            if($obj->getField('isRead')=='no'){
                $obj->setField('isRead', 'is');
                $obj->saveToDB();
            }
        }catch(\ErrorException $e){
            \Sooh2\Misc\Loger::getInstance()->app_warning('更改站内信（'.$mailOid.'）状态为已读失败了，'.$e->getMessage()."\n".$e->getTraceAsString());
        }
        $this->renderWithCodeMsg();
        
    }
    /**
     * 短信列表 ：替换 /jzucapp/getMailType
     * 参数 {"page":1,"rows":10,"isRead":"no","typeCode":"invest"}
     */
    public function lettertypeAction()
    {
        $data =array();
        foreach(\Prj\Model\Letter::$types as $id=>$r){
            $data[]=array('typeCode'=>$id,'typeName'=>$r['name']);
        }
        $this->_view->assign('datas',$data);

        $this->renderWithCodeMsg();

    }
    
    /**
     * 短信列表 ：替换 /jzucapp/getUserMail
     * 参数 {"page":1,"rows":10,"isRead":"no","typeCode":"invest"}
     */
    public function letterlistAction()
    {
        $uid = $this->getUidInSession();
        if(empty($uid)){
            return $this->renderWithCodeMsg('10002','用户末登录或会话已超时');
        }
        $pager = new \Sooh2\DB\Pager($this->_request->get('rows'));
        $db = \Prj\Model\Letter::getCopy(null)->dbWithTablename(0, true);
        $where=array('userOid'=>$uid);
        $isRead =$this->_request->get('isRead');
        if($isRead=='no'){
            $where['isRead']='no';
        }
        $type = $this->_request->get('typeCode');
        if(!empty($type) && isset(\Prj\Model\Letter::$types[$type])){
            $where = array_merge($where,\Prj\Model\Letter::$types[$type]);
        }
        $pager->init($db->getRecordCount($db->kvobjTable(), $where), $this->_request->get('page'));
        $data = array('content'=>array(),'size'=>$pager->page_size,'total'=>$pager->total,'totalPages'=>$pager->page_size);
        $data['content']=$db->getRecords($db->kvobjTable(), 'UNIX_TIMESTAMP(createTime)*1000 as createTime,isRead,mailType,mesContent,mesTitle,oid,UNIX_TIMESTAMP(updateTime)*1000 as updateTime',$where,'rsort createTime',$pager->page_size,$pager->rsFrom());
        $this->_view->assign('datas',$data);

        $this->renderWithCodeMsg();

    }
    
    /**
     * 注册: 
     * /jzucapp/tmp/register
     * { "userAcc": "手机号","platform":"app","vericode":"验证码","channelid":"345...", 
     * 'userPwd':"密码，可选",sceneId:"邀请码，可选" }
     * /wfduc/client/user/register
     * /jzucapp/register
     * 
     */
    public function registerAction()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('userAcc', '', '手机号')->initChecker(new \Sooh2\Valid\Str(true, 11, 11)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('userPwd', '', '用户密码')->initChecker(new \Sooh2\Valid\Str(false, 6, 16)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('vericode', '', '验证码')->initChecker(new \Sooh2\Valid\Str(false, 1, 10)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('platform', '', '平台')->initChecker(new \Sooh2\Valid\Str(false, 1, 10)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('channelid', '', '渠道号')->initChecker(new \Sooh2\Valid\Str(false, 1, 32)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('sceneId', '', '邀请码')->initChecker(new \Sooh2\Valid\Str(false, 1, 15)));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            $this->outPut(['code' => 19998, 'message' => current($err)]);
            return 0;
        }

        $inputs = $form->getInputs();
        if (isset($inputs['vericode']) && !empty($inputs['vericode'])) {
            $ret = \Prj\Redis\Vcode::fetchVCode(\Sooh2\Util::remoteIP(), $inputs['userAcc'], 'regist');
            if ($ret === false) {
                $this->outPut(['code' => 20001, 'message' => '尝试次数过多，请明天再试']);
                return 0;
            } else {
                if ($inputs['vericode'] != $ret) {
                    $this->outPut(['code' => 20000, 'message' => '无效的验证码']);
                    return 0;
                }
            }
        }

        //调用自己的register方法-报错
        $Register = new \Prj\GH\Register();
        $step = $Register->doit($inputs['userAcc'], $inputs['userPwd'], $inputs['channelid'], $inputs['sceneId']);
        if ($step == 99) {
            $this->outPut(['code' => 0, 'message' => '注册成功']);
            return 1;
        }
        $this->outPut(['code' => 0, 'message' => '注册失败，请您稍后再试']);
        return 0;
    }
    /**
     * 临时注册
     * /jzucapp/tmp/register
     */
    public function tempregAction()
    {
        error_log("wangning enter tempreg");
        $phone = $this->_request->getParam('userAcc');
        $platform = $this->_request->getParam('platform','pc');
        $vericode = $this->_request->getParam('vericode');
        $channelid = $this->_request->getParam('channelid');
        error_log("wangning pararm $phone $platform $vericode $channelid");
        $this->renderWithCodeMsg(30002,"手机号已经注册");
    }
    /**
     * 发送验证码
     * /wfduc/client/sms/sendvc
     * /jzucapp/sendvc
     * {  "phone": "",   "smsType": "regist|login",   "values": ["", "2"]}
     */
    public function sendvcAction()
    {
        $phone = $this->_request->getParam('phone');
        $smsType = $this->_request->getParam('smsType','regist');

        switch ($smsType) {
            case 'regist'://注册
                $tmp = \Prj\Model\User::getCopyByPhone($phone);
                $tmp->load();
                if($tmp->exists()){
                    $this->outPut(['code' => 30002, 'message' => '手机号已经注册']);
                    return 0;
                }
                break;
            case 'login'://登录
            case 'forgetlogin'://忘记登录密码
            case 'editlogin'://修改登录密码
                $tmp = \Prj\Model\User::getCopyByPhone($phone);
                $tmp->load();
                if(!$tmp->exists()){
                    $this->outPut(['code' => 30002, 'message' => '手机号未注册']);
                    return 0;
                }
                break;
            default :
                $this->outPut(['code' =>30002, 'message' => '类型参数不正确']);
                return 0;
        }

        $values = $this->_request->getParam('values',array("","2"));
        $vcode = \Prj\Redis\Vcode::createVCode(\Sooh2\Util::remoteIP(), $phone, $smsType, 120);
        error_log('vcode is ::::::::::::::::::::::::::'.$vcode);
        if($vcode===false){
            $this->outPut(['code' => 20001, 'message' => '尝试次数过多，请明天再试']);
            return 0;
        }else{
            $funcGetEvtMsgId = function ($smsType) {
                switch ($smsType) {
                    case 'regist':
                        return 'register';
                    case 'login':
                        return 'quickLogin';
                    case 'forgetlogin':
                        return 'pwdFind';
                    case 'editlogin':
                        return 'loginPwdUpdate';
                }
            };
            \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg($funcGetEvtMsgId($smsType), $phone, ['{num1}' => $vcode, '{num2}' => 2]);
            $this->outPut(['code' => 0, 'message' => '验证码已经发送']);
            return 1;
        }
    }
    /**
     * 验证验证码
     * /wfduc/client/sms/checkvc
     * {   "phone": "13389238223",  "veriCode": "941422",   "smsType": "regist"  }
     */
    public function checkvcAction()
    {
        $phone = $this->_request->getParam('phone');
        $veriCode = $this->_request->getParam('veriCode');
        $smsType = $this->_request->getParam('smsType','regist');
        $ret = \Prj\Redis\Vcode::fetchVCode(\Sooh2\Util::remoteIP(), $phone, $smsType);
        if($ret===false){
            $this->renderWithCodeMsg(20001,"尝试次数过多，请明天再试");
        }else{
            if($veriCode==$ret){
                $this->renderWithCodeMsg(0,"pass");
            }else{
                $this->renderWithCodeMsg(20000,"无效的验证码");
            }
            
        }
    }
    /**
     * 登入
     * /wfduc/client/user/login
     * {"userAcc": "13389238223",userPwd":"","veriCode": "941422","platform": "app" }
     * /jzucapp/login
     * {
    	"datas": {"uid": "ff8080815a8451f4015a924c1d810021"},
    	"errorCode": 0,
    	"errorMessage": "",
    	"picResp": {"needUpdate": false,"pics": []},
    	"uid": "ff8080815a8451f4015a924c1d810021",
    	"versions": []
    }
     */
    public function loginAction()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('userAcc', '', '手机号')->initChecker(new \Sooh2\Valid\Str(true, 11, 11)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('userPwd', '', '用户密码')->initChecker(new \Sooh2\Valid\Str(false, 6, 16)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('vericode', '', '验证码')->initChecker(new \Sooh2\Valid\Str(false, 1, 10)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('platform', '', '平台')->initChecker(new \Sooh2\Valid\Str(false, 1, 10)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('clientId', '', '设备号')->initChecker(new \Sooh2\Valid\Str(false)));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            $this->outPut(['code' => 19998, 'message' => current($err)]);
            return 0;
        }

        $inputs = $form->getInputs();
        /**
         * 调用国槐登录接口
         * @param $ghArgs
         * @return array
         * @author lingtima@gmail.com
         */
        $funcGHRegister = function ($ghArgs) {
            /**
             * 过滤空参数
             * @param $arr
             * @return array
             * @author lingtima@gmail.com
             */
            $funcFilterParams = function ($arr) {
                $tmp = array_filter($arr, function ($v) {
                    if ($v === null || $v === '') {
                        return false;
                    } else {
                        return true;
                    }
                });

                return $tmp;
            };

            //调用国槐的登入接口
            $ghArgs = $funcFilterParams($ghArgs);
            $ret = $this->rpcGHUC('/wfduc/client/user/login', json_encode($ghArgs));
            $arr = json_decode($ret, true);
            return $arr;
        };
        //TODO 调用国槐登录
        $GHRet = $funcGHRegister([
            'userAcc' => $inputs['userAcc'],
            'userPwd' => $inputs['userPwd'],
            'vericode' => $inputs['vericode'],
            'clientId' => $inputs['clientId'],
            'platform' => $inputs['platform'],
        ]);
        \Prj\Loger::out($GHRet);
        if (is_array($GHRet)) {
            $this->outPut(['code' => $GHRet['errorCode'], 'message' => $GHRet['成功'], 'data' => ['uid' => $GHRet['uid']], 'uid' => $GHRet['uid']]);
            return 1;
        } else {
            $this->outPut(['code' => 19998, 'message' => '系统服务繁忙，请稍后再试']);
            return 0;
        }

        //执行本地登录，保持登录状态
//        $UserBll = new \Prj\Bll\User();
//        $UserModel = \Prj\Model\User::getCopyByPhone($inputs['userAcc']);
//        $UserModel->load();
//        if (!($ret = $UserBll->localLogin($UserModel->getField('oid')))) {
//            $this->render(19998, '系统服务繁忙，请稍后再试');
//            return 0;
//        }
//        $this->render(0, '登录成功');
//        return 0;
    }

    /**
     * @SWG\Post(
     *     path="/old/Oldver/login2",
     *     tags={"Oldver"},
     *     summary="宝宝树注册",
     *     @SWG\Parameter(name="phone",description="手机号",type="integer"),
     *     @SWG\Parameter(name="veriCode",description="验证码",type="integer"),
     *     @SWG\Parameter(name="userPwd",description="密码",type="string"),
     *     @SWG\Parameter(name="platform",description="平台：app",type="string",enum={"pc","app","wx"}),
     *     @SWG\Parameter(name="inviteCode",description="邀请码",type="string"),
     *     @SWG\Parameter(name="contractId",description="渠道ID",type="string"),
     *     @SWG\Parameter(name="contractData",description="保留字ID，此处同openId",type="string"),
     *     @SWG\Parameter(name="tdId",description="保留字td ID",type="string"),
     *     @SWG\Parameter(name="otherArgs",description="其他字段",type="string"),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="userinfo",
     *                 @SWG\Property(property="userId", description="用户ID", type="string"),
     *                 @SWG\Property(property="phone", description="手机号", type="integer"),
     *                 @SWG\Property(property="nickname", description="昵称", type="string"),
     *                 @SWG\Property(property="contractId", description="渠道号", type="string"),
     *                 @SWG\Property(property="inviteCode", description="邀请码", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function login2Action()
    {
        $form = new \Sooh2\HTML\Form\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号')->initChecker(new \Sooh2\Valid\Regex(true, '#^1\d{10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('userPwd', '', '用户密码')->initChecker(new \Sooh2\Valid\Str(false, 6, 16)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('veriCode', '', '验证码')->initChecker(new \Sooh2\Valid\Regex(false, '#^\d{6}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('platform', '', '平台')->initChecker(new \Sooh2\Valid\Regex(false, '#^(pc|app|wx)$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('inviteCode', '', '邀请码')->initChecker(new \Sooh2\Valid\Regex(false, '#^[a-zA-Z0-9]{6,10}$#')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contractId', '', '渠道号')->initChecker(new \Sooh2\Valid\Str(false, 6, 32)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contractData', '', '保留字ID')->initChecker(new \Sooh2\Valid\Str(false, 6, 32)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('tdId', '', '保留字tdID')->initChecker(new \Sooh2\Valid\Str(false, 6, 32)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('otherArgs', '', '其他字段'));
        $form->checkReqParams($this->_request);
        if (!empty($err = $form->getErrors())) {
            $this->renderWithCodeMsg(19998,current($err));
            return 0;
        }
        $inputs = $form->getInputs();
        $funcFilterParams = function ($arr) {
            $tmp = array_filter($arr, function ($v) {
                if ($v === null || $v === '') {
                    return false;
                } else {
                    return true;
                }
            });

            return $tmp;
        };

        $inputs = $funcFilterParams($inputs);
        //调用国槐的登入接口
        $ghArgs = [
            'userAcc' => $inputs['phone'],
            'userPwd' => $inputs['userPwd'],
            'vericode' => $inputs['veriCode'],
            'platform' => $inputs['platform'],
            'channelid' => $inputs['contractId'],
        ];
        $ghArgs = $funcFilterParams($ghArgs);
        $ret = $this->rpcGHUC('/jzucapp/register', json_encode($ghArgs));
        $arr= json_decode($ret,true);
        if(!is_array($arr) || !isset($arr['errorCode']) || $arr['errorCode'] != 0){
            \Sooh2\Misc\Loger::getInstance()->app_warning('forward regist reqest failed: '.$ret);
            if (is_array($arr)) {
                $this->renderWithCodeMsg($arr['errorCode'],$arr['errorMessage']);
                return 0;
            }
            $this->renderWithCodeMsg(20001,"系统繁忙请稍后重试");
            return 0;
        }

        //本地PHP注册
        $BllUser = \Prj\Bll\User::getInstance();
        if ($retRegister = $BllUser->register2($inputs['phone'], $inputs['userPwd'])) {
            $userFinalPkey = $retRegister->pkey();
            $retContract = $BllUser->createContract($userFinalPkey['uid'], $inputs['platform'], $inputs['contractId'], $inputs['contractData'], $inputs['tdId'], $inputs['otherArgs'], $inputs['inviteCode']);
            $this->renderWithCodeMsg(0, '注册成功');
            return 0;
        }

        $this->renderWithCodeMsg(20001, '注册失败，请您稍后再试');
        return 0;
    }

    /**
     * Hand 获取我的已使用的券
     */
    public function getMyCouponAction(){
        $params['userId'] = $this->_request->getParam('userId');
        $params['status'] = $this->_request->getParam('status');
        $params['page'] = $this->_request->getParam('page');
        $params['rows'] = $this->_request->getParam('rows');
        \Prj\Loger::out($params);
        $res = \Prj\Bll\UserCoupon::getInstance()->getUserCoupon($params);
        \Prj\Bll\UserCoupon::getInstance()->formatForOldver($res);
        echo $this->outPut($res);
    }

    /**
     * Hand 获取我的未使用或已过期的券
     */
    public function myallcouponAction(){
        $params['userId'] = $this->getUidInSession();
        if(!$params['userId']){
            return $this->renderWithCodeMsg(10002 , '当前用户未登录或会话已超时');
        }
        $params['status'] = $this->_request->getParam('status');
        $params['page'] = $this->_request->getParam('page');
        $params['rows'] = $this->_request->getParam('rows');
        $params['type'] = 'old';
        \Prj\Loger::out($params);
        $res = \Prj\Bll\UserCoupon::getInstance()->getUserCoupon($params);
        \Prj\Bll\UserCoupon::getInstance()->formatForOldver($res , $params['type']);
        $res['rows'] = $res['data']['rows'];
        $res['total'] = $res['data']['total'];
        $res['data'] = [];
        echo $this->outPut($res);
    }

    /**
     * Hand app首页统计
     */
    public function getPlatformDataAction(){
        $res = \Prj\Bll\PlatformStatistics::getInstance()->getPlatformData();
        $this->outPut($res);
    }

    /**
     * Hand 获取我的未读信息+未使用的卡券
     */
    public function getMyDataAction(){
        $params['userId'] = $this->_request->getParam('userId');
        $res = \Prj\Bll\UserCoupon::getInstance()->getNotUsedCount($params);
        if(!\Lib\Misc\Result::check($res))return $this->outPut($res);
        $avaliableCoupon = $res['data']['total'];
        $res = \Prj\Bll\PlatformMail::getInstance()->getNoReadCountByUser($params);
        if(!\Lib\Misc\Result::check($res))return $this->outPut($res);
        $noReadMailNum = $res['data']['total'];
        return $this->outPut([
            'code' => 0,
            'data' => [
                'avaliableCoupon' => $avaliableCoupon,
                'noReadMailNum' => $noReadMailNum,
            ]
        ]);
    }

    /**
     * Hand banner接口数据
     */
    public function cmsHomeAction(){
        $params['channelOid'] = $this->_request->getParam('channelOid');
        //activity
        $activity = [];
        //banner
        $res = \Prj\Bll\Cms::getInstance()->getBannerList($params , 'formatBannerCms');
        if(!\Lib\Misc\Result::check($res))return $res;
        $banner = [
            'errorCode' => 0,
            'errorMessage' => '',
            'rows' => $res['data']['content'],
            'total' => $res['data']['total'],
            'pages' => 0,
        ];
        //infomation
        $res = \Prj\Bll\Cms::getInstance()->getInformationList($params , 'formatInformationCms');
        if(!\Lib\Misc\Result::check($res))return $res;
        $information = [
            'errorCode' => 0,
            'errorMessage' => '',
            'rows' => $res['data']['content'],
            'total' => $res['data']['total'],
            'pages' => 0,
        ];
        //notice
        $res = \Prj\Bll\Cms::getInstance()->getNoticeList($params , 'formatNoticeCms');
        if(!\Lib\Misc\Result::check($res))return $res;
        $notice = [
            'errorCode' => 0,
            'errorMessage' => '',
            'rows' => $res['data']['content'],
            'total' => $res['data']['total'],
            'pages' => 0,
        ];
        //actcarousel
        $actcarousel = [];
        $info = compact('activity' , 'banner' , 'information' , 'notice' , 'actcarousel');
        header('Content-type: application/json');
        echo json_encode([
            '_from' => 'php',
            'errorCode' => 0,
            'errorMessage' => '',
            'info' => $info,
        ] , 256);
        return 0;
    }

    /**
     * 输入格式形如 {'data':[],'message':'','code':10000}
     * Hand 将Result转成既定的格式输出
     * @param $res
     * @return mixed
     */
    protected function formatResult($res){
        $tmp = $res;
        $tmp['datas'] = $res['data'];
        if(isset($res['code'])){
            if($res['code'] == 10000){
                $tmp['errorCode'] = 0;
            }else{
                $tmp['errorCode'] = $res['code'];
            }
        }else{
            $tmp['errorCode'] = 99999;
        }
        $tmp['errorMessage'] = $res['message'] ? $res['message'] : '';
        $tmp['picResp'] = [
            'needUpdate' => false,
            'pics' => [],
        ];
        $tmp['respTime'] = floor(microtime(true) * 1000) ;
        $tmp['versions'] = [];
        $tmp['_from'] = 'php';
        unset($tmp['data']);
        unset($tmp['code']);
        unset($tmp['message']);
        return $tmp;
    }

    /**
     * 输入格式形如 {'data':[],'message':'','code':10000}
     * Hand 输出数组
     * @param $result
     */
    protected function outPut($result , $log = false){
        $arr = $this->formatResult($result);
        if($log)\Prj\Loger::outVal('outPut' , $arr);
        header('Content-type: application/json');
        echo json_encode($arr , 256);
    }

    /**
     * 获取我邀请的好友
     * @return int
     * @author lingtima@gmail.com
     */
    public function getMyUserRebate()
    {
        $userId = $this->_request->get('userId');
        if (empty($userId)) {
            $this->outPut(['code' => 19998, 'message' => '参数不正确']);
            return 0;
        }

        $pageNo = $this->_request->get('page', 1);
        $pageSize = $this->_request->get('rows', 10);

        $ModelUserRebateInfo = \Prj\Model\UserRebateInfo::getCopy('');
        $DB = $ModelUserRebateInfo->dbWithTablename();
        $count = $DB->getRecord($DB->kvobjTable(), 'count(*) as counts', ['referOid' => $userId]);
        $arrResponse = [
            'data' => [],
        ];
        if ($count) {
            $arrResponse['data']['total'] = $count['counts'];
            $arrResponse['data']['size'] = $pageSize;
            $arrResponse['data']['totalPages'] = ceil($count['counts'] / $pageSize);

            $list = $DB->getRecords($DB->kvobjTable(), 'id,lastRebate,lastRebateTime,totalAmount,totalWaitRebate,userMobile,userName,userOid',
                ['referOid' => $userId], 'rsort createTime', $pageSize, $pageNo - 1);
            if ($list) {
                $ret = [];
                foreach ($list as $v) {
                    $ret[] = [
                        'lastRebate' => $v['lastRebate'],
                        'lastRebateTime' => $v['lastRebateTime'],
                        'showName' => '',
                        'totalAmount' => $v['totalAmount'],
                        'totalWaitRebate' => $v['totalWaitRebate'],
                        'userMobile' => substr_replace($v['userMobile'], '****', 3, 4),
                        'userName' => $v['userName'] ? mb_substr($v['userName'], 0, 1) . str_repeat('*', mb_strlen($v['userName']) - 1) : '',
                        'userOid' => $v['userOid'],
                    ];
                }
                $arrResponse['data']['content'] = $ret;
            }
        } else {
            $arrResponse['data'] = [
                'total' => 0,
                'size' => $pageSize,
                'totalPages' => 0,
                'content' =>  [],
            ];
        }
        $arrResponse['code'] = 0;
        $arrResponse['message'] = '成功';
        $this->outPut($arrResponse);
        return 1;
    }

    /**
     * 获取我邀请的好友统计
     * @author lingtima@gmail.com
     */
    public function getMyUserRefereInfo()
    {
        $userId = $this->_request->get('userId');
        if (empty($userId)) {
            $this->outPut(['code' => 19998, 'message' => '参数不正确']);
            return 0;
        }
        $pageNo = $this->_request->get('page', 1);
        $pageSize = $this->_request->get('rows', 10);

        //接口返回结构
        $data = [
            'param' => [
                'investNum' => 0,
                'sceneId' => 0,
                'totalAmount' => 0,
                'totalReferNum' => 0,
                'totalWaitRebate' => 0,
            ],
            'page' => [
                'content' => [],
                'size' => $pageSize,
                'total' => 0,
                'totalPages' => 1,
            ],
        ];

        $ModelUserRefereFinal = \Prj\Model\UserRefereFinal::getCopy('');
        $userRefereFinal = $ModelUserRefereFinal->dbWithTablename()->getRecord($ModelUserRefereFinal->dbWithTablename()->kvobjTable(), '*', ['userOid' => $userId]);
        if ($userRefereFinal) {
            $data['param']['totalAmount'] = $userRefereFinal['totalAmount'];
            $data['param']['totalReferNum'] = $userRefereFinal['totalReferNum'];
            $data['param']['totalWaitRebate'] = $userRefereFinal['totalWaitRebate'];
        }

        $ModelUcUser = \Prj\Model\UcUser::getCopy($userId);
        $ModelUcUser->load();
        if ($ModelUcUser->exists()) {
            $data['param']['sceneId'] = $ModelUcUser->getField('sceneId');
        }

        $ModelRecommender = \Prj\Model\Recommender::getCopy('');
        $inviteUserList = $ModelRecommender->dbWithTablename()->getRecords($ModelRecommender->dbWithTablename()->kvobjTable(), 'userOid', ['recommendLoginName' => $userId]);
        if ($inviteUserList) {
            $userIds = [];
            foreach ($inviteUserList as $v) {
                $userIds[] = $v['userOid'];
            }
            $ModelUcUserFinal = \Prj\Model\UcUserFinal::getCopy('');
            $investNum = $ModelUcUserFinal->dbWithTablename()->getRecord($ModelUcUserFinal->dbWithTablename()->kvobjTable(), 'SUM(investTotalNum) as investTotalNums', ['inviteByUser' => $userIds]);
            if ($investNum) {
                $data['param']['investNum'] = $investNum['investTotalNums'];
            }

            //获取返利记录
            $ModelUserRebateDetail = \Prj\Model\UserRebateDetail::getCopy('');
            $rebateCount = $ModelUserRebateDetail->dbWithTablename()->getRecord($ModelUserRebateDetail->dbWithTablename()->kvobjTable(), 'count(*) as counts', ['referOid' => $userId]);
            if ($rebateCount) {
                $data['page'] = [
                    'size' => $pageSize,
                    'total' => $rebateCount['counts'],
                    'totalPages' => ceil($rebateCount['counts'] / $pageSize),
                ];
                $rebateList = $ModelUserRebateDetail->dbWithTablename()->getRecords($ModelUserRebateDetail->dbWithTablename()->kvobjTable(),
                    '*', ['referOid' => $userId], 'rsort createTime', $pageSize, $pageNo - 1);
                if ($rebateList) {
                    foreach ($rebateList as $v) {
                        $data['page']['content'][] = [
                            'type' => $v['type'],
                            'amount' => $v['amount'],
                            'status' => $v['status'],
                            'statusTime' => $v['statusTime'],
                            'createTime' => $v['createTime'],
                            'userName' => $v['userName'] ? mb_substr($v['userName'], 0, 1) . str_repeat('*', mb_strlen($v['userName']) - 1) : '',
                            'userOid' => $v['userOid'],
                            'userMobile' => substr_replace($v['userMobile'], '****', 3, 4),
                        ];
                    }
                }
            }
        }
        $this->outPut(['data' => $data, 'code' => 0, 'message' => '成功']);
        return 1;
    }

    /**
     * Hand 获取用户的返利详情
     * @return int
     */
    public function getMyUserRebateDetailAction(){
        $userId = $this->_request->getParam('userId');
        $friendId = $this->_request->getParam('friendId');
        $data = \Prj\Bll\Rebate::getInstance()->getMyUserRebateDetail([
            'referOid' => $userId,
            'userOid' => $friendId,
            'rows' => 10,
        ]);
        $this->outPut($data , true);
        return 1;
    }
}
