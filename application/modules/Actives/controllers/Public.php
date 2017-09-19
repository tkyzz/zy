<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/14
 * Time: 13:26
 */


class PublicController extends \Prj\Framework\Ctrl {
    /**
     * 用于获取状态的空函数
     */
    public function donothing() {}
    /**
     * @SWG\Post(path="/actives/public/donothing", tags={"Actives"},
     * @SWG\Parameter(name="extendInfo",description="扩展参数",type="string",in="formData"),
     * summary="单独调用扩展参数",
     * description="",
     * )
     */
    public function donothingAction() {
        return $this->assignRes();
    }
    /**
     * @SWG\Post(path="/actives/public/newbieStepbonus", tags={"Actives"},
     *   summary="新手引导的状态查询",
     *   description="",
     * )
     */
    public function newbieStepbonusAction(){
        \Prj\Loger::reset();
        \Prj\Loger::$prefix .= '[newbieStepbonus]';
        $userId = $this->getUidInSession();
        \Prj\Loger::out('userId: ' . ($userId ? $userId : '未登录!'));
        $user = \Prj\Bll\User::getInstance($userId);
        //查询状态
        try{
            $stepDetail['register']['status'] = !empty($userId) ? 1 : 0;
            $stepDetail['bindcard']['status'] = $user->checkBind() ? 1 : 0;
            $stepDetail['charge']['status'] = $user->checkRecharge() ? 1 : 0;
            $stepDetail['buy']['status'] = $user->checkBuy() ? 1 : 0;
        }catch (\Exception $e){
            $this->assignCodeAndMessage($e->getMessage() , 99999);
            return;
        }
        \Prj\Loger::out('引导信息: register='.$stepDetail['register']['status'] .' bindcard='.$stepDetail['bindcard']['status'].
            ' charge='.$stepDetail['charge']['status'].' buy='.$stepDetail['buy']['status']);
        //奖励
        $coupon['type'] = 'coupon';
        $newbieStepbonus = \Prj\Bll\ActivityConfig::getInstance()->getActiveScheme('新手引导');
        $newbieList = \Rpt\Manage\ManageActivitySchemeConfig::getListByBASE64(bin2hex(json_encode($newbieStepbonus['id'])));
        foreach ($newbieList as $k=>$v){
            if($v['flag'] == 'signin_register_bonus') $stepDetail['register']['bonus'] = !empty($v['value'])?$coupon['type']."_".$v['value']:'';
            if($v['flag'] == 'signin_bindingCard_bonus') $stepDetail['bindcard']['bonus'] = !empty($v['value'])?$coupon['type']."_".$v['value']:'';
            if($v['flag'] == 'signin_recharge_bonus') $stepDetail['charge']['bonus'] = !empty($v['value'])?$coupon['type']."_".$v['value']:'';
            if($v['flag'] == 'signin_investment_bonus') $stepDetail['buy']['bonus'] = !empty($v['value'])?$coupon['type']."_".$v['value']:'';
        }


        //下一步的步骤
        switch (true){
            case $stepDetail['buy']['status'] : $NewbieStepNext = '';break;
            case $stepDetail['charge']['status'] : $NewbieStepNext = 'buy';break;
            case $stepDetail['bindcard']['status'] : $NewbieStepNext = 'charge';break;
            case $stepDetail['register']['status'] : $NewbieStepNext = 'bindcard';break;
            case !$stepDetail['register']['status'] : $NewbieStepNext = 'register';break;
            default : $NewbieStepNext = '';
        }

        //排序数据
        $sordData = [];
        $sordData['register'] = $stepDetail['register'];
        if($stepDetail['bindcard']['status'] == 0 && $stepDetail['charge']['status'] == 1){
            $sordData['charge'] = $stepDetail['charge'];
            $sordData['bindcard'] = $stepDetail['bindcard'];
        }else{
            $sordData['bindcard'] = $stepDetail['bindcard'];
            $sordData['charge'] = $stepDetail['charge'];
        }
        $sordData['buy'] = $stepDetail['buy'];

        //拼装数据
        foreach ($sordData as $k => $v){
            $tmp = $v;
            $tmp['step'] = $k;
            $NewbieStep[] = $tmp;
        }

        $data = compact('NewbieStep' , 'NewbieStepNext');

//        if(empty($userId)){
//            $data['floatnewbieicon'] = array('action'=>'default','icon'=>'','url'=>'');
//        }else{
            $acticityName = "活动图标";
            $activityList = \Prj\Bll\ActivityConfig::getInstance()->getActiveScheme($acticityName);
            $logo_list = \Rpt\Manage\ManageActivitySchemeConfig::getListByBASE64(bin2hex(json_encode($activityList['id'])));
            if($logo_list){
                foreach($logo_list as $k => $v){
                    if($v['flag'] =='signin_logo_change'){
                        if(trim($v['value'])){
                            $data['floatnewbieicon']['action'] = 'change';
                            continue;
            }else{
                $data['floatnewbieicon'] = array('action'=>'default','icon'=>'','url'=>'');
                            break;
            }
        }
                    if($v['flag'] == 'signin_logo_icon') $data['floatnewbieicon']['icon'] = "http://".$_SERVER['HTTP_HOST'].$v['value'];

                    if($v['flag'] == 'signin_logo_url') $data['floatnewbieicon']['url'] = $v['value'];
                }

            }else{
                $data['floatnewbieicon'] = array('action'=>'default','icon'=>'','url'=>'');
            }
//        }

        $this->assignCodeAndMessage('success');
        $this->_view->assign('data', $data);
    }

