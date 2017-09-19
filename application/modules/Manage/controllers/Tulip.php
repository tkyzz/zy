<?php

/*
 *用户卡券*/
class TulipController extends \Rpt\Manage\ManageIniCtrl
{

    protected $statusArr = [
        ''     =>  '请选择',
        'notUsed'   =>  '未使用',
        'used'      =>  '已使用',
        'expired'   =>  '已过期',
        'writeOff'  =>  '已核销',
        'lock'      =>  '锁定中',
        'invalid'   =>  '已作废'
    ];


    protected $typeArr = [
        'redPackets'    =>  '红包',
        'coupon'        =>  '优惠券',
        'tasteCoupon'   =>  '体验金',
        'rateCoupon'    =>  '加息券'
    ];
    public function indexAction(){
        $form = $this->searchForm();

        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('手机号','phone',160, '')
            ->addHeader('卡券名称','name',160,'')
            ->addHeader('领用时间','leadTime',160,'')
            ->addHeader('使用时间','useTime',160,'')
            ->addHeader('生效时间','start',160,'')
            ->addHeader('失效时间','finish',160,'')
            ->addHeader('卡券金额','amount',160,'')
            ->addHeader('类型','type',100,'')
            ->addHeader('状态','status',160,'')
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
            $oid = $pkey['oid'];
        }else{
            return $this->returnError('参数错误[oid]');
        }

        $actInfo = \Prj\Model\Tulip\UserCoupon::getRecord('*',['oid'=>$oid]);

        if(empty($actInfo)) return $this->returnError('没有此条记录');

        $event = \Prj\Model\Event::getOneByEventId($actInfo['eventId']);
        $actInfo['eventName'] = $event['title'];
        
        $actInfo['phone'] = array_shift(\Prj\Model\User::getRecords('userAcc' , ['oid'=>$actInfo['userId']]))['userAcc'];
        $actInfo['status'] = $this->statusArr[$actInfo['status']];
        $actInfo['type'] = $this->typeArr[$actInfo['type']];



        $page = \Prj\View\Bjui\Detail::getInstance()
            ->setData('手机号',$actInfo['phone'])
            ->setData('卡券名称',$actInfo['name'])->setData('卡券描述',$actInfo['description'])
            ->setData('状态',$actInfo['status'])->setData('类型',$actInfo['type'])
            ->setData('领用时间',$actInfo['leadTime'])->setData('核销时间',$actInfo['settlement'])
            ->setData('使用时间',$actInfo['useTime'])->setData('生效时间',$actInfo['start'])
            ->setData('失效时间',$actInfo['finish'])->setData('卡券金额',$actInfo['amount'])
            ->setData('事件名称',$actInfo['eventName'])->setData('事件标题',$actInfo['eventTitle'])
            ->setData('最小投资额',$actInfo['investAmount'])->setData('产品范围',$actInfo['products'])
            ->setData('使用规则',$actInfo['rules']);
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

        $obj = \Prj\Model\Tulip\UserCoupon::getCopy(null);
//        $whereArr = array();
//        list($db,$tb) = $obj->dbAndTbName();

        if(!empty($where)){
            if(!empty($where['phone'])){

                $oid = array_shift(\Prj\Model\User::getRecords('oid' , ['UserAcc'=>$where['phone']]));
                $whereArr['*userId'] = $oid['oid'];
            }

            if(!empty($where['status'])) $whereArr['status'] = $where['status'];

            if(!empty($where[']ymdhis'])) $whereArr[']leadTime'] = date('Y-m-d H:i:s',strtotime($where[']ymdhis']));

            if(!empty($where['[ymdhis'])) $whereArr['[leadTime'] = date('Y-m-d H:i:s',strtotime($where['[ymdhis']));


            $db = $obj->dbWithTablename();
            $arr = $db->getRecords($db->kvobjTable(),'*',$whereArr,'rsort leadTime');

        }else{
            $db = $obj->dbWithTablename();
            $arr = $db->getRecords($db->kvobjTable(),'oid,userId,couponBatch,name,leadTime,useTime,start,finish,amount,type,status','','rsort leadTime',300);
        }

        foreach($arr as $k => $v){
            $arr[$k]['status'] = $this->statusArr[$v['status']];
            $arr[$k]['phone'] = array_shift(\Prj\Model\User::getRecords('userAcc' , ['oid'=>$v['userId']]))['userAcc'];
            $arr[$k]['type'] = $this->typeArr[$v['type']];
            $arr[$k]['op'] = $this->btnDetail(['oid'=>$v['oid']]);

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