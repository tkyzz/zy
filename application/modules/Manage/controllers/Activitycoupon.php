<?php
class ActivitycouponController extends \Rpt\Manage\ManageIniCtrl
{
    const  SUCCESS_STATUS='success';
    const FAIL_STATUS='fail';
    const NEW_STATUS = 'new';
    protected static $SWITCH_STATUS =[
        self::SUCCESS_STATUS    =>  '成功',
        self::FAIL_STATUS       =>  '失败',
        self::NEW_STATUS        =>  '新建'
        ];



    protected function searchForm()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form= new \Sooh2\BJUI\Forms\Search($uri->uri(),'post','listdata');
        $actArr = \Prj\Model\Activity::$actCodeMap;
        $actArr[''] = '请选择';
//        array_unshift($actArr,'请选择');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_eventId', '', '活动名称')->initOptions($actArr))
        ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('eq_phone', '', '手机号'));

        $form->isUserRequest($this->_request);
        return $form;
    }

    public function indexAction(){
        $form = $this->searchForm();
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('用户手机号','ucUserId',250,'')
            ->addHeader('订单id号','orderId',200,'')
            ->addHeader('产品名','productId',250,'')
            ->addHeader('活动名称','eventId',250,'')
            ->addHeader('发放状态','statusCode',250)
            ->addHeader('发放时间','createTime',250,'')
            ->initJsonDataUrl($this->urlForActivityCoupon($form));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('发券记录')
            ->initForm($form)->initDatagrid($table);
        $this->renderPage($page);
    }


    public function listDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = json_decode(hex2bin($getWhere),true);

        $where = array();
        $obj = \Prj\Model\ActivityCoupon::getCopy();
        list($db,$tb) = $obj->dbAndTbName();
        if(!empty($getWhere)){
            if(!empty($getWhere['phone'])){
                $oid = \Prj\Model\User::getRecords('oid' , ['UserAcc'=>$getWhere['phone']]);
                $where['ucUserId'] = $oid[0]['oid'];

            }
            if(!empty($getWhere['eventId'])){
                $where['*type'] = $getWhere['eventId'];
            }

            $arr = $db->getRecords($tb,'type,orderId,productId,ucUserId,eventId,statusCode',$where,'rsort createTime');



        }else{

            $arr = $db->getRecords($tb,'type,orderId,productId,ucUserId,eventId,statusCode','','rsort createTime');
        }

        foreach($arr as $k =>$v){
            $arr[$k]['type'] = $v['type']?\Rpt\Manage\Activity::$actCodeMap[$v['type']]:'';

            $arr[$k]['orderId'] = $v['orderId']?$v['orderId']:'';
            $arr[$k]['productId'] = $v['productId']?\Prj\Model\MimosaProduct::getOneByProId($v['productId'])['name']:'';
            $arr[$k]['ucUserId'] = $v['ucUserId']?\Prj\Model\UcUser::getUserByOid($v['ucUserId'])['userAcc']:'';

            $event = $v['eventId']?\Rpt\Manage\Activity::$actCodeMap[\Prj\Bll\Activity::getInstance()->getRecords(array('oid'=>$v['eventId']))['data'][0]['actCode']]:'';
//
            $arr[$k]['eventId']=$v['eventId']?$event:'';

            $arr[$k]['statusCode'] = $v['statusCode']?self::$SWITCH_STATUS[$v['statusCode']]:'';
            $arr[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
        }


        $this->renderArray($arr);
    }


    protected function urlForActivityCoupon($form,$act = 'listData')
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