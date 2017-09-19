<?php
/**
 * 内部接口：
 * 配置文件 InnerSign里：innerip是ip白名单，keys是当前认可的key的列表
 *
 * @author simon.wang
 */
class ApiController extends \Prj\Framework\Ctrl{
    /**
     * @SWG\Post(
     *     path="/platform/api/donothing",
     *     tags={"Platform"},
     *     summary="空接口,用来获取扩展信息",
     *     @SWG\Parameter(name="extendInfo",description="扩展参数(数组)",type="string",in="formData"),
     *
     *
     * )
     */
    public function donothingAction(){
        $uid = $this->_request->get('uid');
        \Sooh2\Misc\Loger::getInstance()->app_trace($uid);
        if ($uid) {
            \Sooh2\Misc\Ini::getInstance()->setRuntime('userId', $uid);
        }
        return $this->assignCodeAndMessage();
    }

    /**
     * @SWG\Post(
     *     path="/platform/api/doevt",
     *     tags={"Platform"},
     *     summary="事件通知(立即执行)",
     *     @SWG\Parameter(name="evt",description="事件名称(例如 'JavaEvt\Test')",type="string",in="formData"),
     *     @SWG\Parameter(name="objid",description="关键ID",type="string",in="formData"),
     *     @SWG\Parameter(name="uid",description="用户ID",type="string",in="formData"),
     *     @SWG\Parameter(name="args",description="附加参数",type="string",in="formData"),
     *
     * )
     */
    public function doevtAction()
    {
        $o = new \Sooh2\EvtQue\EvtData();
        $o->evtId = $this->_request->get('evt');
        $o->objId = $this->_request->get('objid');
        $o->userId = $this->_request->get('uid');
        $o->args = $this->_request->get('args','');
        \Prj\Loger::setKv('objid' , $o->objId);
        if(is_array($o->args))$o->args = json_encode($o->args , 256);
        try{
            $o->evtId = str_replace('/' , '\\' , $o->evtId);
            $classname = '\\Prj\\Events\\'.$o->evtId;
            $e = $classname::getInstance();
            $e->init($o);
            $ret = $e->onEvt();
            if(false==\Sooh2\EvtQue\QueDataLog::createNew(-1 * \Lib\Misc\StringH::createOid(), array(
                    'evt'=>$o->evtId,'objid'=>$o->objId,'uid'=>$o->userId,'args'=>$o->args,'ret'=>$ret
                ))){
                \Sooh2\Misc\Loger::getInstance()->app_warning('事件('.$this->getEvtData()->toStringDetail().')处理结束:'.$ret.' 但记录日志失败');

                $this->returnOk('成功执行事件,但记录数据库失败');
            }else{
                $this->returnOk('成功执行事件');
            }

        } catch (\ErrorException $ex){
            $this->returnErr($ex->getMessage());
        }
    }
    /**
     * @SWG\Post(
     *     path="/platform/api/addevt",
     *     tags={"Platform"},
     *     summary="事件通知",
     *     @SWG\Parameter(name="evt",description="事件名称(例如 'JavaEvt\Test')",type="string",in="formData"),
     *     @SWG\Parameter(name="objid",description="关键ID",type="string",in="formData"),
     *     @SWG\Parameter(name="uid",description="用户ID",type="string",in="formData"),
     *     @SWG\Parameter(name="args",description="附加参数",type="string",in="formData"),
     *
     * )
     */
    public function addevtAction(){
        \Prj\Loger::addPrefix('addevt');
        $objid = $this->_request->get('objid');
        $uid = $this->_request->get('uid');
        $evt = $this->_request->get('evt');
        $args = $this->_request->get('args');
        $evt = str_replace('/' , '\\' , $evt);
        \Prj\Loger::setKv('objid' , $objid);
        if(is_array($args))$args = json_encode($args , 256);
        if(!empty($uid)){
            $u = \Prj\Model\User::getCopy($uid);
            $u->load();
            if(!$u->exists()){
                \Prj\Loger::out(__CLASS__.'->'.__FUNCTION__."(evt:$evt, uid:$uid  无效, objid:$objid, args:$args)");
                return $this->returnErr('指定uid无效', 19998);
            }
        }
        try{
            $autoid = \Sooh2\EvtQue\QueData::addOne($evt, $objid, $uid, $args);
            if($autoid){
                \Prj\Loger::out('成功添加事件');
                $this->returnOk('成功添加事件');
            }else{
                \Prj\Loger::out(__CLASS__.'->'.__FUNCTION__." 添加失败(evt:$evt, uid:$uid, objid:$objid, args:$args)");
                $this->returnErr('添加事件失败');
            }
        } catch (\ErrorException $e){
            \Prj\Loger::out(__CLASS__.'->'.__FUNCTION__." 添加失败(evt:$evt, uid:$uid, objid:$objid, args:$args):".$e->getMessage()."\n".$e->getTraceAsString());
            $this->returnErr('添加事件失败');
        }
    }