    protected function getBonus($eventId){
        $couponRet = \Prj\Bll\Event::getCouponInfoByEventId($eventId);
        if(!\Lib\Misc\Result::check($couponRet)){
            \Prj\Loger::out($couponRet['massage']);
            $bonus = '';
        }else{
            $coupon = $couponRet['data'];
            $bonus = !empty($coupon) ? ($coupon['type'] . '_' . $coupon['upperAmount']) : '';
        }
        return $bonus;
    }



    /**
     * @SWG\Post(
     *     path="/actives/public/home",
     *     tags={"Public"},
     *     summary="首页信息获取",
     *     description="首页一些信息接口",
     *     @SWG\Parameter(name="getBanner",description="是否获取banner",type="string",in="formData",enum={true,false}),
     *     @SWG\Parameter(name="getProductList",description="是否获取首页产品列表",type="string",in="formData",enum={true,false}),
     *     @SWG\Parameter(name="getAppIcon",description="是否获取app图标",type="string",in="formData",enum={true,false}),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="product",
     *                 @SWG\Property(property="collectedVolume", description="已募集金额", type="integer"),
     *                 @SWG\Property(property="durationPeriodDays", description="产品期限", type="integer"),
     *                 @SWG\Property(property="expAror", description="年化利率", type="string"),
     *                 @SWG\Property(property="investMin", description="起投金额", type="string"),
     *                 @SWG\Property(property="labelList", description="标签列表", type="string"),
     *                 @SWG\Property(property="name", description="产品名称", type="string"),
     *                 @SWG\Property(property="productOid", description="产品ID", type="string"),
     *                 @SWG\Property(property="raisedTotalNumber", description="募集总额", type="integer"),
     *                 @SWG\Property(property="rewardInterest", description="奖励年化利率", type="string"),
     *                 @SWG\Property(property="type", description="产品类型 PRODUCTTYPE_02=活期 PRODUCTTYPE_01=定期", type="string"),
     *                 @SWG\Property(property="state", description="产品状态 DURATIONING=立即抢购 RAISING=立即抢购 RAISEEND=已售罄", type="string")
     *             ),
     *              @SWG\Property(property="platformStatistics",
     *                 @SWG\Property(property="totalTradeAmount", description="累计投资金额", type="integer"),
     *                 @SWG\Property(property="repayedOrderNum", description="累计回款笔数", type="integer"),
     *
     *             ),
     *              @SWG\Property(property="icon",
     *                 @SWG\Property(property="icon", description="图标地址", type="string"),
     *                 @SWG\Property(property="iconTitle", description="图标标题", type="string"),
     *                 @SWG\Property(property="iconLink", description="链接类型，'0'=>'链接','1'=>'跳转(只适用app)'", type="string"),
     *                 @SWG\Property(property="iconPage", description="类型，H5=>H5,INVITATION=>邀请,HELP=>帮助中心,COUPON=>优惠券,CALENDAR=>回款日历,SINGIN=>每日签到", type="string"),
     *                 @SWG\Property(property="iconUrl", description="图标链接地址", type="string"),
     *             ),
     *         )
     *     )
     * )
     */
    public function homeAction(){
        $productChannel = \Prj\Model\ZyBusiness\PlatformChannel::getChannel();
        $cmsChannel=\Prj\Model\CmsChannel::getChannel();

        $getBanner=$this->_request->get('getBanner');
        $getProductList=$this->_request->get('getProductList');
        $contractId = $this->_request->get("contractId");
        if(!isset($contractId)) $contractId = $this->_request->get("channelId");

        $version = $this->_request->get("version");
        $productChannelOid = \Prj\Bll\Channel::getInstance()->getChannelId($contractId,$version);
        $channelInfo = \Prj\Bll\Product::getInstance()->getChannelInfoCopy($productChannelOid);
        $productChannelOid = $channelInfo['channelId'];
        $channelOid = \Prj\Bll\Channel::getInstance()->getBannerChannel($productChannelOid);
        \Prj\Loger::outVal("cmsChannel",$cmsChannel);
        \Prj\Loger::outVal("channelOid",$channelOid);
        $data=array();

        if($getBanner=='true'){
            if(!empty($channelOid)) {
                if(!array_key_exists ($channelOid,$cmsChannel)){
                    $data['banner']= [
                        'rows' => [],
                        'total' => 0,
                    ];
                    \Sooh2\Misc\Loger::getInstance()->app_warning('getBanner::channelOid错误');
                }else {
                    $banner = $this->getBanner($channelOid);
                    $data['banner']['rows'] = $banner;
                    $data['banner']['total'] = count($banner);
                }
            }else{
                $data['banner']= [
                    'rows' => [],
                    'total' => 0,
                ];
            }
        }else{
            $data['banner']= [
                'rows' => [],
                'total' => 0,
            ];
        }


        if($getProductList=='true'){
            if(!empty($productChannelOid)){
                if(!array_key_exists ($productChannelOid,$productChannel)){
                    $data['product']=[];
                    \Sooh2\Misc\Loger::getInstance()->app_warning('getProductList::productChannelOid错误');
                }else {
                    $data['product']['rows'] = $this->getIndexProductList($productChannelOid);
                }
            }else{
                $data['product']=[];
            }
        }
        $getAppIcon=$this->_request->get('getAppIcon');
        if($getAppIcon=='true'){
            $data['icon']=$this->getAppIcon();
        }

        $res = \Prj\Bll\PlatformStatistics::getInstance()->getPlatformStatistics();
        $data['platformStatistics'] = $res['data'] ?: [];

        $this->_view->assign('data' ,$data);
        return $this->assignCodeAndMessage('success');
    }






