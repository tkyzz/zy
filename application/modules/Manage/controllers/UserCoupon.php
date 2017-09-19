<?php

/*
 *用户卡券*/
class userCouponController extends \Rpt\Manage\ManageIniCtrl
{

    protected $statusArr = [
        ''     =>  '请选择',
        'NOTUSED'   =>  '未使用',
        'USED'      =>  '已使用',
        'EXPIRED'   =>  '已过期',
        'WRITEOFF'  =>  '已核销',
        'LOCK'      =>  '锁定中',
        'INVALID'   =>  '已作废'
    ];


    protected $typeArr = [
        'REDPACKETS'    =>  '红包',
        'COUPON'        =>  '优惠券',
        'RATECOUPON'    =>  '加息券'
    ];
    public function indexAction(){
        $form = $this->searchForm();

        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('手机号','phone',160, '')
            ->addHeader('卡券名称','name',160,'')
            ->addHeader('领用时间','lenderTime',160,'')
            ->addHeader('使用时间','useTime',160,'')
            ->addHeader('生效时间','effectTime',160,'')
            ->addHeader('失效时间','expireTime',160,'')
            ->addHeader('卡券金额','couponAmount',160,'')
            ->addHeader('类型','couponType',100,'')
            ->addHeader('状态','couponStatus',160,'')
            ->addHeader('操作', 'op', 300, '')
            ->initJsonDataUrl($this->urlForListLog($form));

        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('用户卡券管理')
            ->initForm($form)
            ->initDatagrid($table);
        $this->renderPage($page);
    }


    public function detailAction(){
        $pkey = $this->getPkey();
        if($pkey){
            $oid = $pkey['ucId'];
        }else{
            return $this->returnError('参数错误[oid]');
        }

        $actInfo = \Prj\Model\ZyBusiness\UserCoupon::getRecord('*',['ucId'=>$oid]);

        if(empty($actInfo)) return $this->returnError('没有此条记录');

//        $event = \Prj\Model\Event::getOneByEventId($actInfo['eventId']);
//        $actInfo['eventName'] = $event['title'];
//
        $actInfo['phone'] = array_shift(\Prj\Model\User::getRecords('userAcc' , ['oid'=>$actInfo['userId']]))['userAcc'];
        $actInfo['status'] = $this->statusArr[$actInfo['status']];
        $actInfo['type'] = $this->typeArr[$actInfo['type']];



        $page = \Prj\View\Bjui\Detail::getInstance()
            ->setData('手机号',$actInfo['phone'])
            ->setData('卡券名称',$actInfo['name'])
            ->setData('状态',$this->statusArr[$actInfo['couponStatus']])->setData('类型',$this->typeArr[$actInfo['couponType']])
            ->setData('领用时间',$actInfo['lenderTime'])
            ->setData('使用时间',$actInfo['useTime'])->setData('生效时间',$actInfo['effectTime'])
            ->setData('失效时间',$actInfo['expireTime'])->setData('卡券金额',$actInfo['couponAmount'])
            ->setData('最小投资额',$actInfo['investAmount'])->setData('产品范围',$actInfo['limitLabels']);
        $this->renderPage($page);
    }


    protected function urlForListLog($form,$act = 'listdata')
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $where = $form->getWhere();
        if(empty($where)){
            return $uri->uri(null,$act);
        }else{
            return $uri->uri(array('__wHeRe__'=> bin2hex(json_encode($where))),$act);
        }

    }


    public function listdataAction(){
        $getwhere =  $this->_request->get('__wHeRe__');
        if(empty($getwhere)){
            $where = array();
        }else{
            $where = json_decode(hex2bin($getwhere),true);
        }

        $obj = \Prj\Model\ZyBusiness\UserCoupon::getCopy(null);
//        $whereArr = array();
//        list($db,$tb) = $obj->dbAndTbName();
        $fields = 'ucId,(select userAcc from jz_db.tb_user_0 b where b.oid=`userId` ) as phone,couponId,name,lenderTime,useTime,effectTime,expireTime,couponAmount,couponType,couponStatus';
        if(!empty($where)){
            if(!empty($where['phone'])){

                $oid = array_shift(\Prj\Model\User::getRecords('oid' , ['UserAcc'=>$where['phone']]));
                $whereArr['userId'] = $oid['oid'];
            }

            if(!empty($where['status'])) $whereArr['couponStatus'] = $where['status'];

            if(!empty($where[']ymdhis'])) $whereArr[']lenderTime'] = date('Y-m-d H:i:s',strtotime($where[']ymdhis']));

            if(!empty($where['[ymdhis'])) $whereArr['[lenderTime'] = date('Y-m-d H:i:s',strtotime($where['[ymdhis']));


            $db = $obj->dbWithTablename();
            $arr = $db->getRecords($db->kvobjTable(),$fields,$whereArr,'rsort lenderTime');

        }else{
            $db = $obj->dbWithTablename();
            $arr = $db->getRecords($db->kvobjTable(),$fields,'','rsort lenderTime',300);
        }

        foreach($arr as $k => $v){
            $arr[$k]['couponStatus'] = $this->statusArr[$v['couponStatus']];
//            $arr[$k]['phone'] = array_shift(\Prj\Model\User::getRecords('userAcc' , ['oid'=>$v['userId']]))['userAcc'];
            $arr[$k]['couponType'] = $this->typeArr[$v['couponType']];
            $arr[$k]['op'] = $this->btnDetail(['ucId'=>$v['ucId']]);

        }
        $this->renderArray($arr);

    }

    protected function searchForm()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $unique_htmlid = 'frm_'.$uri->currentModule().'_'.$uri->currentController();
        $form= new \Sooh2\BJUI\Forms\Search($uri->uri(),'post',$unique_htmlid);
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('eq_phone', '', '手机号'));
        $form->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_status', '', '状态')->initOptions($this->statusArr));
        $form->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('gt_ymdhis', '', '开始时间'));
        $form->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('lt_ymdhis', '', '结束时间'));
        $form->isUserRequest($this->_request);
        return $form;
    }



}