    /**
     * @SWG\Post(
     *     path="/platform/api/getvcode",
     *     tags={"Platform"},
     *     summary="获取验证码",
     *     @SWG\Parameter(name="uid",description="用户ID",type="string",in="formData"),
     *     @SWG\Parameter(name="smsType",description="短信类型",type="string",in="formData")
     * )
     */
    public function getvcodeAction()
    {
        $uid = $this->_request->get('uid');
        $smsType = $this->_request->get('smsType');

        if (empty($uid) || empty($smsType)) {
            return $this->returnErr('参数不正确');
        }

        $ModelUser = \Prj\Model\User::getCopy($uid);
        $ModelUser->load();
        if ($ModelUser->exists()) {
            $phone = $ModelUser->getField('userAcc');
            $vcode = \Prj\Redis\Vcode::fetchVCodeWithoutIp($phone, $smsType);
            $this->_view->assign('data', ['vcode' => !empty($vcode) ? $vcode : '']);
            return $this->returnOk();
        } else {
            return $this->returnErr('用户不存在');
        }
    }
    /**
     * 用于获取用户状态的空行为
     */
    public function donothing(){}
    ////////////////////////////////////////////////////////////////////////////////////
    protected function returnOk($msg=null)
    {
        $this->_view->assign('code',10000);
        if(!empty($msg)){
            $this->_view->assign('message',$msg);
        }
    }
    protected function returnErr($msg,$code=19998)
    {
        $this->_view->assign('code',$code);
        $this->_view->assign('message',$msg);
    }

    /**
     * 增加验签，如果不通过，设置错误码
     */
    protected function initPageFromRequest()
    {
        \Prj\Loger::setKv('Api');
        $ret = parent::initPageFromRequest();
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType(\Sooh2\Misc\ViewExt::type_json);
        $ip = \Sooh2\Util::remoteIP();
        $sign = $this->_request->get('sign');
        $dt = $this->_request->get('dt');
        if(\Lib\Misc\Sign::chkMd5ByDt($dt, $sign, $ip)){
            return $ret;
        }else{
            \Sooh2\Misc\Loger::getInstance()->app_warning("sign check failed dt=$dt sign=$sign ip=$ip");
            header('Content-type: application/json');
            echo '{"code": 19999,"message": "sign check failed"}';
            exit;
        }
    }

