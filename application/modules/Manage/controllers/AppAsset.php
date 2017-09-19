<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/4
 * Time: 14:21
 */

class AppAssetController extends \Rpt\Manage\ManageIniCtrl
{

    public $option = ['0'=>"否",'1'=>"是"];
    public $webFormOptions= [1=>1,2=>2,3=>3,4=>4];
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = Sooh2\HTML\Table::factory()
            ->addHeader('渠道','channel','250','')
            ->addHeader('是否展示活期','showDemand','150','')
            ->addHeader('是否展示定期','showFixed','150','')
            ->addHeader('是否展示Banner','showBanner','100','')
            ->addHeader('是否展示新手','showNewbie','150','')
            ->addHeader('开屏页状态','hasAD','150','')
            ->addHeader('链接','url','400','')
            ->addHeader('开屏广告持续秒数','duration','300','')
            ->addHeader('下拉刷新时的文字提示','refreshNotice','300','')
            ->addHeader('图片','img','300','')
            ->addHeader('状态','status','300','')
            ->addHeader('开始时间','startTime','200','')
            ->addHeader('结束时间','endTime','200','')
            ->addHeader("操作","op",'250')
            ->initJsonDataUrl($uri->uri('','listData'));

        $page = Sooh2\BJUI\Pages\ListStd::getInstance()->init('App配置')->initStdBtn($uri->uri('','pageAdd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }

    public function listDataAction(){

        $res = \Prj\Model\AppAsset::getRecords("*",[]);

        $imgBaseUrl=\Sooh2\Misc\Ini::getInstance()->getIni('application.inistart.imgBaseUrl');
        foreach($res as $k => $v){
            $res[$k]['op'] = $this->btnEdtInDatagrid(['id'=>$v['id']])."|".$this->btnDelInDatagrid(['id'=>$v['id']]);
            $config = json_decode($v['config'],true);
            \Prj\Loger::outVal("config",$config);
            $res[$k]['showDemand'] = $config['showDemand']?"是":"否";
            $res[$k]['showFixed'] = $config['showFixed']?"是":"否";
            $res[$k]['showBanner'] = $config['showBanner']?"是":"否";
            $res[$k]['showNewbie'] = $config['showNewbie']?"是":"否";
            $res[$k]['hasAD'] = $config['hasAD']?"是":"否";
            $res[$k]['url'] = $config['url'];
            $res[$k]['duration'] = $config['duration'];
            $res[$k]['refreshNotice'] = $config['refreshNotice'];
            $res[$k]['img'] = "<img src='". $imgBaseUrl.$config['img']."'  height='70'/>";

            if($v['status']){
                $res[$k]['status'] = "已开启";
            }else{
                $res[$k]['status'] = "已关闭";
            }
            //$res[$k]['op'] .= $this->btnDelInDatagrid($v['id']);

        }
        \Prj\Loger::outVal("list;",$res);
        $this->renderArray($res);
    }


    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("channel",'','渠道')->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("status",0,"是否开启")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("startTime","","开始时间")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("endTime","","结束时间")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("showDemand",0,"是否展示活期")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("showBanner",0,"是否展示Banner")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("showFixed",0,"是否展示定期")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("showNewbie",0,"是否展示新手")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("color",'','客户端风格')->initOptions($this->webFormOptions))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("hasAD",1,"开屏页状态")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('url', "", '链接'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('duration', "", '开屏广告持续秒数')->initChecker(new \Sooh2\Valid\Int64(true,1,60)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('refreshNotice', "", '下拉刷新时的文字提示'))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('img', '','上传图片','/manage/inistartup/imgUpload/')->initChecker(new \Sooh2\Valid\Str(false)));

        if($form->isUserRequest($this->_request)){

            $inputs = $form->getInputs();
            $config = [
                'showDemand'    =>  $inputs["showDemand"],
                'showBanner'    =>  $inputs["showBanner"],
                'showFixed'     =>  $inputs["showFixed"],
                'showNewbie'    =>  $inputs["showNewbie"],
                'style'         =>  ['color'=>$inputs['color']],
                'hasAD'         =>  $inputs["hasAD"],
                "url"           =>  $inputs["url"],
                "duration"      =>  $inputs["duration"],
                "refreshNotice" =>  $inputs["refreshNotice"],
                "img"           =>  $inputs["img"]
            ];
            $obj = \Prj\Model\AppAsset::getCopy(null);
            $obj->setField("config",json_encode($config));
            $obj->setField("channel",$inputs['channel']);
            $obj->setField("status",$inputs['status']);
            $obj->setField("startTime",$inputs['startTime']);
            $obj->setField("endTime",$inputs['endTime']);
            $obj->setField("updateTime",date('Y-m-d H:i:s'));
            $ret = $obj->saveToDB();
            if($ret){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'添加App配置成功!',true);
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'添加App配置失败！');
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('App配置')->initForm($form);
            $this->renderPage($page,true);
        }
    }


    public function delAction()
    {
        $pkey = $this->getPkey();

        $Model = \Prj\Model\AppAsset::getCopy($pkey);
        $Model->load();
        if ($Model->exists()) {
            try {
                $Model->delete();
                return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'删除成功！');
            } catch (Exception $ex) {
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'删除失败：'.$ex->getMessage());

            }
        } else {
            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'删除失败，记录不存在！');
        }
    }


    public function pageupdAction()
    {
        $strkey = $this->_request->get("__pkey__");

        $obj = \Prj\Model\AppAsset::getByBASE64($strkey);
        $obj->load();
        $config = $obj->getField("config");

//
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->appendHiddenFirst("__pkey__",$strkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("channel",$obj->getField("channel"),'渠道')->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("status",$obj->getField("status"),"是否开启")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("startTime",$obj->getField("startTime"),"开始时间")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("endTime",$obj->getField("endTime"),"结束时间")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("showDemand",$config['showDemand'],"是否展示活期")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("showBanner",$config['showBanner'],"是否展示Banner")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("showFixed",$config['showFixed'],"是否展示定期")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("showNewbie",$config['showNewbie'],"是否展示新手")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("color",$config['style']['color'],'客户端风格')->initOptions($this->webFormOptions))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("hasAD",$config['hasAD'],"开屏页状态")->initOptions($this->option))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('url', $config['url'], '链接'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('duration', $config['duration'], '开屏广告持续秒数')->initChecker(new \Sooh2\Valid\Int64(true,1,60)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('refreshNotice', $config['refreshNotice'], '下拉刷新时的文字提示'))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('img', $config['img'],'上传图片','/manage/inistartup/imgUpload/')->initChecker(new \Sooh2\Valid\Str(false)));
        if($form->isUserRequest($this->_request)){

            $err = $form->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $inputs = $form->getInputs();
            \Prj\Loger::outVal("inputs",$inputs);
            $updConfig = [
                'showDemand'    =>  $this->_request->get("showDemand"),
                'showBanner'    =>  $this->_request->get("showBanner"),
                'showFixed'     =>  $this->_request->get("showFixed"),
                'showNewbie'    =>  $this->_request->get("showNewbie"),
                'style'         =>  ['color'=>$inputs['color']],
                'hasAD'         =>  $inputs["hasAD"],
                "url"           =>  $inputs["url"],
                "duration"      =>  $inputs["duration"],
                "refreshNotice" =>  $inputs["refreshNotice"],
            ];
            $img = $this->_request->get("img");
            if(!isset($img)) {
                $updConfig['img'] = $config['img'];
            }else{
                $updConfig['img'] = $inputs['img'];
            }
            $obj->setField("config",json_encode($updConfig));
            $obj->setField("channel",$inputs['channel']);
            $obj->setField("status",$inputs['status']);
            $obj->setField("startTime",$inputs['startTime']);
            $obj->setField("endTime",$inputs['endTime']);
            $obj->setField("updateTime",date('Y-m-d H:i:s'));
            $ret = $obj->saveToDB();
            if($ret){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'修改App配置成功!',true);
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'修改App配置失败！');
            }
        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("修改配置信息");
            $page->initForm($form);
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