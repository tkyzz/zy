<?php
class NewbieController extends \Rpt\Manage\ManageIniCtrl
{

    protected $relate_id = array('register_bonus'=>'25','bindingCard_bonus'=>'26','recharge_bonus'=>'30','investment_bonus'=>'27',
        'logo_change'=>'22','logo_icon'=>'28','logo_url'=>'29'
        );


    protected $optionsLogo = array('0'=>'关闭','1'=>'开启');


    public function indexAction()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('配置项名', 'name', 300, '')
            ->addHeader('配置项值', 'value', 300, '')
            ->addHeader('更新时间', 'create_time', 300, '')
            ->addHeader('操作', 'op', 300, '')
            ->initJsonDataUrl($uri->uri(null,'listNewbie'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('配置项列表')->initDatagrid($table);

        $this->renderPage($page);

    }




    protected function btnChangeStatus($pkey, $action, $status)
    {
        if ($status == '1') {
            $btnName = '关闭';
            $statusUdp = '0';
        } else {
            $btnName = '开启';
            $statusUdp = '1';
        }
        $warn = "确定要进行【{$btnName}】操作么？！";

        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey), 'value' => $statusUdp), $action);
        return '<a href="' . $url . '" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'' . $warn . '\', okCall:function(){mydelcmd(\'' . $url . '\');}}">' . $btnName . '</a>&nbsp;';
    }


    public function listNewbieAction(){
        $obj = \Rpt\Manage\ManageActivitySchemeConfig::getCopy(null);

        list($db, $tb) = $obj->dbAndTbName();

//        $fields = $this->listFieldsAction();
        $arr = $db->getRecords($tb, 'id,name,value,create_time',"flag like '%newbieStepbonus_%'");
        foreach ($arr as $i => $r) {
            $pkey = array('id' => $r['id']);
            if ($r['id'] == $this->relate_id['logo_icon']) {
                $arr[$i]['value'] = "<img src='" . $r['value'] . "' width='150' height='80'/>";
            }
            $arr[$i]['op'] = $this->btnEdtInDatagrid($pkey);
            if($r['id'] == $this->relate_id['logo_change']){
                if($r['value'] == 1){
                    $arr[$i]['value'] = '<span class="bg-primary">已开启</span>';
                }else{
                    $arr[$i]['value'] = '已关闭';
                }

                $arr[$i]['op'] = $this->btnChangeStatus($pkey, 'pageChangeStatus', $r['value']);
            }




        }
        $this->renderArray($arr);
    }


    public function pageChangeStatusAction()
    {
        $pkey = $this->getPkey();
        $statusUpd = $this->_request->get('value');
        if (!in_array($statusUpd, ['0', '1'])) {
            return $this->returnError('数据不合法！');
        }

        $Model = \Rpt\Manage\ManageActivitySchemeConfig::getCopy($pkey);
        $Model->load();
        if ($Model->exists()) {
            $Model->setField('value', $statusUpd);
            $ret = $Model->saveToDB();
            return $ret ? \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'操作成功！') : \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'操作失败！');
        } else {
            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'记录不存在！');
        }
    }


    public function newbieData(){
        $obj = \Rpt\Manage\ManageActivitySchemeConfig::getCopy(null);

        list($db, $tb) = $obj->dbAndTbName();
        $data = array();
//        $fields = $this->listFieldsAction();
        $arr = $db->getRecords($tb, 'id,name,value',"flag like '%newbieStepbonus_%'");
        foreach ($arr as $k=>$v){
            $data[$v['id']] = $v['value'];

        }
        return $data;
    }


    public function pageupdAction()
    {
        $strpkey = $this->_request->get('__pkey__');


        $obj = \Rpt\Manage\ManageActivitySchemeConfig::getByBASE64($strpkey);

        list($db, $tb) = $obj->dbAndTbName();



        $pkey = \Lib\Misc\StringH::base64DecodePkey($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'imgUpload');
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $valData = $this->newbieData();
        $url_width = "size=50";
        if($pkey['id']>=$this->relate_id['register_bonus'] && $pkey['id']<=$this->relate_id['investment_bonus']||$pkey['id']==$this->relate_id['recharge_bonus'] ){
            $edtForm->appendHiddenFirst('__pkey__',$strpkey)
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value[{$this->relate_id['register_bonus']}]",$valData[$this->relate_id['register_bonus']],'注册奖励金额')->initChecker(new \Sooh2\Valid\Str(true,0,1000)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value[{$this->relate_id['bindingCard_bonus']}]",$valData[$this->relate_id['bindingCard_bonus']],'绑卡奖励金额')->initChecker(new \Sooh2\Valid\Str(true,0,1000)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value[{$this->relate_id['recharge_bonus']}]",$valData[$this->relate_id['recharge_bonus']],'充值奖励金额')->initChecker(new \Sooh2\Valid\Str(true,0,1000)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value[{$this->relate_id['investment_bonus']}]",$valData[$this->relate_id['investment_bonus']],'投资奖励金额')->initChecker(new \Sooh2\Valid\Str(true,0,1000)));
        }elseif ($pkey['id'] == $this->relate_id['logo_change']){
            $edtForm->appendHiddenFirst('__pkey__',$strpkey)
                ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('value',$obj->getField('value'),'是否开启图标')->initChecker(new \Sooh2\Valid\Str())->initOptions($this->optionsLogo));
        }elseif ($pkey['id']==$this->relate_id['logo_icon']||$pkey['id']==$this->relate_id['logo_url']){
            $edtForm->appendHiddenFirst('__pkey__',$strpkey)
                ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("value[{$this->relate_id['logo_change']}]",$valData[$this->relate_id['logo_change']],'是否开启图标')->initChecker(new \Sooh2\Valid\Str())->initOptions($this->optionsLogo))
                ->addFormItem(\Sooh2\BJUI\FormItem\File::factory("value[{$this->relate_id['logo_icon']}]",$valData[$this->relate_id['logo_icon']],'上传图片',$imgAction)->initChecker(new \Sooh2\Valid\Str()))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value[{$this->relate_id['logo_url']}]",$valData[$this->relate_id['logo_url']],'图片跳转地址',$url_width)->initChecker(new \Sooh2\Valid\Str()));
        }


        if($edtForm->isUserRequest($this->_request)){

            $values = $this->_request->get('value');

            if($values[$this->relate_id['logo_icon']] == ''){
                unset($values[$this->relate_id['logo_icon']]);
            }


            try{
                $flag = true;
                while(list($id,$value)=each($values)){
                    $ret = $db->updRecords($tb,array('value'=>$value),array('id'=>$id));
                    $flag = $ret?true:false;
                }


                if($flag){
//
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '更新成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败:'.$ex->getMessage());
            }
        }else {
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();

            $page->init("修改配置");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }


    }



    /**
     *图片上传
     */
    public function imgUploadAction(){
        $up=new \Sooh2\Upload;
        $fileField=array_keys($_FILES)[0];
        $uploadPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadPath');
        $up -> setOption("path", $uploadPath)
            -> setOption("maxSize", 20000000)
            -> setOption("allowType", array("png", "jpg","jpeg"));
        if($up->upload($fileField)){
            $uploadUrl=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadUrl');
            $fileName=$uploadUrl."/".$up->getFileName();
            $arr=array('statusCode'=>'200','filename'=>$fileName);
            $this->renderArray($arr);
        }else{
            $arr=array('statusCode'=>'100','message'=>$up->getErrorMsg());
            $this->renderArray($arr);
        }
    }




}