    /**
     * @SWG\Get(
     *     path="/platform/api/protocolList",
     *     tags={"Platform"},
     *     summary="协议列表",
     *     description="根据类型获取协议列表 ['register' =>'注册协议','recharge'=>"充值协议",'buy'=>'购买协议','fengxian'=>'风险提示书',]",
     *     @SWG\Parameter(name="sign",description="必填的标识[a11ae4a9c161506c602fede06260918d]",type="string",in="query",required=true,),
     *     @SWG\Parameter(name="dt",description="必填的标识[1501812584]",type="string",in="query",required=false,),
     *     @SWG\Parameter(name="code",description="协议类型",type="string",in="query",required=true,),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="data",
     *                 @SWG\Property(property="id", description="协议ID", type="string"),
     *                 @SWG\Property(property="type", description="协议类型", type="string"),
     *                 @SWG\Property(property="version", description="协议版本号", type="string"),
     *             ),
     *         )
     *     )
     * )
     */

    /**
     * @SWG\POST(
     *     path="/platform/api/protocolList",
     *     tags={"Platform"},
     *     summary="协议列表",
     *     description="根据类型获取协议列表 （'register' =>'注册协议','recharge'=>'充值协议','buy'=>'购买协议','fengxian'=>'风险提示书'）",
     *     @SWG\Parameter(name="sign",description="必要的认证信息(a11ae4a9c161506c602fede06260918d)",type="string",in="query"),
     *     @SWG\Parameter(name="dt",description="必要的认证信息(1501812584)",type="string",in="query"),
     *     @SWG\Parameter(name="code",description="协议类型",type="string",in="query"),
     * )
     */
    public function protocolListAction(){
        //协议类型
        $code = $this->_request->get('code');
        $where = array();
        if( !empty($code) ) {
            if( $code == 'dingxiang' ){
                $where = ['type' => ['regular_dingxiang','current_dingxiang']];
            }else{
                $where = ['type' => $code];
            }

        }
        $data = \Prj\Model\Protocol::getVersionList($where);
        if( empty($data) ){
            return $this->assignCodeAndMessage('暂无内容！' , 99999);
        }
        $protocolOptions = [
            'register'                  =>   "注册协议",
            'recharge'                  =>   "充值协议",
            'buy'                       =>   "购买协议",
            'fast'                      =>   "快捷支付服务协议",
            'regular_dingxiang'         =>   "定向委托投资协议-定期",
            'current_dingxiang'         =>   "定向委托投资协议-活期",
            'service'                   =>   "服务协议",
            'fengxian'                  =>   "风险提示书",

        ];
        foreach($data as $k=>$v){
            $data[$k]['verDesc'] = $v['version'].'-'.$protocolOptions[$v['type']];
        }
        return $this->assignRes([
            'code' => 10000,
            'data' => $data
        ]);
    }

    /**
     * 获取协议html  code='dingxiang'  orderNo='xxxxxxxxxxxxxxxx'
     */
    public function protocolDetailAction(){

        $code = $this->_request->get('code');
        $orderNo = $this->_request->get('orderNo');
        // 协议类型
        if( empty($code) || empty($orderNo) || empty($orderNo) ) {header('HTTP/1.1 404 Missing params');die();}
        $orderDetail = \Prj\Model\ZyBusiness\TradOrder::getRecord('userId,productId,holdStatus,orderAmount',['orderNo'=>$orderNo]);
        if( empty($orderDetail) ){
            header('HTTP/1.1 404 Not Found');die();
        }
        $product = \Prj\Model\Product::getRecord("weight,detailJson", ['productId'=>$orderDetail['productId']]);
        $productDetail = json_decode($product['detailJson'],true);
        // 产品类型 活期/定期
        if( $product['weight'] == 0 ){
            $productDetail['type'] = 'current_dingxiang';
        }else{
            $productDetail['type'] = 'regular_dingxiang';
        }
        //获取已替换内容的字段
        $replace = \Prj\Bll\Protocol::getInstance()->getProductReplaceField($productDetail);
        // 订单金额
        $replace['{orderAmount}'] = number_format($orderDetail['orderAmount'],2);
        $data = \Prj\Model\Protocol::getVersionDetail(['type'=>$productDetail['type']])['content'];
        if( empty($data) ){
            die('数据为空！');
        }
        //常规替换
        $data = str_replace(array_keys($replace), $replace, stripslashes($data));
        //自定义遍历替换
        foreach ($_GET as $k => $v) {
            $data = str_replace("{".$k."}",$v,$data);
        }
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        echo  $data;
    }



