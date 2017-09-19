<?php
/*
 * AppIcon设置
 */

class AppIconController extends \Rpt\Manage\ManageCtrl
{
    public function indexAction(){
        $iconLink=array('0'=>'链接','1'=>'跳转(只适用app)');
        $iconTo=array('H5'=>'H5','INVITATION'=>'邀请','HELP'=>'帮助中心','COUPON'=>"优惠券","CALENDAR"=>"回款日历","SINGIN"=>"每日签到");
        $tmp = \Prj\Model\AppIcon::getCopy(null);
        list($db,$tb)=$tmp->dbAndTbName();
        $arr=$db->getRecord($tb,"*","","sort createTime");

        echo "<div class=\"bjui-pageContent\" style=\"top: 30px; bottom: 0px;\">";
        echo "<br/>";
        //echo "<a href=\"/manage/AppIcon/iconadd\" data-toggle=\"dialog\" data-options=\"{mask:true,width:800,height:800}\"><img src=\"/B-JUI//imgs/btn0_addnew.png\" border=\"0\"></a>";
        echo "图标1:<img src='".$arr['icon1']."'/ width='80'>";
        echo "<br/>";
        echo "<br/>";
        echo "图标1标题:".$arr['icon1Title'];
        echo "<br/>";
        echo "图标1链接类型:".$iconLink[$arr['icon1Link']];
        echo "<br/>";
        echo "图标1链接地址:";
        echo $str=$arr['icon1Link'] ? $iconTo[$arr['icon1Page']] : $arr['icon1Url'];
        echo "<hr/>";
        echo "图标2:<img src='".$arr['icon2']."'/ width='80'>";
        echo "<br/>";
        echo "<br/>";
        echo "图标2标题:".$arr['icon2Title'];
        echo "<br/>";
        echo "图标2链接类型:".$iconLink[$arr['icon2Link']];
        echo "<br/>";
        echo "图标2链接地址:";
        echo $str=$arr['icon2Link'] ? $iconTo[$arr['icon2Page']] : $arr['icon2Url'];
        echo "<hr/>";
        echo "图标3:<img src='".$arr['icon3']."'/ width='80'>";
        echo "<br/>";
        echo "<br/>";
        echo "图标3标题:".$arr['icon3Title'];
        echo "<br/>";
        echo "图标3链接类型:".$iconLink[$arr['icon3Link']];
        echo "<br/>";
        echo "图标3链接地址:";
        echo $str=$arr['icon3Link'] ? $iconTo[$arr['icon3Page']] : $arr['icon3Url'];
        echo "<hr/>";
        echo "图标4:<img src='".$arr['icon4']."'/ width='80'>";
        echo "<br/>";
        echo "<br/>";
        echo "图标4标题:".$arr['icon4Title'];
        echo "<br/>";
        echo "图标4链接类型:".$iconLink[$arr['icon4Link']];
        echo "<br/>";
        echo "图标4链接地址:";
        echo $str=$arr['icon4Link'] ? $iconTo[$arr['icon4Page']] : $arr['icon4Url'];
        echo "<hr/>";
        echo "<a href=\"/manage/AppIcon/iconUpd/oid/{$arr['oid']}\" data-toggle=\"dialog\" data-options=\"{id:'iniUpd', title:'修改', mask:true,width:1000, height:600}\">修改</a>";
        echo "</div>";
    }


    /**
     *图片上传
     */
    public function imgUploadAction(){
        $up=new \Sooh2\Upload;
        $fileField=array_keys($_FILES)[0];
        $uploadPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadPath');
        $up -> setOption("path", $uploadPath.'/icon/')
            -> setOption("maxSize", 20000000)
            -> setOption("allowType", array("png", "jpg","jpeg"));
        if($up->upload($fileField)){
            $uploadUrl=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadUrl');
            $fileName=$uploadUrl."/icon/".$up->getFileName();
            $arr=array('statusCode'=>'200','filename'=>$fileName);
            $this->renderArray($arr);
        }else{
            $arr=array('statusCode'=>'100','message'=>$up->getErrorMsg());
            $this->renderArray($arr);
        }
    }