    protected function getIndexProductList($channelId=""){
        $param = [
            'channelOid'    =>  $channelId,
            'isNew'         =>  \Prj\Bll\Product::getInstance()->getUserTiro()
        ];
        $list = \Prj\Bll\Product::getInstance()->getIndexProductList($param);

        \Prj\Loger::outVal("list-->",$list);
        $data = array();
        foreach($list['data']['content'] as $k =>$v){
            $data['data']['content'][] = json_decode($v,true);
        }

        return !empty($data['data']['content'])?$data['data']['content']:[];

    }

//    public function getIndexProductList($channelId=""){
//        $param = [
//            'channelOid'    =>  $channelId,
//            'isNew'         =>  \Prj\Bll\Product::getInstance()->getUserTiro()
//        ];
//        $list = \Prj\Bll\Product::getInstance()->getIndexProductList2($param);
//        \Prj\Loger::outVal("list-->",$list);
//        $data = array();
//        foreach($list['data']['content'] as $k =>$v){
//            $data['data']['content'][] = json_decode($v,true);
//        }
//
//        return $data['data']['content'];
//
//    }



    /*获取app图标配置*/
    protected function getAppIcon(){
        $obj=\Prj\Model\AppIcon::getCopy();
        $field=array('icon1','icon1Title','icon1Link','icon1Url','icon1Page','icon2','icon2Title','icon2Link','icon2Url','icon2Page','icon3','icon3Title','icon3Link','icon3Url','icon3Page','icon4','icon4Title','icon4Link','icon4Url','icon4Page');
        list($db,$tb)=$obj->dbAndTbName();
        $arr=$db->getRecord($tb,$field,"","sort createTime");
        $data = [];
        for ($i = 1; $i <= 4 ; $i ++ ){
            $data[] = [
                'icon' => $arr['icon' . $i],
                'iconTitle' => $arr['icon'. $i .'Title'],
                'iconLink' => $arr['icon'. $i .'Link'],
                'iconPage' => $arr['icon'. $i .'Page'],
                'iconUrl' => $arr['icon'. $i .'Url'],
            ];
        }
        return $data;
    }


