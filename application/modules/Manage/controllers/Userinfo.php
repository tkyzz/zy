<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/20
 * Time: 15:04
 */
class UserinfoController extends \Rpt\Manage\ManageIniCtrl
{
    public function indexAction(){
        $form = $this->getSearchForm();

        $table = \Sooh2\HTML\Table::factory()->addHeader("手机号","userAcc",250)
            ->addHeader("状态","status",250)
            ->addHeader("创建时间","createTime",250)
            ->addHeader("操作",'op',250)->initJsonDataUrl($this->urlForListLog($form));

        $page = Sooh2\BJUI\Pages\ListWithCondition::getInstance()->init("用户列表")->initForm($form)->initDatagrid($table);
        $this->renderPage($page);
    }


    public function getSearchForm(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $unique_htmlid = 'frm_'.$uri->currentModule().'_'.$uri->currentController();
        $form= new \Sooh2\BJUI\Forms\Search($uri->uri(),'post',$unique_htmlid);

        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('eq_userAcc', '', '手机号'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("gt_createTime","","开始时间"))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("lt_createTime","","结束时间"));
        $form->isUserRequest($this->_request);
        return $form;
    }


    public function listDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');

        $getWhere = json_decode(hex2bin($getWhere),true);

        $where = array();
        $obj = \Prj\Model\User::getCopy();
        list($db,$tb) = $obj->dbAndTbName();

        if(!empty($getWhere)){

            $arr = $db->getRecords($tb,"oid,userAcc,status,createTime",$getWhere,"rsort createTime");
        }else{
            $arr = $db->getRecords($tb,"oid,userAcc,status,createTime",'',"rsort createTime");

        }

        foreach($arr as $k => $v){
            $arr[$k]['status'] =  $v['status']=='normal'?'正常':'冻结';
            $arr[$k]['op'] = $this->btnLookInDatagrid($v['oid']);
        }

        $this->renderArray($arr);
    }



    protected function btnLookInDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey)), 'detail');
        return '<a href="' . $url . '" data-toggle="navtab" data-options="{id:\'delUpd\', title:\'查看详情\', mask:true,width:800, height:500}">详情</a>';
    }

    public function detailAction(){
        $strpkey = $this->_request->get('__pkey__');
        $key = json_decode(hex2bin($strpkey),true);
        $ret = \Prj\Model\User::getRecord("*",array("oid"=>$key));
        $ret['status'] = $ret['status'] == 'normal'?"正常":"已冻结";
        $ret['source'] = $ret['source'] == 'frontEnd'?"前台":'后台';
        $page = \Prj\View\Bjui\Detail2::getInstance();

//        $page = \Prj\View\Bjui\Detail::getInstance();
        $page->setData("手机号：",$ret['userAcc'],2,"用户基本信息")->setData("状态",$ret['status'],2)
            ->setData("来源",$ret['source'])->setData('创建时间',$ret['createTime'],2,'',true);
        $page->setData("用户：",'dsd',2,"增值税")->setData("金额",100,2,'',true);
        $arr = [[0,1,2,3],[5,6,7,4]];
        $table = \Sooh2\HTML\Table::factory();
        $table->addHeader("订单号",'orderId',200)->addHeader("金额","money",200)
            ->addHeader("时间","createTime",200)->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri("","getorderNum"));
//        $p = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()->initForm($page)->initDatagrid($table);
        $p = \Prj\View\Bjui\Detail2::getInstance()->init("订单")->initForm($page)->initDatagrid($table);
//        $page->setData("手机号：",$ret['userAcc'],3)->setData("状态：",$ret['status'],3)->setData("来源",$ret['source'],3)->setData("创建时间",$ret['createTime'])->init("用户详细信息",3);

        $this->renderPage($p);
    }

    public function getorderNumAction(){
        $arr =  [
            ['orderId'=>1,"money"=>100,"createTime"=>"2017-07-16"],
            ['orderId'=>2,"money"=>200,"createTime"=>"2017-08-16"]
        ];
        $this->renderArray($arr);
    }

    protected function urlForListLog($form,$act = 'listData')
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