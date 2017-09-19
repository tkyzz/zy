<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/11
 * Time: 11:49
 */
class UserBasicInfoController extends \Rpt\Manage\ManageIniCtrl
{
    public $productStatus = [
        'RAISING'   =>  "募集中",
        'NOTSTARTRAISE' =>  "待售",
        'RAISEEND'      =>  "募集完成",
        'DURATIONING'   =>  "存续期",
        'CLEARED'       =>  "已结清"
    ];

    public $bankOrderStatus = ['INIT'=>"已申请",'APPLIED'=>"已申请",'REJECTED'=>'已驳回','PROCESSING'=>"处理中",'FAILED'=>"交易失败","SUCCESS"=>"交易成功"];
    public function indexAction(){
        $form = $this->searchForm();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('手机号','phone',250,'')
            ->addHeader('姓名','realname',100,'')
            ->addHeader('注册时间','ymdReg',150,'')
            ->addHeader('首投时间','orderTime',150,'')
            ->addHeader('账户余额','cashBalance',100)
            ->addHeader('在投金额','asset',100)
            ->addHeader('邀请人','inviter',100)
            ->addHeader('邀请好友数','inviteNum',100)
            ->addHeader('邀请好友投资数','rebateNum',100)
            ->addHeader("冻结状态",'freeze','100')
            ->addHeader('操作','op',500,'')
            ->initJsonDataUrl($this->urlForActivityCoupon($form));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('用户基本信息')
            ->initForm($form)->initDatagrid($table);
        $this->renderPage($page,true);
    }

    protected function searchForm()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form= new \Sooh2\BJUI\Forms\Search($uri->uri(),'post','listData');

        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('eq_phone', '', '手机号'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_nickname",'',"姓名"))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("eq_ymdReg",'','注册时间'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_idCard","","身份证"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_bankPhone","","银行预留手机号"))
            ->appendHiddenFirst("inviter","")->appendHiddenFirst("rebateNum",'')->appendHiddenFirst('uid','');

        $form->isUserRequest($this->_request);
        return $form;
    }


    protected function urlForActivityCoupon($form,$act = 'listData')
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $where = $form->getWhere();
        $inviter = $this->_request->get("inviter");
        $rebateId = $this->_request->get("rebateNum");
        $uid = $this->_request->get("uid");

        if(empty($where)){
            if(!empty($inviter)) $where['inviter'] = $inviter;
            if(!empty($rebateId)) $where['rebateId'] = $rebateId;
            if(!empty($uid)) $where['uid'] = $uid;
            if(!empty($where)) {
                return $uri->uri(array('__wHeRe__'=> bin2hex(json_encode($where))),$act);
            }else{
                return $uri->uri(null,$act);
            }


        }else{
            return $uri->uri(array('__wHeRe__'=> bin2hex(json_encode($where))),$act);
        }

    }
    public function listDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);

        $where = array();
        if(!empty($getWhere)){
            if(!empty($getWhere['bankPhone'])){
                $userInfo = \Prj\Model\Payment\BankBind::getRecords("userId",['bankPhone'=>$getWhere['bankPhone'],'status'=>"BIND"]);
                if(!empty($userInfo)) {
                    $where['uid'] = array_column($userInfo,'userId');
                }else{
                    $where['uid'] = '';
                }
            }
            if(!empty($getWhere['uid'])) $where['uid'] = $getWhere['uid'];
            if(!empty($getWhere['phone'])) $where['*phone'] = "%".$getWhere['phone']."%";
            if(!empty($getWhere['nickname'])) $where['*nickName'] = $getWhere['nickname'];
            if(!empty($getWhere['ymdReg'])) $where['ymdReg'] = date('Ymd',strtotime($getWhere['ymdReg']));
            if(!empty($getWhere['idCard'])) $where['idCard'] = $getWhere['idCard'];
            if(!empty($getWhere['inviter'])) $where['inviter'] = $getWhere['inviter'];
            if(!empty($getWhere['rebateId'])){
                $inviteInvest = \Prj\Model\InviteFinal::getRecords("uid",['formUid'=>$getWhere['rebateId'],'!uid'=>$getWhere['rebateId'],'lastStatus'=>1]);

                if(!empty($inviteInvest)) {
                    $where['uid'] = array_column($inviteInvest,'uid');
                }else{
                    return $this->renderArray([]);
                }

            }
        }
        $tbName = \Prj\Model\UserFinal::getTbname();
        $cmd = new \Sooh2\DB\Myisam\Cmd();
        $buildWhere ="";
        $assetTbName = \Prj\Model\Payment\InvestorAssetTotal::getTbname();
        if(!empty($where)) $buildWhere = $cmd->buildWhere($where);
        $sql = "select a.*,( select count(*) from ".$tbName." b where b.inviter=a.uid) as inviteNum, (select d.regularAsset+d.currentAsset from ".$assetTbName." d where d.userId=a.uid) asset, 
        (select d.availableBalance from ".$assetTbName." d where d.userId=a.uid) cashBalance
          from ".$tbName." a ".$buildWhere." limit 1000";
        \Prj\Loger::outVal("sql-->",$sql);
