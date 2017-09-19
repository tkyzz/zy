<?php
class UserInfoServiceController extends \Rpt\Manage\ManageIniCtrl
{
    public function indexAction(){
        $form = $this->getSearchForm();
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()->initForm($form);
        $this->renderPage($page);
    }


    public function getSearchForm(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $unique_htmlid = 'frm_'.$uri->currentModule().'_'.$uri->currentController();
        $form= new \Sooh2\BJUI\Forms\Search($uri->uri("","detail"),'post',$unique_htmlid);

        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号'));
        $form->isUserRequest($this->_request);
        return $form;
    }


    public function detailAction(){
        $form = $this->getSearchForm();
//        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->_request->get("phone");


        $where = array();
        $obj = \Prj\Model\UserFinal::getCopy();

        list($db,$tb) = $obj->dbAndTbName();

        if(!empty($getWhere)){

            $arr = $db->getRecord($tb,"*",$getWhere);
        }else{
            $arr['phone'] = $getWhere;

        }

        while(list($k,$v)= each($arr)){
            if(empty($v)||!$v){
                $arr[$k] = "--";
            }

            if($k == 'isTiro'&&$v){
                $arr[$k] = "是";
            }
        }
        if(!empty($arr['bankCode'])){
            $arr['BankName'] = \Prj\Model\Payment\BankInfo::getRecord("bankName",['bankCode'=>$arr['bankCode']])['bankName'];
        }

        $page = \Prj\View\Bjui\Detail2::getInstance();

        $page->setData("手机号：",$arr['phone'],2,"用户基本信息：",true)->setData("用户名：",$arr['nickname'],2)
            ->setData("生日：",$arr['ymdBirthday'],2)->setData("身份证号：",$arr['idCard'],2)
            ->setData("注册时间：",$arr['ymdReg'],2)->setData("是否新手：",$arr['isTiro'],2)
            ->setData("钱包余额：",$arr['wallet'],2,"用户账户信息：",true)
            ->setData("累计充值金额：",$arr['rechargeTotalAmount'],2)->setData("累计投资金额：",$arr['investTotalAmount'],1)
            ->setData("是否绑卡：",$arr['isBindCard'],2,"用户绑卡信息：",true)
            ->setData("首次绑卡日期",$arr['bindCardTime'],2)->setData("银行卡：",$arr['bindCardId'],2)->setData("开户行：",$arr['bankName'],2)
            ->ConditionForm($form);


        $this->_view->assign("phone",$getWhere);
        $this->renderPage($page,true);
    }


    public function getChargeHtmlAction(){
        $__wHeRe__ = $this->_request->get("__wHeRe__");
        $table = \Sooh2\HTML\Table::factory()->addHeader("订单号","orderNo",270)
            ->addHeader("订单金额(元)","orderAmount",250)
            ->addHeader("手续费(元)","fee",250)
            ->addHeader("交易状态",'orderStatus',250)
            ->addHeader("完成时间",'completeTime',250)
            ->addHeader("更新时间",'updateTime',250)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(['__wHeRe__'=>$__wHeRe__],'getOrderInfo'));

        $page = Sooh2\BJUI\Pages\ListStd::getInstance()->init("订单列表")->initDatagrid($table);
        $this->renderPage($page);
    }


    public function getBuyHtmlAction(){
        $__wHeRe__ = $this->_request->get("__wHeRe__");
        $table = \Sooh2\HTML\Table::factory()->addHeader("订单号","orderNo",300)
            ->addHeader("订单类型","orderType",250)
            ->addHeader("订单状态","orderStatus",250)
            ->addHeader("订单金额(元)","orderAmount",250)
            ->addHeader("实付金额(元)",'payAmount',250)
            ->addHeader("手续费(元)",'fee',250)
            ->addHeader("更新时间",'updateTime',250)
            ->addHeader("确认时间",'confirmedTime',250)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(['__wHeRe__'=>$__wHeRe__],'getBuyInfo'));

        $page = Sooh2\BJUI\Pages\ListStd::getInstance()->init("用户列表")->initDatagrid($table);
        $this->renderPage($page);
    }


    public function getBuyInfoAction(){
        $__wHeRe__ = $this->_request->get("__wHeRe__");
        $getInfo = json_decode(hex2bin($__wHeRe__),true);

        if(empty($getInfo['phone'])) return ;
        $userId = $this->getUidByPhone($getInfo['phone']);
        $where = [
            'userId'        =>  $userId,
            'orderType'     =>  "INVEST"
        ];
        $buyList = \Prj\Model\ZyBusiness\TradOrder::getRecords("*",$where,'rsort updateTime');
        foreach($buyList as $k =>$v){
            switch($v['orderType']){
                case "INVEST":$buyList[$k]['orderType'] = "投资";break;
//                case "REDEEM":$buyList[$k]['orderType'] = "赎回";break;
//                case "CASH":$buyList[$k]['orderType'] = "回款";break;
            }
            switch ($v['orderStatus']){
                case "REFUSED":$buyList[$k]['orderStatus'] = "已拒绝";break;
                case "SUBMITTED":$buyList[$k]['orderStatus'] = "已申请";break;
                case "CONFIRMED":$buyList[$k]['orderStatus'] = "已确认";break;
                case "INVAIN":$buyList[$k]['orderStatus'] = "已作废";break;
            }
        }
        $this->renderArray($buyList);
    }

    public function getOrderInfoAction(){
        $__wHeRe__ = $this->_request->get("__wHeRe__");
        $getInfo = json_decode(hex2bin($__wHeRe__),true);

        if(empty($getInfo['phone'])) return ;
        $userId = $this->getUidByPhone($getInfo['phone']);
        $where = [
            'orderType'     =>  $getInfo['orderType'],
            'userId'        =>  $userId
        ];
        $list = \Prj\Model\Payment\BankOrder::getRecords("*",$where,'rsort updateTime');
        foreach($list as $k => $v){
            switch ($v['orderStatus']){
                case "INIT": $list[$k]['orderStatus'] = "处理中";
                break;
                case "PROCESSING":$list[$k]['orderStatus'] = "处理中";break;
                case "FAILED":$list[$k]['orderStatus'] = "交易失败";break;
                case "SUCCESS":$list[$k]['orderStatus'] = "交易成功";break;
            }
        }
        return $this->renderArray($list);

    }







    protected function urlForListLog($form,$act = 'detail')
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $where = $form->getWhere();
        if(empty($where)){
            return $uri->uri(null,$act);
        }else{
            return $uri->uri(array('__wHeRe__'=> bin2hex(json_encode($where))),$act);
        }

    }
}