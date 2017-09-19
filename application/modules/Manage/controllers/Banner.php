<?php
/*
 * Banner设置
 */

class BannerController extends \Rpt\Manage\Ctrl\BannerCtrl
{

    private $jz_db;
    private $optionsChannel;
    public function __construct(){
        list($this->jz_db,$this->jz_obj) = \Prj\GH\GHBanner::getCopy(null)->dbAndTbName();
        $this->optionsChannel = \Prj\Model\CmsChannel::getChannel();
        $this->optionsLink=array('xx'=>'链接&nbsp;&nbsp;','1'=>'跳转(只适用app)');
        $this->optionsTo=array('T1'=>'活期','T2'=>'定期','T3'=>'注册');
    }




    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('标题', 'title', 160, '')
            ->addHeader('渠道', 'channelOid', 160, '')
            ->addHeader('链接', 'linkUrl', 260, '')
            ->addHeader('图片', 'imageUrl', 180, '')
            ->addHeader('审核状态', 'approveStatus', 80, '')
            ->addHeader('上架状态', 'releaseStatus', 80, '')
            ->addHeader('上下架时间', 'releaseTime', 160, '')
            ->addHeader('操作', 'op', 300, '')
            ->initJsonDataUrl($uri->uri(null,'listBanner'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('banner列表')->initStdBtn($uri->uri(null,'bannerAdd'), $uri->uri(null,'bannerUpd'), 'del')
            ->initDatagrid($table);

        $this->renderPage($page);
    }


    /**
     *返回需要查询Banner的字段
     * @return array
     */
    public function listFieldsAction(){
        return array('oid','title','channelOid','linkUrl','imageUrl','approveStatus','releaseStatus','operator','releaseTime','releaseStatus');
    }




    /**
     *Banner列表数据获取
     */
    public function listBannerAction()
    {
        $tmp = \Prj\Model\Banner::getCopy(null);
        list($db, $tb) = $tmp->dbAndTbName();

        $fields = $this->listFieldsAction();
        $arr = $db->getRecords($tb, $fields, null, 'rsort createTime', null, 0);
        foreach($arr as $i=>$r){
            $pkey = array('oid'=>$r['oid']);
            if($r['imageUrl']!=''){
                $imgBaseUrl=\Sooh2\Misc\Ini::getInstance()->getIni('application.inistart.imgBaseUrl');
                $arr[$i]['imageUrl']="<img src='". $imgBaseUrl.$r['imageUrl']."'  height='70'/>";
            }
            $channelOid=explode(",",$r['channelOid']);
            $arr[$i]['channelOid']="";
            foreach($channelOid as $k=>$v){
                $arr[$i]['channelOid'].=$this->optionsChannel[$v].",";
            }
            $arr[$i]['channelOid']=substr($arr[$i]['channelOid'],0,-1);
            if($r['releaseStatus']!='ok'){
                $arr[$i]['op'] = $this->btnEdtInDatagrid($pkey).' ' . $this->btnDelInDatagrid($pkey);
            }
            if($r['approveStatus']=='toApprove'){
                $arr[$i]['approveStatus']="待审核";
                $arr[$i]['op'].=' '.$this->btnAprInDatagrid($pkey);
            }else if($r['approveStatus']=='pass'){
                $arr[$i]['approveStatus']="通过";
            }
            else if($r['approveStatus']=='refused'){
                $arr[$i]['approveStatus']="驳回";
            }
            if($r['releaseStatus']=='wait'){
                $arr[$i]['releaseStatus']="待上架";
                if($r['approveStatus']=='pass'){
                    $arr[$i]['op'].=' '.$this->btnReleaseDatagrid($pkey);
                }
            }else if($r['releaseStatus']=='ok'){
                $arr[$i]['releaseStatus']="已上架";
                $arr[$i]['op'].=' '.$this->btnUnreleaseDatagrid($pkey);
                $arr[$i]['op'].=' '.$this->btnDetailInDatagrid($pkey);
                $arr[$i]['op'].=' '.$this->btnSortDatagrid($pkey);
            }else if($r['releaseStatus']=='no'){
                $arr[$i]['releaseStatus']="已下架";
                if($r['approveStatus']=='pass'){
                    $arr[$i]['op'].=' '.$this->btnReleaseDatagrid($pkey);
                }
            }else{
                $arr[$i]['releaseStatus']="--";
            }
        }
        $this->renderArray($arr);
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



    /**
     *添加Banner
     */
    public function bannerAddAction(){
        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'imgUpload');
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('channelOid', '', '渠道' , '')->initChecker(new \Sooh2\Valid\Str(true, 0, 300))->initOptions($this->optionsChannel))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('title', '','标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('isLink', '','链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('linkUrl', '','链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('imageUrl', '','上传图片',$imgAction)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('toPage', '','跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo));
        if($edtForm->isUserRequest($this->_request)){
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $edtForm->getInputs();
            $channel=$changed['channelOid'];
            unset($changed['channelOid']);
            $changed['channelOid']=implode(",",$channel);
            $count=count($channel);
            //替换isLink值 默认值为0取不到
            if($changed['isLink']=='xx'){
                $changed['isLink']=0;
            }
            $changed['approveStatus']='toApprove';
            $changed['releaseStatus']='wait';
            $changed['operator']=md5($this->getMyId());
            $changed['createTime']=date("Y-m-d H:i:s");
            $changed['sorting'] = '1';
            $oid= substr(\Prj\Model\Banner::getBannerOid(),0,-1)."a";
            $obj = \Prj\Model\Banner::getCopy($oid);
            $obj->load();
            if($obj->exists()){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, 'banner已经存在');
                return;
            }

            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    //批量回填到国槐的banner表
                    foreach ($channel as $k=>$v) {
                        $length='-'.strlen($count);
                        $changed['oid'] = substr($oid, 0, $length) . $k;
                        $changed['channelOid']=$v;
                        $this->jz_db->addRecord($this->jz_obj, $changed);
                    }
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添加Banner：'.$changed['title'],true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加Banner失败（Banner已存在？）');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }


        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('添加Banner');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }


    /**
     * banner排序
     */
    public function bannerSortAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Banner::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }

        try{
            $sorting=$obj->getField('sorting');
        }catch(Exception $ex){
            $sorting="";
        }

        $oid=$obj->getField('oid');
        $channel=explode(",",$obj->getField('channelOid'));
        $count=count($channel);
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('sorting', $sorting,'排序')->initChecker(new \Sooh2\Valid\Str(true)));
        if($edtForm->isUserRequest($this->_request)) {//用户提交的请求
            $changed = $edtForm->getInputs();
            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
                try {
                    $ret = $obj->saveToDB();
                    if($ret){
                        $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid));
                        foreach ($channel as $k=>$v) {
                            $length='-'.strlen($count);
                            $oid2 = substr($oid, 0, $length) . $k;
                            $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid2));
                        }
                        \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '更新成功',false);
                        $this->_view->assign('closeCurrent',true);
                    }
                }catch(Exception $ex) {
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改排序失败:'.$ex->getMessage());
                }
            }
        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("修改Banne排序");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }

    
    /**
     * 修改Banner
     */
    public  function bannerUpdAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Banner::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $isLink=$obj->getField('isLink');
        if($isLink==0){
            $isLink='xx';
        }
        $oid=$obj->getField('oid');
        $channel=explode(",",$obj->getField('channelOid'));
        $count=count($channel);
        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'imgUpload');
        $readonly="readonly";
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('channelOid',  $channel, '渠道' , $readonly)->initChecker(new \Sooh2\Valid\Str(true, 0, 300))->initOptions($this->optionsChannel))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('title', $obj->getField('title'),'标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('isLink', $isLink,'链接类型')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsLink))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('linkUrl', $obj->getField('linkUrl'),'链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('imageUrl', '','上传图片',$imgAction)->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Radio::factory('toPage', $obj->getField('toPage'),'跳转页面')->initChecker(new \Sooh2\Valid\Str(false))->initOptions($this->optionsTo));

        if($edtForm->isUserRequest($this->_request)) {//用户提交的请求
            $changed = $edtForm->getInputs();
            unset($changed['channelOid']);
            //$changed['channelOid']=implode(",",$channel);

            if($changed['isLink']=='xx'){
                $changed['isLink']=0;
            }
            if($changed['imageUrl']==''){
                unset($changed['imageUrl']);
            }
            $changed['operator']=md5($this->getMyId());
            $changed['approveStatus']='toApprove';
            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    //批量更新到国槐表
//                    $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid));
//                    foreach ($channel as $k=>$v) {
//                        $length='-'.strlen($count);
//                        $oid2 = substr($oid, 0, $length) . $k;
//                        $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid2));
//                    }
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '更新成功');
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("修改Banner");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }


    /**
     *审核Banner
     */
    public function bannerAprAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Banner::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $oid=$obj->getField('oid');
        $channel=explode(",",$obj->getField('channelOid'));
        $count=count($channel);
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('approveStatus', 'pass','审核')->initChecker(new \Sooh2\Valid\Str(true))->initOptions(array('pass'=>'通过','refused'=>'驳回')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('remark', '','审核意见')->initChecker(new \Sooh2\Valid\Str(true)));
        if($edtForm->isUserRequest($this->_request)) {//用户提交的请求
            $changed = $edtForm->getInputs();
            $changed['approveTime'] = date("Y-m-d H:i:s");
            $changed['approveOpe'] = md5($this->getMyId());
            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    //批量更新到国槐表
//                    $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid));
//                    foreach ($channel as $k=>$v){
//                        $length='-'.strlen($count);
//                        $oid2 = substr($oid, 0, $length) . $k;
//                        $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid2));
//                    }
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '审核成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '审核失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '审核失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("审核Banner");
            $page->initForm($edtForm);
            $this->renderPage($page);}

    }


    /**
     *上架Banner
     */
    public function bannerReleaseAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Banner::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，下架失败');
            return;
        }
        $oid=$obj->getField('oid');
        $channel=explode(",",$obj->getField('channelOid'));
        $count=count($channel);
        $changed=array();
        $changed['releaseStatus'] = 'ok';
        //$changed['sorting'] = '1';
        $changed['releaseOpe'] = md5($this->getMyId());
        $changed['releaseTime'] = date("Y-m-d H:i:s");
        foreach($changed as $k=>$v){
            $obj->setField($k, $v);
        }
        try {
            $ret = $obj->saveToDB();
            if($ret){
                //批量更新到国槐表
//                $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid));
//                foreach ($channel as $k=>$v){
//                    $length='-'.strlen($count);
//                    $oid2 = substr($oid, 0, $length) . $k;
//                    $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid2));
//                }
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '上架成功');
            }
            else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '上架失败');
            }
        }catch (Exception $ex) {
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '上架失败:'.$ex->getMessage());
        }

    }


    /**
     *下架Banner
     */
    public function bannerUnreleaseAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Banner::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，下架失败');
            return;
        }
        $oid=$obj->getField('oid');
        $channel=explode(",",$obj->getField('channelOid'));
        $count=count($channel);
        $changed=array();
        $changed['releaseStatus'] = 'no';
        //$changed['sorting'] = '0';
        $changed['releaseOpe'] = md5($this->getMyId());
        $changed['releaseTime'] = date("Y-m-d H:i:s");
        foreach($changed as $k=>$v){
            $obj->setField($k, $v);
        }
        try {
            $ret = $obj->saveToDB();
            if($ret){
                //批量更新到国槐表
//                $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid));
//                foreach ($channel as $k=>$v){
//                    $length='-'.strlen($count);
//                    $oid2 = substr($oid, 0, $length) . $k;
//                    $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid2));
//                }
                \Sooh2\BJUI\Broker::getInstance()->setResultOk2($this->_view, '下架成功');
            }
            else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '下架失败');
            }
        }catch (Exception $ex) {
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '下架失败:'.$ex->getMessage());
        }
    }
    /**
     *删除Banner
     */
    public function bannerDelAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Banner::getByBASE64($strpkey);
        $obj->load();
        if($obj->exists()){
            $oid=$obj->getField('oid');
            $channel=explode(",",$obj->getField('channelOid'));
            $count=count($channel);
            $obj->delete();
            //批量从国槐表删除
//            $this->jz_db->delRecords($this->jz_obj,array('oid'=>$oid));
//            foreach ($channel as $k=>$v){
//                $length='-'.strlen($count);
//                $oid2 = substr($oid, 0, $length) . $k;
//                $this->jz_db->delRecords($this->jz_obj,array('oid'=>$oid2));
//            }
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功删除');
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，删除失败');
        }
    }

    /**
     *查看banner详情
     */
    public function bannerDetailAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Banner::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $oid=$obj->getField('oid');
        list($db,$tb)=$obj->dbAndTbName();
        $arr=$db->getRecord($tb,'*',array('oid'=>$oid),null);
        $channel=explode(',',$arr['channelOid']);
        $channelName="";
        foreach ($channel as $k=>$v){
            $channelName.=$this->optionsChannel[$v].",";
        }
        $channelName=substr($channelName,0,-1);
        echo "渠道：".$channelName."<hr/>";
        echo "标题：".$arr['title']."<hr/>";
        echo "链接：".$arr['linkUrl']."<hr/>";
        echo "图片：<img src='".$arr['imageUrl']."' width='200'/><hr/>";
        echo "内容：<br/>".$arr['linkHtml']."<hr/>";
        echo "审核状态：".$arr['approveStatus']."<hr/>";
        echo "上架状态：".$arr['releaseStatus']."<hr/>";
        echo "创建者：".$arr['operator']."<hr/>";
        echo "审核者：".$arr['approveOpe']."<hr/>";
        echo "上架者：".$arr['releaseOpe']."<hr/>";
        echo "审核时间：".$arr['approveTime']."<hr/>";
        echo "审核说明：".$arr['remark']."<hr/>";
        echo "上下架时间：".$arr['releaseTime']."<hr/>";
        echo "修改时间：".$arr['updateTime']."<hr/>";
        echo "创建时间：".$arr['createTime']."<hr/>";
    }


}