//        $list = \Prj\Model\UserFinal::getRecords("*,(select count(*) from )",$where);
        $list = \Prj\Model\UserFinal::query($sql);
        $uri = \Sooh2\Misc\Uri::getInstance();
        foreach($list as $k => $v){
            $list[$k]['freeze'] = $v['freeze']?"已冻结":"未冻结";
            $list[$k]['asset'] = $v['asset']?number_format($v['asset'],2):0;
            $list[$k]['cashBalance'] = $v['cashBalance']?number_format($v['cashBalance'],2):'0.00';
            $list[$k]['inviter'] = !empty($v['inviter'])?"<button data-title='".$v['inviter']."' class='invite btn-green' >".$this->getUserName($v['inviter'])."</a>":'';
            $list[$k]['inviteNum'] = $v['inviteNum']?"<button data-title='".$v['uid']."' class='inviteNum btn-green'>".$v['inviteNum']."</button>":0;
            $list[$k]['rebateNum'] = $v['rebateNum']?"<button  data-title='".$v['uid']."' class='rebateNum btn-green'>".$v['rebateNum']."</button>":0;
            $list[$k]['op'] = $this->btnLookAtDatagrid($v['uid'])."|".$this->btnInvestRecordDatagrid($v['uid'])
                ."|".$this->btnChargeRecordDatagrid($v['uid'])."|".$this->btnWithDrawRecordDatagrid($v['uid'])."|".$this->btnCouponRecordDatagrid($v['uid']);
        }
        $this->renderArray($list);

    }


    public function indexDataAction(){
        $uid = $this->_request->get("uid");

        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('手机号','phone',250,'')
            ->addHeader('姓名','nickname',100,'')
            ->addHeader('注册时间','ymdReg',150,'')
            ->addHeader('首投时间','orderTime',150,'')
            ->addHeader('账户余额','wallet',250)
            ->addHeader('在投金额','investWayAmount',100)
            ->addHeader('邀请人','inviter',100)
            ->addHeader('邀请好友数','inviteNum',100)
            ->addHeader('邀请好友投资数','rebateNum',100)
            ->addHeader('操作','op',500,'')
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(['__wHeRe__'=>bin2hex(json_encode(['uid'=>$uid]))],'listData'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->title("用户基本信息")->initDatagrid($table);
        $this->renderPage($page);
    }

    public function decodePkey($strpkey)
    {
        return json_decode(hex2bin($strpkey),true);
    }

    protected function btnLookAtDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'basicInfo');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'基本信息\', mask:true,width:800, height:500}">基本信息</a>&nbsp;';
    }



    /*投资记录按钮*/
    protected function btnInvestRecordDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'investRecord');
        return  '<a href="'.$url.'" data-toggle="navtab" data-options="{id:\'delUpd\', title:\'投资记录\', mask:true,width:800, height:500}">投资记录</a>&nbsp;';
    }

    /**/
    protected function btnChargeRecordDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'ChargeRecord');
        return  '<a href="'.$url.'" data-toggle="navtab" data-options="{id:\'delUpd\', title:\'充值明细\'}">充值明细</a>&nbsp;';
    }

    protected function btnWithDrawRecordDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'WithdrawRecord');
        return  '<a href="'.$url.'" data-toggle="navtab" data-options="{id:\'delUpd\', title:\'提现记录\', mask:true,width:800, height:500}">提现记录</a>&nbsp;';
    }


    protected function btnCouponRecordDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'CouponRecord');
        return  '<a href="'.$url.'" data-toggle="navtab" data-options="{id:\'delUpd\', title:\'优惠券记录\', mask:true,width:800, height:500}">优惠券记录</a>&nbsp;';
    }



    public function getUserName($uid){
        if(empty($uid)) return '';

        $obj = \Prj\Model\UserFinal::getCopy($uid);
        $obj->load();
        $name = $obj->getField("realname");

        return $name;
    }

    public function getUserPhone($uid){
        if(empty($uid)) return '';

        $obj = \Prj\Model\UserFinal::getCopy($uid);
        $obj->load();
        $phone = $obj->getField("phone");

        return $phone;
    }


    protected function btnUnBind($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=>\Rpt\KVObjBase::base64EncodePkey($pkey)),'unBind');
        return '<a href="'.'" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'你确定要解绑銀行卡'.str_replace('"','',\Sooh2\Util::toJsonSimple($pkey)).'！\', okCall:function(){mydelcmd(\''.$url .'\');}}">解绑銀行卡</a>&nbsp;';
    }


    public function unBindAction(){
        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);

        $this->addLog($pkey,\Prj\Model\UserFinal::getClassName());
        $ret = \Lib\Services\Unbind::getInstance()->sendUnBind($pkey);
        \Prj\Loger::outVal("ret",$ret);
        if(!\Lib\Misc\Result::check($ret)){
            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, $ret['message']);
        }else{
            return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '修改成功', true);
        }
    }

    protected function addLog($content,$objtable){
        $obj = \Prj\Model\UserFinal::getCopy(null);
        $db = $obj->dbWithTablename();
        $fields = array(
            '`ymd`'=>date('Ymd'),'his'=>date('His'),'managerid'=> \Rpt\Session\Broker::getManagerId(),
            'objtable'=>$objtable,'chgcontent'=>$content,'rowVersion'=>1
        );

        try{

            $ret = $db->addRecord(\Prj\Model\UserFinal::getDbname().".tb_manage_log",$fields);

            return $ret;
        } catch (\ErrorException $ex){
            \Sooh2\Misc\Loger::getInstance()->app_warning('记录管理员操作记录失败：'.\Sooh2\Util::toJsonSimple($fields));
        }

    }

    public function getInviteCount($uid){
        if(empty($uid)) return 0;
        $count = \Prj\Model\UserFinal::getRecord("count(*) as total",['inviter'=>$uid]);
        if(empty($count)){
            return 0;
        }else{
            return $count['total'];
        }
    }


    /**/
    public function getInviterInfoAction(){
        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);
        $obj = \Prj\Model\UserFinal::getCopy($pkey);
        $obj->load();
        $page = \Prj\View\Bjui\Detail::getInstance();
        $page->setData("手机号：",$obj->getField("phone"))->setData("姓名：",$obj->getField("nickname"))
            ->setData("注册时间：",$obj->getField("ymdReg"))->setData("首投时间：",$obj->getField("orderTime"))
            ->setData("账户余额:",$obj->getField("wallet"))->setData("在投金额:",$obj->getField("investWayAmount"))
            ->setData("邀请人:",$this->getUserName($obj->getField("inviter")))->setData("邀请好友数",$this->getInviteCount($obj->getField("inviter")))
            ->setData("邀请好友投资数：",$obj->getField("rebateNum"));
        $this->renderPage($page);
    }

    /*用户基础信息*/
    public function basicInfoAction(){
        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);
        $obj = \Prj\Model\UserFinal::getCopy($pkey);
        $obj->load();
        if(!$obj->exists()) return [];
        $bankObj = \Prj\Model\Payment\BankInfo::getRecord("*",['bankCode'=>$obj->getField("bankCardCode")]);
        $bankInfo = \Prj\Model\Payment\BankBind::getRecord("*",['userId'=>$obj->getField("uid"),'status'=>"BIND"]);
        $page = \Prj\View\Bjui\Detail2::getInstance();
        $channelName = '';
        $basicTitle = "基本信息&nbsp&nbsp&nbsp";
        if($obj->getField("freeze")){
            $basicTitle .= $this->btnUnfreeze(['userId'=>$pkey]);
        }else{
            $basicTitle .= $this->btnfreeze(['userId'=>$pkey]);
        }

        $bindCardId = $obj->getField("isBindCard")?$obj->getField("bindCardId"):'';
        $page->setData("姓名：",$obj->getField("nickname"),2,$basicTitle)->setData("身份证号：",$obj->getField("certNo"))
            ->setData("注册时间：",date("Y.m.d",strtotime($obj->getField("ymdReg",2))))->setData("注册手机号：",$obj->getField("phone"))
            ->setData("渠道名：",$channelName)->setData("渠道ID：",$obj->getField("contractId"))
            ->setData("绑定银行：",$bankObj['bankName'],2,'银行卡信息')->setData("银行预留手机号：",$bankInfo['bankPhone'])
            ->setData("绑卡时间：",$bankInfo['createTime'])->setData("银行卡号：",$bindCardId."&nbsp&nbsp&nbsp".$this->btnUnBind(['userId'=>$obj->getField("uid")]));
        $this->renderPage($page);
    }


    protected function btnfreeze($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=>\Rpt\KVObjBase::base64EncodePkey($pkey)),'freeze');
        return '<a class="btn btn-green" href="'.'" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'你确定要冻结账户吗？'.str_replace('"','',\Sooh2\Util::toJsonSimple($pkey)).'！\', okCall:function(){mydelcmd(\''.$url .'\');}}">冻结账户</a>&nbsp;';
    }



    protected function btnUnfreeze($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=>\Rpt\KVObjBase::base64EncodePkey($pkey)),'unfreeze');
        return '<a href="'.'" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'你确定要解冻账户吗？'.str_replace('"','',\Sooh2\Util::toJsonSimple($pkey)).'！\', okCall:function(){mydelcmd(\''.$url .'\');}}">解冻账户</a>&nbsp;';
    }


    /*冻结*/
    public function freezeAction(){
        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);

        $this->addLog($pkey);

        $ret = \Lib\Services\Freeze::getInstance()->sendFreeze($pkey);
        \Prj\Loger::outVal("ret",$ret);
        if(!\Lib\Misc\Result::check($ret)){
            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, $ret['message']);
        }else{
            return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '冻结成功', true);
        }
    }


    public function unfreezeAction(){
        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);

        $this->addLog($pkey);
        $ret = \Lib\Services\Freeze::getInstance()->sendUnfreeze($pkey);
        \Prj\Loger::outVal("ret",$ret);
        if(!\Lib\Misc\Result::check($ret)){
            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, $ret['message']);
        }else{
            return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '解冻成功', true);
        }
    }


    public function investRecordAction(){

        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('订单ID','orderId',250,'')
            ->addHeader('产品名称','productName',100,'')
            ->addHeader('产品状态','productStatus',150,'')
            ->addHeader('期限(天)','durationPeriodDays',150,'')
            ->addHeader('预期年化收益(%)','rate',250)
            ->addHeader('投资时间','createTime',100)
            ->addHeader('还款日期(预计)','closedTime',100)
            ->addHeader('投资金额','orderAmount',100)
            ->addHeader('使用优惠券类型','couponType',100)
            ->addHeader('使用优惠券名称','couponName',100)
            ->addHeader('使用优惠券面额','couponAmount',100)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(['__wHeRe__'=>$pkeyParams],'listInvestRecord'));
        $userStatics = \Prj\Model\Payment\InvestorAssetTotal::getRecord("*",['userId'=>$pkey]);
        $title = "投资记录 累计投资金额：".number_format($userStatics['totalAsset'],2).";在投金额：".number_format($userStatics['regularAsset']+$userStatics['currentAsset'],2)."元";
        $title .=";定期在投金额：".number_format($userStatics['regularAsset'],2)."元; 活期在投金额：".number_format($userStatics['currentAsset'],2)."元";
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init($title)->initDatagrid($table);
        $this->renderPage($page);
    }


    public function listInvestRecordAction(){
        $couponType = ['COUPON'=>"优惠券","REATECOUPON"=>"加息券"];
        $pkeyParams = $this->_request->get("__wHeRe__");
        $pkey = $this->decodePkey($pkeyParams);
        $traderOrder = \Prj\Model\ZyBusiness\TradOrder::getRecords("*",['userId'=>$pkey,'orderType'=>"INVEST"],'rsort createTime');
        foreach ($traderOrder as $k =>$v){
            $product = \Prj\Model\ZyBusiness\ProductInfo::getRecord("*",['productId'=>$v['productId']]);
            $coupon = '';
            if(!empty($v['userCouponId'])){
                $coupon = \Prj\Model\ZyBusiness\UserCoupon::getRecord("*",['ucId'=>$v['userCouponId']]);
            }
            $traderOrder[$k]['productName'] = $product['productName'];
            $traderOrder[$k]['productStatus'] = array_key_exists($product['productStatus'],$this->productStatus)?$this->productStatus[$product['productStatus']]:"已售罄";
            $traderOrder[$k]['durationPeriodDays'] =$product['durationPeriodDays'];
            $traderOrder[$k]['rate'] = $product['baseRate']+$product['rewardRate'];
            $traderOrder[$k]['createTime'] = $v['createTime'];
            $traderOrder[$k]['closedTime'] = !empty($v['closedTime'])?$v['closedTime']:\Prj\Model\ZyBusiness\InvestorTradeOrderDetail::getRecord('redeemExpPayDate',['orderDetailId'=>$v['orderDetailId']])['redeemExpPayDate'];
            $traderOrder[$k]['orderAmount'] = number_format($v['orderAmount'],2);
            $traderOrder[$k]['couponType'] = isset($v['couponType'])?$couponType[strtoupper($v['couponType'])]:'';
            $traderOrder[$k]['couponName'] = isset($coupon['name'])?$coupon['name']:'';
            $traderOrder[$k]['couponAmount'] = isset($coupon['couponAmount'])?number_format($coupon['couponAmount'],2):'0.00';
        }
        $this->renderArray($traderOrder);
    }



    public function ChargeRecordAction(){
        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);
        $userFinal = \Prj\Model\UserFinal::getCopy($pkey);
        $userFinal->load();
        $count = \Prj\Model\Payment\BankOrder::getRecord("sum(orderAmount)as totalMoney",['userId'=>$pkey,'orderType'=>\Prj\Model\Payment\BankOrder::$type_recharge,'orderStatus'=>'SUCCESS']);
        $titlename = "充值记录   累计充值金额：".floatval($count['totalMoney']);
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('交易流水号','orderNo',250,'')
            ->addHeader("充值渠道",'tradeType',250,'')
            ->addHeader('申请时间','createTime',100,'')
            ->addHeader('到账时间','completeTime',150,'')
            ->addHeader('交易状态','orderStatus',150,'')
            ->addHeader('充值金额','orderAmount',250)
            ->addHeader('在投金额','investWayAmount',100)
            ->addHeader('充值金额','orderAmount',100)
            ->addHeader('失败原因','returnMsg',100)
            ->addHeader('错误代码','returnCode',100)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(['__wHeRe__'=>$pkeyParams],'listDataChargeRecord'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init($titlename)->initDatagrid($table);
        $this->renderPage($page);
    }


    public function listDataChargeRecordAction(){
        $pkeyParams = $this->_request->get("__wHeRe__");
        $pkey = $this->decodePkey($pkeyParams);
        $chargeChannel = ['PAY'=>"代付",'PAYEE'=>"实名支付",'GATEWAY_PAYEE'=>"网关支付"];
        $bankOrder = \Prj\Model\Payment\BankOrder::getRecords("*",['userId'=>$pkey,'orderType'=>\Prj\Model\Payment\BankOrder::$type_recharge]);
        foreach ($bankOrder as $b=>$order){
            $bankOrder[$b]['orderStatus'] = $this->bankOrderStatus[$order['orderStatus']];
            $bankOrder[$b]['tradeType'] = $chargeChannel[$order['tradeType']];
        }
        $this->renderArray($bankOrder);
    }

    /*提现*/
    public function WithdrawRecordAction(){
        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);
        $count = \Prj\Model\Payment\BankOrder::getRecord("sum(orderAmount)as totalMoney",['userId'=>$pkey,'orderType'=>\Prj\Model\Payment\BankOrder::$type_withdraw,'orderStatus'=>'SUCCESS']);

        $title = "提现记录 累计提现金额：".number_format($count['totalMoney'],4);
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('交易流水号','orderNo',250,'')
            ->addHeader('申请时间','createTime',100,'')
            ->addHeader('到账时间','completeTime',150,'')
            ->addHeader('交易状态','orderStatus',150,'')
            ->addHeader('申请提现金额','orderAmount',250)
            ->addHeader('实际到账金额','investWayAmount',100)
            ->addHeader('提现手续费','orderAmount',100)
            ->addHeader('失败原因','returnMsg',100)
            ->addHeader('错误代码','returnCode',100)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(['__wHeRe__'=>$pkeyParams],'listDataWithDrawRecord'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init($title)->initDatagrid($table);
        $this->renderPage($page);

    }


    public function listDataWithDrawRecordAction(){
        $pkeyParams = $this->_request->get("__wHeRe__");
        $pkey = $this->decodePkey($pkeyParams);
        $bankOrder = \Prj\Model\Payment\BankOrder::getRecords("*",['userId'=>$pkey,'orderType'=>\Prj\Model\Payment\BankOrder::$type_withdraw]);
        foreach ($bankOrder as $k => $v){
            $bankOrder[$k]['orderStatus'] = $this->bankOrderStatus[$v['orderStatus']];
            $bankOrder[$k]['investWayAmount'] = number_format(($v['orderAmount']-$v['fee']),2);
            $bankOrder[$k]['orderAmount'] = number_format($v['orderAmount'],2);
        }
        $this->renderArray($bankOrder);
    }



    public function CouponRecordAction(){
        $pkeyParams = $this->_request->get("__pkey__");
        $pkey = $this->decodePkey($pkeyParams);

        $total = \Prj\Model\ZyBusiness\UserCoupon::getRecord("count(*) as total",['userId'=>$pkey])['total'];
        $UsedCount = \Prj\Model\ZyBusiness\UserCoupon::getRecord("count(*) as total",['userId'=>$pkey,'couponStatus'=>"USED"])['total'];
        $expiredCount = \Prj\Model\ZyBusiness\UserCoupon::getRecord("count(*) as total",['userId'=>$pkey,'couponStatus'=>"EXPIRED"])['total'];
        $title = "优惠券记录 共".$total."条记录,未过期".($total-$expiredCount)."条,已使用".$UsedCount."条,已过期".$expiredCount."条";
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('优惠券ID','couponId',250,'')
            ->addHeader('优惠券状态','couponStatus',100,'')
            ->addHeader('优惠券类型','couponType',150,'')
            ->addHeader('优惠券名称','name',150,'')
            ->addHeader('优惠券面额','couponAmount',250)
            ->addHeader('起头金额(元)','limitInvestAmount',100)
            ->addHeader('限制使用产品类型','limitLabels',100)
            ->addHeader('发放时间','lenderTime',100)
            ->addHeader('到期时间','expireTime',100)
            ->addHeader("操作",'op',100)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(['__wHeRe__'=>$pkeyParams],'listDataCouponRecord'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init($title)->initDatagrid($table);
        $this->renderPage($page);
    }


    public function listDataCouponRecordAction(){
        $couponType = ['REDPACKETS'=>"红包",'COUPON'=>"优惠券","RATECOUPON"=>"加息券"];
        $couponStatus = ["NOTUSED"=>"未使用","LOCKED"=>"已锁定","EXPIRED"=>"已过期","USED"=>"已使用"];

        $pkeyParams = $this->_request->get("__wHeRe__");
        $pkey = $this->decodePkey($pkeyParams);
        $coupon = \Prj\Model\ZyBusiness\UserCoupon::getRecords("*",['userId'=>$pkey],"rsort updateTime");
        foreach ($coupon as $k => $v){
            $coupon[$k]['couponStatus'] = $couponStatus[$v['couponStatus']];
            $coupon[$k]['couponType'] = $couponType[$v['couponType']];
            $coupon[$k]['limitInvestAmount'] = number_format($v['limitInvestAmount'],2);
            $coupon[$k]['couponAmount'] = number_format($v['couponAmount'],2);
            if($v['couponStatus'] == 'NOTUSED') {

                $coupon[$k]['op'] = $this->btnDelay(['ucId'=>$v['ucId'],'userId'=>$v['userId']]);
            }
        }
        $this->renderArray($coupon);

    }


    protected function btnDelay($pkey){
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'delay');
        return  '<a href="'.$url.'" class="btn btn-green" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'延期\', mask:true,width:800, height:500}">延期</a>&nbsp;';
    }


    /*延期*/
    public function delayAction(){

        $form= new \Prj\View\Bjui\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $pkey = $this->getPkey();
        $key = $this->_request->get("__pkey__");
        $form->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('expireTime', '', '失效时间'))
        ->appendHiddenFirst("__pkey__",$key);
        if($form->isUserRequest($this->_request)){
            $fields = $form->getInputs();
            \Prj\Loger::outVal("fileds",$pkey);
            if(empty($fields['expireTime'])) return $this->returnError("过期时间不能设置为空");

            if($fields['expireTime']<date('Y-m-d')) return $this->returnError("设置的时间不能小于当前时间");
            $expireTime = strtotime($fields['expireTime']);
            $time = date('Y-m-d H:i:s',mktime(23,59,59,date('m',$expireTime),date('d',$expireTime),date('Y',$expireTime)));
            $params = ['expireTime'=>$time];

            $ret = \Prj\Model\ZyBusiness\UserCoupon::updateOne($params,$pkey);

            if($ret === true) return $this->returnError("修改失败");
            $logContent = "userId{".$pkey['userId']."}的优惠券ucId{".$pkey['ucId']."}失效时间延长时间到".$time;
            $this->addLog($logContent,\Prj\Model\ZyBusiness\UserCoupon::getClassName());
            return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,"更新成功",true);
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('延期');
            $page->initForm($form);

            $this->renderPage($page);
        }

    }




}