    /**
     * @SWG\Post(path="/Platform/Api/sendWarningLog", tags={"Platform"},
     *   summary="发送警报信息",
     *
     *      @SWG\Parameter(name="content", type="string", in="formData",
     *     description="警报内容"   ),
     *     @SWG\Parameter(name="type", type="string", in="formData",
     *     description="发生警报来源，字符串，多个来源平台用逗号隔开： (app和server)平台"   ),
     *     @SWG\Response(response=200, description="successful operation"),
     *     @SWG\Schema(type="object",
     *              @SWG\Property(property="code", description="状态码" , type="string"),
     *              @SWG\Property(property="message", description="返回信息", type="string")
     *             ),
     *
     *
     * )
     */
    public function sendWarningLogAction(){
        $deviceInfo = $this->_request->get("deviceInfo");
        $deviceInfo = json_encode($deviceInfo);
        $content = $this->_request->get("content");
        if(empty($deviceInfo)) return $this->assignCodeAndMessage("设备信息不能为空！",89999);
        if(empty($content)) return $this->assignCodeAndMessage("警报内容不能为空！",89999);
        $type = $this->_request->get("type");
        if(empty($type)) return $this->assignCodeAndMessage("来源类型不能为空",89999);
        $obj  = \Prj\Model\WarningLog::getCopy(null);
        $obj->setField("deviceInfo",$deviceInfo);
        $obj->setField("warningContent",$content);
        $obj->setField("source",$type);
        $obj->setField("status",0);
        $obj->setField("createTime",date("Y-m-d H:i:s"));
        try{
            $ret = $obj->saveToDB();
            if($ret) return $this->assignRes();
            return $this->assignCodeAndMessage("上传警报信息失败！");
        }catch (Exception $ex){
            return $this->assignCodeAndMessage("上传警报信息失败！".$ex->getMessage());
        }
    }

    /**
     * @SWG\Post(path="/platform/Api/checkRepeatUserPwd", tags={"Platform"},
     *      summary="检查支付密码和登录密码是否相同",
     *      @SWG\Parameter(name="uid", type="string", description="用户ID"   ),
     *      @SWG\Parameter(name="payPwd", type="string", description="支付密码"   ),
     *      @SWG\Response(response=200, description="successful operation",
     *          @SWG\Schema(type="object",
     *              @SWG\Property(property="code", description="状态码" , type="string"),
     *              @SWG\Property(property="message", description="返回信息", type="string"),
     *              @SWG\Property(property="data", type="object",
     *                  @SWG\Property(property="isRepeat", description="是否重复：1重复；0不重复", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function checkRepeatUserPwdAction()
    {
        $uid = $this->_request->get('uid');
        $payPwd = $this->_request->get('payPwd');
        if (empty($uid) || empty($payPwd)) {
            $this->_view->assign('data', ['isRepeat' => 1]);
            return $this->assignCodeAndMessage('请求信息有误', 99998);
        }
        $ModelUser = \Prj\Model\User::getCopy($uid);
        $ModelUser->load();
        if ($ModelUser->exists()) {
            try {
                $salt = $ModelUser->getField('salt');
                $userPwd = $ModelUser->getField('userPwd');
            } catch (\Exception $e) {
                $userPwd = $salt = '';
            }

            if (empty($userPwd) || $userPwd != \Prj\Bll\User::getInstance()->encryptPwd($payPwd, $salt)) {
                $this->_view->assign('data', ['isRepeat' => 0]);
                return $this->assignCodeAndMessage('OK');
            }
        }
        $this->_view->assign('data', ['isRepeat' => 1]);
        return $this->assignCodeAndMessage('OK');
    }
}