    /**
     *添加Banner
     */
    public function iconAddAction(){
        $this->optionsLink=array('xx'=>'链接&nbsp;&nbsp;','1'=>'跳转(只适用app)');
        $this->optionsTo=array('T1'=>'活期','T2'=>'定期','T3'=>'注册');
        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'imgUpload');
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('icon1', '','图标1',$imgAction)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon1Title', '','图标1标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon1Link', '','图标1链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon1Url', '','图标1链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon1Page', '','图标1跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('icon2', '','图标2',$imgAction)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon2Title', '','图标2标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon2Link', '','图标2链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon2Url', '','图标2链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon2Page', '','图标2跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('icon3', '','图标3',$imgAction)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon3Title', '','图标3标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon3Link', '','图标3链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon3Url', '','图标3链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon3Page', '','图标3跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('icon4', '','图标4',$imgAction)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon4Title', '','图标4标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon4Link', '','图标4链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon4Url', '','图标4链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon4Page', '','图标4跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo));
        if($edtForm->isUserRequest($this->_request)){

            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $edtForm->getInputs();

            //替换isLink值 默认值为0取不到
            if($changed['icon1Link']=='xx'){
                $changed['icon1Link']=0;
            }
            if($changed['icon2Link']=='xx'){
                $changed['icon2Link']=0;
            }
            if($changed['icon3Link']=='xx'){
                $changed['icon3Link']=0;
            }
            if($changed['icon4Link']=='xx'){
                $changed['icon4Link']=0;
            }

            $changed['operator']=md5($this->getMyId());
            $changed['createTime']=date("Y-m-d H:i:s");
            $oid= \Prj\Model\AppIcon::getAppIconOid();
            $obj = \Prj\Model\AppIcon::getCopy($oid);
            $obj->load();
            if($obj->exists()){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录已经存在');
                return;
            }
            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添加记录',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加记录失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('添加icon');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
   }


   /**
    * 修改Banner
    */
    public  function iconUpdAction(){
        $oid = $this->_request->get('oid');
        $obj = \Prj\Model\AppIcon::getCopy($oid);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }

        $this->optionsLink=array('xx'=>'链接&nbsp;&nbsp;','1'=>'跳转(只适用app)');
        $this->optionsTo=array('H5'=>'H5','INVITATION'=>'邀请','HELP'=>'帮助中心','COUPON'=>"优惠券","CALENDAR"=>"回款日历","SINGIN"=>"每日签到");
        $icon1Link=$obj->getField('icon1Link');
        $icon2Link=$obj->getField('icon2Link');
        $icon3Link=$obj->getField('icon3Link');
        $icon4Link=$obj->getField('icon4Link');
        if($icon1Link==0){
            $icon1Link='xx';
        }
        if($icon2Link==0){
            $icon2Link='xx';
        }
        if($icon3Link==0){
            $icon3Link='xx';
        }
        if($icon4Link==0){
            $icon4Link='xx';
        }
        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'imgUpload');
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('oid', $oid)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon1Title', $obj->getField('icon1Title'),'图标1标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon2Title',  $obj->getField('icon2Title'),'图标2标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon1Link', $icon1Link,'图标1链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon2Link', $icon2Link,'图标2链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon1Url', $obj->getField('icon1Url'),'图标1链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon2Url', $obj->getField('icon2Url'),'图标2链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon1Page', $obj->getField('icon1Page'),'图标1跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon2Page', $obj->getField('icon2Page'),'图标2跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('icon1', $obj->getField('icon1'),'图标1',$imgAction)->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('icon2', $obj->getField('icon2'),'图标2',$imgAction)->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon3Title', $obj->getField('icon3Title'),'图标3标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon4Title', $obj->getField('icon4Title'),'图标4标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon3Link', $icon3Link,'图标3链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon4Link', $icon4Link,'图标4链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon3Url',  $obj->getField('icon3Url'),'图标3链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('icon4Url', $obj->getField('icon4Url'),'图标4链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon3Page', $obj->getField('icon3Page'),'图标3跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('icon4Page', $obj->getField('icon4Page'),'图标4跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('icon4', $obj->getField('icon4'),'图标4',$imgAction)->initChecker(new \Sooh2\Valid\Str(false)))
        ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('icon3', $obj->getField('icon3'),'图标3',$imgAction)->initChecker(new \Sooh2\Valid\Str(false)));


        if($edtForm->isUserRequest($this->_request)) {//用户提交的请求
            $changed = $edtForm->getInputs();

            //替换isLink值 默认值为0取不到
            if($changed['icon1Link']=='xx'){
                $changed['icon1Link']=0;
            }
            if($changed['icon2Link']=='xx'){
                $changed['icon2Link']=0;
            }
            if($changed['icon3Link']=='xx'){
                $changed['icon3Link']=0;
            }
            if($changed['icon4Link']=='xx'){
                $changed['icon4Link']=0;
            }

            if($changed['icon1']==''){
                unset($changed['icon1']);
            }
            if($changed['icon2']==''){
                unset($changed['icon2']);
            }
            if($changed['icon3']==''){
                unset($changed['icon3']);
            }
            if($changed['icon4']==''){
                unset($changed['icon4']);
            }
            $changed['updateOpe']=md5($this->getMyId());

            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '更新成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("修改icon");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }

}