    /**
     *获取banner
     * @param string $channelOid
     * @return array $arr
     */
    protected function getBanner($channelOid){
        list($db,$tb)=\Prj\Model\Banner::getCopy(null)->dbAndTbName();
        $arr=$db->getRecords($tb,'oid,title,imageUrl,linkUrl,toPage,isLink',array('releaseStatus'=>'ok','*channelOid'=>"*$channelOid*"),'sort sorting rsort updateTime');
        return $arr;
    }

    /**
     * @SWG\Post(
     *     path="/actives/public/sendFeedBack",
     *     tags={"Public"},
     *     summary="发送反馈信息",
     *     description="发送反馈信息接口",
     *     @SWG\Parameter(name="content",description="反馈信息",type="string",in="formData"),
     * )
     */
    public function sendFeedBackAction(){
        $userId = $this->getUidInSession();
        if(empty($userId))return $this->assignCodeAndMessage('未登录或登录信息已经过期!' , 10001);
        $deviceInfo = $this->_request->get("deviceInfo");
        \Prj\Loger::outVal("deviceInfo",$deviceInfo);
        if(empty($deviceInfo)) return $this->assignCodeAndMessage("未传入设备信息！",10002);
        $platform = $this->_request->get("platform");

        $content = $this->_request->get("content");
        if(empty($content)) return $this->assignCodeAndMessage("反馈信息为空！",10003);
        $User = \Prj\Model\User::getCopy($userId);
        $User->load();

        $params = [
            'userId'    =>  $userId,
            'IDFA'      =>  isset($deviceInfo['IDFA'])?$deviceInfo['IDFA']:'',
            'IMEI'      =>  isset($deviceInfo['IMEI'])?$deviceInfo['IMEI']:'',
            'brand'     =>  $deviceInfo['brand'],
            'deviceName'    =>  isset($deviceInfo['deviceName'])?$deviceInfo['deviceName']:"",
            'phone'     =>  $User->getField("userAcc"),
            'platform'  =>  $platform,
            'statusCode'    =>  0,
            'updateTime'    =>  date("Y-m-d H:i:s"),
            'createTime'    =>  date("Y-m-d H:i:s"),
            'content'       =>  htmlentities($content)
        ];
        \Prj\Loger::outVal("parfss",$params);
        try{
            \Prj\Model\FeedBack::saveOne($params);
            return $this->assignCodeAndMessage("添加成功");
        }catch (Exception $ex){
            return $this->assignCodeAndMessage("添加失败！".$ex->getMessage(),10006);
        }





    }


    public function replaceSpecialChar($strParam){
        $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return preg_replace($regex,"",$strParam);
    }


    /**
     *获取用户登陆信息
     */
    protected function getUidInSession($userOid = null)
    {
        if(!empty($userOid))return $userOid;
        return \Prj\Session::getInstance()->getUid();
    }

    /**
     * @SWG\Post(
     *     path="/actives/public/iniStartUp",
     *     tags={"Public"},
     *     summary="开屏页配置",
     *     description="配合扩展参数 appSet",
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="data",
     *                 @SWG\Property(property="hasAD", description="是否开启", type="integer"),
     *                 @SWG\Property(property="img", description="图片", type="integer"),
     *                 @SWG\Property(property="url", description="跳转链接", type="string"),
     *                 @SWG\Property(property="duration", description="持续时间", type="string"),
     *                 @SWG\Property(property="refreshNotice", description="开屏页下拉刷新提示文字", type="string"),
     *             ),
     *         )
     *     )
     * )
     */
    public function iniStartUpAction(){
        $contractId = $this->_request->get("contractId");
        if(!isset($contractId)) $contractId = $this->_request->get("channelId");

        $version = $this->_request->get("version");
        $channelId = \Prj\Bll\Channel::getInstance()->getChannelId($contractId,$version);
        $params = [
            'channel'   =>  $channelId,
            'status'    =>  1
        ];
        $appAsset = \Prj\Model\AppAsset::getRecord("*",$params);
        if(empty($appAsset)){
            $params['channel'] = '*';
            $appAsset = \Prj\Model\AppAsset::getRecord("*",$params);
        }
        $appAssetConfig = json_decode($appAsset['config'],true);
        unset($appAssetConfig['showDemand']);unset($appAssetConfig['showBanner']);
        unset($appAssetConfig['showFixed']);unset($appAssetConfig['showNewbie']);
        unset($appAssetConfig['style']);
        $res = [
            'code'  =>  10000,
            'data'  =>  $appAssetConfig
        ];
        $this->assignRes($res);

    }

