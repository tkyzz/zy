<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/9
 * Time: 10:48
 */
class FeedbackController extends \Rpt\Manage\ManageIniCtrl
{
    protected $feedbackStatus = [0=>"待处理",1=>"已确认",2=>"已忽略"];
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = Sooh2\HTML\Table::factory()
            ->addHeader('手机号','phone','250','')
            ->addHeader('反馈内容','content','300','')
            ->addHeader('IDFA','IDFA','200','')
            ->addHeader('IMEI','IMEI','250','')
            ->addHeader('品牌','brand','250')
            ->addHeader('设备名','deviceName','250')
            ->addHeader("确认信息","answer","350")
            ->addHeader('创建时间','createTime','250')
            ->addHeader('状态','statusCode','250')
            ->addHeader('操作','op','250')
            ->initJsonDataUrl($uri->uri('','listData'));

        $page = Sooh2\BJUI\Pages\ListStd::getInstance()->init('消息发送')->initStdBtn($uri->uri('','pageAdd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }



    public function listDataAction(){

        $obj = \Prj\Model\FeedBack::getCopy(null);
        list($db,$tb) = $obj->dbAndTbName();
        $res = $db->getRecords($tb,'*');
        foreach($res as $k => $v){
            if($v['statusCode']==0) {
                $res[$k]['op'] = $this->btnEdt(['oid'=>$v['oid']])."|".$this->btnChangeStatus(['oid'=>$v['oid']]);
                $res[$k]['statusCode'] = $this->feedbackStatus[$v['statusCode']];
                continue;
            }
            $res[$k]['statusCode'] = "<p class='bg-primary'>".$this->feedbackStatus[$v['statusCode']]."</p>";
        }

        $this->renderArray($res);
    }


    protected function btnChangeStatus($pkey)
    {

        $warn = "确定要进行【忽略】么？！";

        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey)), "pageIgnore");
        return '<a href="' . $url . '" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'' . $warn . '\', okCall:function(){mydelcmd(\'' . $url . '\');}}">' . "忽略" . '</a>&nbsp;';
    }

    protected function btnEdt($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey)), 'pageupd');
        return '<a href="' . $url . '" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'确认\', mask:true,width:800, height:500}">确认</a>&nbsp;';
    }


    public function pageIgnoreAction(){
        $strpkey = $this->_request->get('__pkey__');

        $obj = \Prj\Model\FeedBack::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $obj->setField("statusCode",2);
        try {
            $ret = $obj->saveToDB();
            return $ret ? \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'操作成功！') : \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'操作失败！');

        }catch (Exception $ex) {
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '忽略失败:'.$ex->getMessage());
        }
    }


    public function pageupdAction(){
        $strpkey = $this->_request->get('__pkey__');

        $obj = \Prj\Model\FeedBack::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('answer', "",'备注')->initChecker(new \Sooh2\Valid\Str(true)));
        if($edtForm->isUserRequest($this->_request)){
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $edtForm->getInputs();
            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            $obj->setField("updateTime",date("Y-m-d H:i:s"));
            $obj->setField("statusCode",1);
            try {
                $ret = $obj->saveToDB();
                if ($ret) {

                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '确认成功', true);
                }

            }catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '确认失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("审核反馈信息");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }
}