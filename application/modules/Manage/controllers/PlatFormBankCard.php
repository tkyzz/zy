<?php
/**
 * 银行信息
 *
 */
class PlatFormBankCardController extends \Rpt\Manage\ManageIniCtrl {


     public function listDataAction(){


       $list = \Prj\Model\PlatformBankcard::getRecords("oid,bankCode,bankName","","groupby bankCode");

//       $pkey = $list['oid'];
       $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/bankMaintain.json';

        $bankInfo =json_decode(file_get_contents($file),true);

        if(empty($bankInfo)){
            foreach($list as $k=>$v){
                $list[$k]['planMaintain'] = "";
                $list[$k]['op'] = $this->btnEdtInDatagrid(array('bankName'=>$v['bankName'],'bankCode'=>$v['bankCode'],"planMaintain"=>""));
            }
        }else {
            if (isset($bankInfo['bankname'])) {
                $bank[] = $bankInfo;
            } else {
                $bank = $bankInfo;
            }

            foreach ($list as $k => $v) {
                $flag = false;
                foreach ($bank as $i => $info) {

                    if (substr_count($v['bankName'], $info['bankname'])) {

                        $list[$k]['planMaintain'] = $info['planMaintain'];
                        $flag = true;

                    }
                }
                if (!$flag) {
                    $list[$k]['planMaintain'] = "";
                }
                $list[$k]['op'] = $this->btnEdtInDatagrid(array('bankName' => $v['bankName'], "bankCode"=>$list[$k]['bankCode'],"planMaintain" => $list[$k]['planMaintain']));

            }
        }

        $this->renderArray($list);
    }



    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('银行名称','bankName',200,'')
            ->addHeader('消息内容','planMaintain',500,'')
            ->addHeader('操作','op',150,'')
            ->initJsonDataUrl($uri->uri("","listData"));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('银行信息')->initDatagrid($table);

        $this->renderPage($page);


    }
//    public function bankAddAction(){
//
//        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
//        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('bankName', '','银行名称'))
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('planMaintain', '','信息内容'));
//
//        if($edtForm->isUserRequest($this->_request)){
//            $err = $edtForm->getErrors();
//            if(!empty($err)){
//                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
//                return;
//            }
//            $changed = $edtForm->getInputs();
//
//
//            $obj = \Prj\Model\PlatformBankcard::getCopy();
//            $k = 'bankName';
//            $v = $changed['bankName'];
//            $obj->setField($k, $v);
//
//            try{
//                $ret = $obj->saveToDB();
//                if($ret){
//
//
//                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添加：'.$changed['bankName'],true);
//                }else{
//                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败（银行已存在？）');
//                }
//            } catch (Exception $ex) {
//                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
//            }
//        }else {
//
//            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
//            $page->init('添加银行信息');
//            $page->initForm($edtForm);
//            $this->renderPage($page);
//        }
//    }

//    public function  delAction()
//    {
//        parent::delAction();
//    }

    public function pageUpdAction()
    {
        $strpkey = $this->_request->get('__pkey__');

        $str = json_decode(hex2bin($strpkey),true);



        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__',$strpkey)

            ->appendHiddenFirst("bankCode",$str['bankCode'])
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('bankName',$str['bankName'],'银行名称',"readonly"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory('planMaintain',$str['planMaintain'],'信息内容'));



        if($edtForm->isUserRequest($this->_request)){
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $edtForm->getInputs();
            $list = $this->getJsonConfig($changed);

            $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/bankMaintain.json';
            sort($list);
            chmod($file,0777);
            $ret = file_put_contents($file,json_encode($list , 256));
            $this->refCdnAction('https://www.zhangyuelicai.com/h5/app/bankMaintain.json');
            if($ret){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,"修改银行维护信息成功!",true);
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,"修改银行维护信息失败，请重新修改！");
            }



        }else{//展示页面
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->initForm($edtForm);
            $this->renderPage($page,true);
        }
    }






    public function getJsonConfig($getInfo){
        $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/bankMaintain.json';
        $list = \Prj\Model\PlatformBankcard::getRecords("bankCode,bankName","","groupby bankCode");
        if(file_exists($file)){
            $bankInfo =json_decode(file_get_contents($file),true);
        }else{
            $bankInfo = array();
        }


        $data = array();

        if(empty($bankInfo)){

            foreach($list as $k=>$v){
                if($getInfo['bankName']==$v['bankName']){
                    if(empty(['planMaintain'])) continue;
                    $data[] = [
                        'bankname'      =>  $getInfo['bankName'],
                        'bankCode'      =>  $getInfo['bankCode'],
                        'planMaintain'  =>  $getInfo['planMaintain'],
                    ];
                }

            }
        }else{
            if(isset($bankInfo['bankname'])){
                $bank[] = $bankInfo;
            }else{
                $bank = $bankInfo;
            }

            foreach($list as $k => $v){

                $flag = false;
                foreach($bank as $i=> $info){
                    if($info['bankname']&&($info['bankname'] == $v['bankName'])) {
                        if ($getInfo['bankName'] == $info['bankname']) {
                            if(empty($getInfo['planMaintain'])) {
                                $flag = true;
                                continue;
                            }
                            $data[] = [
                                'bankname' => $getInfo['bankName'],
                                'bankCode' => $getInfo['bankCode'],
                                'planMaintain' => $getInfo['planMaintain'],
                            ];
                            $flag = true;
                        } else {
                            $data[] = [
                                'bankname' => $info['bankname'],
                                'bankCode' => $info['bankCode'],
                                'planMaintain' => $info['planMaintain'],
                            ];
                            $flag = true;
                        }
                    }
                }
                if($flag == false&&($getInfo['bankName']==$v['bankName'])){
                    if(empty($getInfo['planMaintain'])) continue;
                    $data[] = [
                        'bankname' => $getInfo['bankName'],
                        'bankCode' => $getInfo['bankCode'],
                        'planMaintain' => $getInfo['planMaintain'],
                    ];
                }




            }
        }

        return $data;
    }