    /** 电子签章 */
    public function agreementAction(){
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        $userId = $this->getUidInSession();
        if(empty($userId))return $this->output('未登录或登录信息已经过期!' , 10001);
        $dir = '/opt/mimosa_file';
        $ip = \Sooh2\Misc\Ini::getInstance()->getIni('application.serverip.agreement');
        $orderId = $this->_request->get('orderId');
        if(empty($orderId))return $this->output('参数错误#orderId' , 99999);
//        $investor = \Prj\Model\MimosaUser::getUserByUcUserId($userId);
//        if(empty($investor))return $this->output('用户不存在' , 99999);
//        $investorId = $investor['oid'];
        $dbName = \Prj\Model\ZyBusiness\TradOrder::getDbname();
        $orderNo = \Prj\Model\ZyBusiness\TradOrder::getRecord("orderNo",['orderId'=>$orderId])['orderNo'];
        $sql = <<<sql
select * from {$dbName}.tpf_compact_info where orderNo  = '$orderNo' and investorOid = '$userId' limit 1
sql;
        $info = \Prj\Model\User::query($sql)[0];
        if(empty($info))return $this->output('协议信息查询失败' , 99999);
        if($info['status'] != 'signOK')return $this->output('该协议尚未签章，请等待产品成立' . 99999);
        if(!empty($info['signUrl'])){
            $url = 'http://' . $ip ."/agreements". $info['signUrl'];
        }else{
            $url = 'http://' . $ip ."/agreements". $info['pdfUrl'];
        }

        // $curl = \Sooh2\Curl::factory();
        if(!$data = file_get_contents($url)){
            $url = 'http://' . $ip ."/agreements". $info['pdfUrl'];
            $data= file_get_contents($url);
        }

        header("Content-Type: application/pdf;charset=UTF-8");
        echo $data;
    }

    protected function output($msg , $code = 10000){
        echo $msg;
    }

    public function jsapiTicketAction(){
        $appID = \Sooh2\Misc\Ini::getInstance()->getIni('application.weixin.appID');
        $appsecret = \Sooh2\Misc\Ini::getInstance()->getIni('application.weixin.appsecret');
        $data = (new \Lib\Wx\Jssdk($appID , $appsecret))->getSignPackage();
        $this->_view->assign('data' , $data);
        $this->assignCodeAndMessage();
    }
    /**
     * @SWG\Post(
     *     path="/actives/public/tdInfoFromTd",
     *     tags={"TD"},
     *     summary="TD上报用户设备信息",
     *     description="",
     *     @SWG\Parameter(name="appkey",description="appkey",type="string",in="formData"),
     * )
     */
    public function tdInfoFromTdAction(){
        $map = [
            'appkey' , 'activietime' , 'osversion' , 'devicetype' , 'idfa' , 'tdid' , 'activieip' , 'spreadurl' , 'spreadname' ,
            'ua' , 'clickip' , 'clicktime' , 'appstoreid' , 'adnetname' , 'channelpackage' , 'other'
        ];
        $params = [];
        foreach ($map as $v){
            $params[$v] = $this->_request->get($v);
        }

        $res = \Prj\Bll\Td::getInstance()->infoFromTd($params);
        return $this->assignRes($res);
    }

//    /**
//     * @SWG\Post(
//     *     path="/actives/public/tdInfoFromApp",
//     *     tags={"TD"},
//     *     summary="客户端TD上报",
//     *     description="",
//     *     @SWG\Parameter(name="type",description="类型",type="string",in="formData"),
//     *     @SWG\Parameter(name="content",description="内容",type="string",in="formData"),
//     * )
//     */
//    public function tdInfoFromAppAction(){
//        $userId = $this->getUidInSession();
//        if(empty($userId))return $this->assignCodeAndMessage('' , 10001);
//
//        $params = [
//            'userId' => $userId,
//            'type' => $this->_request->get('type' , 'register'),
//            'content' => $this->_request->get('content'),
//        ];
//
//        $res = \Prj\Bll\Td::getInstance()->infoFromApp($params);
//        return $this->assignRes($res);
//    }
}