//    public function quickUpdBankInfo(){
//     \Rpt\Manage\Inistartup::refCdn();
//    }


//   public function getButton(){
//       $r=$this->getADPageAction();
//       if($r['hasAD']){
//         $str="<a href=\"/manage/inistartup/quickUpdAd/hasAD/0\" data-toggle=\"alertmsg\" data-options=\"{type:'confirm', msg:'你确定要关闭配置', okCall:function(){mydelcmd('/manage/inistartup/quickUpdAd/hasAD/0');}}\">(关闭)</a>";
//       }else{
//         $str="<a href=\"/manage/inistartup/quickUpdAd/hasAD/1\" data-toggle=\"alertmsg\" data-options=\"{type:'confirm', msg:'你确定要开启配置', okCall:function(){mydelcmd('/manage/inistartup/quickUpdAd/hasAD/1');}}\">(开启)</a>";
//       }
//       return $str;
//   }

   /*
    * 快捷修改inistartup
    *
    */
//   public function quickUpdAdAction(){
//       $changed=$this->getADPageAction();
//       $changed['hasAD']= $this->_request->get('hasAD');
//       $r = array('hasAD'=>$changed['hasAD']?true:false,'img'=>$changed['img'],'url'=>$changed['url'],'duration'=>$changed['duration'],'refreshNotice'=>$changed['refreshNotice']);
//       $s=json_encode($r);
//       $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/ADPage.json';
//       file_put_contents($file, $s);
//       $this->refCdnAction('https://www.zhangyuelicai.com/h5/app/ADPage.json');
//       \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '已更新');
//   }


    /**
     * 修改启动配置
     */
//    public function iniUpdAction(){
//        $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/ADPage.json';
//        $r=$this->getADPageAction();
//        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
//        $form->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('hasAD', $r['hasAD'], '当前状态')->initOptions($this->optionStatus))
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('url', $r['url'], '链接'))
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('duration', $r['duration'], '开屏广告<br/>持续秒数')->initChecker(new \Sooh2\Valid\Int64(true,1,60)))
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('refreshNotice', $r['refreshNotice'], '下拉刷新时<br/>的文字提示'))
//            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('img', '','上传图片','/manage/inistartup/imgUpload/')->initChecker(new \Sooh2\Valid\Str(false)))
//            ;
//        if($form->isUserRequest($this->_request)){
//            $errs = $form->getInputErrors();
//            if(!empty($errs)){
//                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, implode(',', $errs));
//                return;
//            }
//            $changed = $form->getInputs();
//            if($changed['img']==""){
//                //判断是否是远程图片文件
//                if(substr($r['img'],0,4)!='http'){
//                    $changed['img']=\Sooh2\Misc\Ini::getInstance()->getIni('application.inistart.imgBaseUrl').$r['img'];
//                }else{
//                    $changed['img']=$r['img'];
//                }
//            }else{
//                $changed['img']=\Sooh2\Misc\Ini::getInstance()->getIni('application.inistart.imgBaseUrl').$changed['img'];
//            }
//
//            $r = array('hasAD'=>$changed['hasAD']?true:false,'img'=>$changed['img'],'url'=>$changed['url'],'duration'=>$changed['duration'],'refreshNotice'=>$changed['refreshNotice']);
//            //$s = \Sooh2\Util::toJsonSimple($r);
//            $s=json_encode($r);
//            file_put_contents($file, $s);
//            $this->refCdnAction('https://www.zhangyuelicai.com/h5/app/ADPage.json');
//            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '已更新',true);
//        }else{
//            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
//            $page->init("修改启动配置");
//            $page->initForm($form);
//            $this->renderPage($page);
//        }
//
//    }








    /**
     *刷新cdn缓存
     * @param string $url
     * @return boolean
     */
    public function refCdnAction($url){
        if($url=="") {
            return false;
        }
        $key=\Sooh2\Misc\Ini::getInstance()->getIni('cdn.Alicdn.accessKeyId');
        $secret=\Sooh2\Misc\Ini::getInstance()->getIni('cdn.Alicdn.accessKeySecret');
        $activated=\Sooh2\Misc\Ini::getInstance()->getIni('cdn.Alicdn.activated');
        $cdn=\Sooh2\Cdn\Alicdn::getInstance($key,$secret,$activated);
        if($cdn->refresh($url)){
            \Sooh2\Misc\Loger::getInstance()->app_warning('刷新cdn---'.$url."成功");
        }else{
            $errMsg=$cdn->getErrorMessage();
            \Sooh2\Misc\Loger::getInstance()->app_warning('刷新cdn---'.$url."失败------".$errMsg);
        }
    }



}