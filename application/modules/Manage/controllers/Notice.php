<?php

class NoticeController extends \Rpt\Manage\Ctrl\NoticeCtrl
{

    private $jz_db;
    private $optionsChannel;
    protected $optionsSub = array('New'=>'New','Hot'=>'Hot','无'=>'无');
    public function __construct(){
        list($this->jz_db,$this->jz_obj) = \Prj\GH\GHNotice::getCopy(null)->dbAndTbName();
        $this->optionsChannel = \Prj\Model\CmsChannel::getChannel();
    }


    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('标题', 'title', 460, '')
            ->addHeader('角标', 'subscript', 60, '')
            ->addHeader('首页推荐', 'page', 80, '')
            ->addHeader('置顶', 'top', 60, '')
            ->addHeader('发布来源', 'sourceFrom', 80, '')
            ->addHeader('审核状态', 'approveStatus', 80, '')
            ->addHeader('上架状态', 'releaseStatus', 80, '')
            ->addHeader('上架时间', 'onShelfTime', 140, '')
            ->addHeader('操作', 'op', 250, '')
            ->initJsonDataUrl($uri->uri(null,'listNotice'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('公告列表')->initStdBtn($uri->uri(null,'noticeAdd'), $uri->uri(null,'noticeUpd'), 'del')
            ->initDatagrid($table);

        $this->renderPage($page);
    }

    public function testAction(){
        $this->refCdnAction('https://www.zhangyuelicai.com/information/mtjson/?{}');
    }




    /**
     *返回需要查询的字段
     * @return array
     */
    public function listFieldsAction(){
        return array('oid','title','subscript','page','top','sourceFrom','approveStatus','releaseStatus','onShelfTime');
    }


   /**
    * 公告列表展示
    */
    public function listNoticeAction(){
        $tmp = \Prj\Model\Notice::getCopy(null);
        $where=array('channelOid'=>'000000005a83152e015a894d28b80000');
        list($db,$tb) = $tmp->dbAndTbName();
        $fields=$this->listFieldsAction();
        $arr=$db->getRecords($tb,$fields,$where,'rsort releaseStatus rsort updateTime',null,0);
        foreach($arr as $i=>$r){
            $pkey = array('oid'=>$r['oid']);
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
            }else if($r['releaseStatus']=='no'){
                $arr[$i]['releaseStatus']="已下架";
                if($r['approveStatus']=='pass'){
                    $arr[$i]['op'].=' '.$this->btnReleaseDatagrid($pkey);
                }
            }else{
                $arr[$i]['releaseStatus']="--";
            }
            if($r['page']=='no'){
                $arr[$i]['page']="否";
            }else if($r['page']=='is'){
                $arr[$i]['page']="是";
            }
            if($r['top']=='2'){
                $arr[$i]['top']="否";
            }else if($r['top']=='1'){
                $arr[$i]['top']="是";
            }

            $arr[$i]['op'].=' '.$this->btnDetailInDatagrid($pkey);
        }
        $this->renderArray($arr);
    }

    /**
     * 添加公告
     */
    public function noticeAddAction(){
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('title', '','标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('subscript', 'New','角标')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsSub))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('linkUrl', '','链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('sourceFrom', '','来源')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Editor::factory('linkHtml', '','内容')->initChecker(new \Sooh2\Valid\Str(false)));
        if($edtForm->isUserRequest($this->_request)){
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $edtForm->getInputs();
            $changed['channelOid']='000000005a83152e015a894d28b80000';
            $changed['approveStatus']='toApprove';
            $changed['releaseStatus']='wait';
            $changed['operator']=md5($this->getMyId());
            $changed['page']='no';
            $changed['top']='2';
            $changed['createTime']=date("Y-m-d H:i:s");
            $oid= substr(\Prj\Model\Notice::getNoticeOid(),0,-1).'1';
            $oid2= substr($oid,0,-1).'2';
            $count=\Prj\Model\Notice::getNoticeReleaseCount();
            if($count>=20){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '上架大于20条公告，请先删除后再添加');
                return;
            }
            $obj = \Prj\Model\Notice::getCopy($oid);
            $obj->load();
            if($obj->exists()){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '公告已经存在');
                return;
            }

            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    $changed['oid']=$oid;
                    $this->jz_db->addRecord($this->jz_obj,$changed);
                    $changed2=$changed;
                    $changed2['oid']=$oid2;
                    $changed2['channelOid']='000000005a83152e015a894dfa380001';
                    $this->jz_db->addRecord($this->jz_obj,$changed2);
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添加公告：'.$changed['title'],true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加公告失败（公告已存在？）');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }
        }else {

            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('添加公告');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }


    /**
     *修改更新公告
     */
    public function noticeUpdAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('title', $obj->getField('title'),'标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('subscript', $obj->getField('subscript'),'角标')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsSub))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('linkUrl', $obj->getField('linkUrl'),'链接地址')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('sourceFrom', $obj->getField('sourceFrom'),'来源')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Editor::factory('linkHtml', $obj->getField('linkHtml'),'内容')->initChecker(new \Sooh2\Valid\Str(false)));

        if($edtForm->isUserRequest($this->_request)) {//用户提交的请求
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $edtForm->getInputs();
            $changed['operator']=md5($this->getMyId());
            $changed['approveStatus']='toApprove';
           /*
                $approveStatus=$obj->getField('approveStatus');
               if($approveStatus=='refused'){
                    $changed['approveStatus']='toApprove';
                }
           */
            //$changed['approveStatus']='toApprove';
            //$changed['releaseStatus']='wait';
            //$changed['page']='no';
            //$changed['top']='2';
            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }

            try{
                $ret = $obj->saveToDB();
                if($ret){
                    $oid=$obj->getField('oid');
                    $oid2=substr($oid,0,-1).'2';
                    $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid));
                    $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid2));
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '更新成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败:'.$ex->getMessage());
            }

        }else {
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("修改公告");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }




    /**
     *审核公告
     */
    public function noticeAprAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('approveStatus', 'pass','审核')->initChecker(new \Sooh2\Valid\Str(true))->initOptions(array('pass'=>'通过','refused'=>'驳回')))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('remark', '','审核意见')->initChecker(new \Sooh2\Valid\Str(true)));
        if($edtForm->isUserRequest($this->_request)) {//用户提交的请求
            //todo
            $changed = $edtForm->getInputs();
            $changed['approveTime'] = date("Y-m-d H:i:s");
            $changed['approveOpe'] = md5($this->getMyId());
            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    $oid=$obj->getField('oid');
                    $oid2=substr($oid,0,-1).'2';
                    $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid));
                    $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid2));
                   /*if($changed['releaseStatus']=='ok') {
                        $this->noticeCreateAction($strpkey);
                    }*/
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '审核成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '审核失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '审核失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("审核公告");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }


    /**
     *删除公告
     */
    public function noticeDelAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if($obj->exists()){
            $obj->delete();
            $oid=$obj->getField('oid');
            $oid2=substr($oid,0,-1).'2';
            $this->jz_db->delRecords($this->jz_obj,array('oid'=>$oid));
            $this->jz_db->delRecords($this->jz_obj,array('oid'=>$oid2));
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功删除');
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，删除失败');
        }
    }

    /**
     *上架公告
     */
    public function noticeReleaseAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，下架失败');
            return;
        }
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('onShelfTime', '','上架时间')->initChecker(new \Sooh2\Valid\Str(true)));
        if($edtForm->isUserRequest($this->_request)) {
            $changed = $edtForm->getInputs();
            $changed['releaseStatus'] = 'ok';
            $changed['releaseOpe'] = md5($this->getMyId());
            $changed['releaseTime'] = date("Y-m-d H:i:s");
            $changed['page'] = 'is';
            $changed['top'] = '1';
            foreach ($changed as $k => $v) {
                $obj->setField($k, $v);
            }
            try {
                $ret = $obj->saveToDB();
                if ($ret) {
                    $oid = $obj->getField('oid');
                    $oid2 = substr($oid, 0, -1) . '2';
                    $this->jz_db->updRecords($this->jz_obj, $changed, array('oid' => $oid));
                    $this->jz_db->updRecords($this->jz_obj, $changed, array('oid' => $oid2));
                    $this->noticeCreateAction($strpkey);
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '上架成功',true);
                } else {
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '上架失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '上架失败:' . $ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("上架公告");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }



    /**
     *下架公告
     */
    public function noticeUnreleaseAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，下架失败');
            return;
        }
        $title=$obj->getField('title');
        $changed=array();
        $changed['releaseStatus'] = 'no';
        $changed['releaseOpe'] =md5($this->getMyId());
        foreach($changed as $k=>$v){
            $obj->setField($k, $v);
        }
        try {
            $ret = $obj->saveToDB();
            if($ret){
                $oid=$obj->getField('oid');
                $oid2=substr($oid,0,-1).'2';
                $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid));
                $this->jz_db->updRecords($this->jz_obj,$changed,array('oid'=>$oid2));
                $this->listCreateAction();
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '下架成功');
            }
            else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '下架失败');
            }
        }catch (Exception $ex) {
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '下架失败:'.$ex->getMessage());
        }

    }


    /**
     *查看公告详细
     */

    public function noticeDetailAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $oid=$obj->getField('oid');
        list($db,$tb)=$obj->dbAndTbName();
        $arr=$db->getRecord($tb,'*',array('oid'=>$oid),null);
        echo "标题：".$arr['title']."<hr/>";
        echo "链接：".$arr['linkUrl']."<hr/>";
        echo "角标：".$arr['subscript']."<hr/>";
        echo "首页推荐：".$arr['page']."<hr/>";
        echo "置顶：".$arr['top']."<hr/>";
        echo "内容：<br/>".$arr['linkHtml']."<hr/>";
        echo "审核状态：".$arr['approveStatus']."<hr/>";
        echo "上架状态：".$arr['releaseStatus']."<hr/>";
        echo "创建者：".$arr['operator']."<hr/>";
        echo "审核者：".$arr['approveOpe']."<hr/>";
        echo "上架者：".$arr['releaseOpe']."<hr/>";
        echo "审核时间：".$arr['approveTime']."<hr/>";
        echo "审核说明：".$arr['remark']."<hr/>";
        echo "上架审核时间：".$arr['releaseTime']."<hr/>";
        echo "上架时间：".$arr['onShelfTime']."<hr/>";
        echo "修改时间：".$arr['updateTime']."<hr/>";
        echo "创建时间：".$arr['createTime']."<hr/>";

    }

    /**
     *查看生产的公告静态页面url
     */
    public function noticeSeeAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $urlPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.urlpath');
        $oid=$obj->getField('oid');
        echo "<a href='{$urlPath}/pc/{$oid}.html' target='_blank'>pc公告详情页</a>";
        echo "<hr/>";
        echo "<a href='{$urlPath}/wap/{$oid}.html' target='_blank'>wap公告详情页</a>";
        echo "<hr/>";
        echo "<a href='{$urlPath}/app/{$oid}.html' target='_blank'>app公告详情页</a>";
        echo "<hr/>";
        echo "<a href='{$urlPath}/pc/index.html' target='_blank'>pc列表地址</a>";
        echo "<hr/>";
        echo "<a href='{$urlPath}/wap/index.html' target='_blank'>wap列表地址</a>";
        echo "<hr/>";
        echo "<a href='{$urlPath}/app/index.json' target='_blank'>app列表地址</a>";
        echo "<hr/>";
        echo "<a href='{$urlPath}/pc/index_top.json' target='_blank'>pc 首页json地址</a>";
        echo "<hr/>";
        echo "<a href='{$urlPath}/wap/index_top.json' target='_blank'>wap 首页json地址</a>";
        echo "<hr/>";
        echo "<a href='{$urlPath}/app/index_top.json' target='_blank'>app 首页json地址</a>";
        echo "<hr/>";
    }


    /**
     * 生成详情页
     */
    public function noticeCreateAction($strpkey){
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $oid=$data['oid']=$obj->getField('oid');
        $data['title']=$obj->getField('title');
        $data['onShelfTime']=$obj->getField('onShelfTime');
        $data['linkHtml']=$obj->getField('linkHtml');
        $file=array();
        $file[]=$this->getFile('content',$oid,'pc');
        $file[]=$this->getFile('content',$oid,'wap');
        $file[]=$this->getFile('content',$oid,'app');
        $template=array();
        $template[]=$this->getTemplate('content','pc');
        $template[]=$this->getTemplate('content','wap');
        $template[]=$this->getTemplate('content','app');

        foreach($file as $key=>$value){
            ob_start();
            include($template[$key]);
            $this->createHtml($value[0]);
            $this->refCdnAction('https://www.zhangyuelicai.com/'.$value[1]);
        }
        $this->listCreateAction();
    }


    /**
     * 生成列表页
     */
    function listCreateAction(){
        $where=array('releaseStatus'=>'ok','channelOid'=>'000000005a83152e015a894d28b80000');
        $pageSize=12;
        $obj = \Prj\Model\Notice::getCopy();
        list($db,$tb)=$obj->dbAndTbName();
        $count=$db->getRecordCount($tb,$where);
        $totalPage=ceil($count/$pageSize) ? ceil($count/$pageSize) : 1;
        for($i=1;$i<=$totalPage;$i++){
            $rsFrom=($i-1)*$pageSize;
            $data=$db->getRecords($tb,'oid,title,linkUrl,linkHtml,subscript,sourceFrom,page,top,releaseTime,onShelfTime',$where,'rsort onShelfTime rsort updateTime',$pageSize,$rsFrom);
            //去除重复title数据
            // $data=$db-
            $total=count($data);
            foreach($data as $k=>$v){
                $data[$k]['releaseTime']=date("Y-m-d",strtotime($v['releaseTime']));
            }
            $dataCode=array('errorCode'=>0,'errorMessage'=>'','total'=>$total,'pages'=>1);
            $dataCode['rows']=$data;
            $dataJson=json_encode($dataCode);//toJsonSimple
            $pageInfo=$this->pageAction($i,$totalPage);
            $file=array();
            $template=array();
            $file[]=$this->getFile('list',$i,'pc');
            $file[]=$this->getFile('list',$i,'wap');
            $file[]=$this->getFile('list',$i,'app');
            $template[]=$this->getTemplate('list','pc');
            $template[]=$this->getTemplate('list','wap');
            $template[]=$this->getTemplate('list','app');
            foreach($file as $key=>$value){
                ob_start();
                include($template[$key]);
                $this->createHtml($value[0]);
                $this->refCdnAction('https://www.zhangyuelicai.com/'.$value[1]);

            }
        }
        $this->indexTopJsonAction();
    }




    /**
     * 生成首页所需json
     */
    public function indexTopJsonAction(){
        $where=array('releaseStatus'=>'ok','channelOid'=>'000000005a83152e015a894d28b80000');
        $obj = \Prj\Model\Notice::getCopy();
        list($db,$tb)=$obj->dbAndTbName();
        $data=$db->getRecords($tb,'oid,title,linkUrl,linkHtml,subscript,sourceFrom,page,top,releaseTime',$where,'rsort createTime',null,null);
        foreach ($data as $k=>$v){
            $data[$k]['releaseTime'] = date('Y-m-d',strtotime($v['releaseTime']));
        }
        //$rs=$db->getRecords($tb,'oid,title,subscript,sourceFrom,releaseTime',$where,'rsort createTime',null,null);
        $urlPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.urlpath');
        $total=count($data);
        $dataCode2=$dataCode=array('errorCode'=>0,'errorMessage'=>'','total'=>$total,'pages'=>0);
        $dataCode['rows']=$data;
        $dataJson=json_encode($dataCode);
        //$data=\Sooh2\Util::toJsonSimple($datars);
        $file=array();
        $template=array();
        $type=array();
        $file[]=$this->getFile('json','','pc');
        $file[]=$this->getFile('json','','wap');
        $file[]=$this->getFile('json','','app');
        $template[]=$this->getTemplate('json','pc');
        $template[]=$this->getTemplate('json','wap');
        $template[]=$this->getTemplate('json','app');
        $type[]='pc';
        $type[]='wap';
        $type[]='app';
        foreach($file as $key=>$value){
//            if($type[$key]=='wap'||$type[$key]=='app'){
                $rsarr=$data;
                foreach($rsarr as $kk=>$arr){
                    $rsarr[$kk]['linkHtml']="";
                    $rsarr[$kk]['url']='/'.$urlPath.'/'.$type[$key].'/'.$arr['oid'].'.html';
                }
                $dataCode2['rows']=$rsarr;
                $dataJson=json_encode($dataCode2);
//            }
            ob_start();
            include($template[$key]);
            $this->createHtml($value[0]);
            $this->refCdnAction('https://www.zhangyuelicai.com/'.$value[1]);
        }
    }

    /**
     *批量生成公告详情静态页面
     */
    public function batchShowAction(){
        $where=array('releaseStatus'=>'ok');
        $obj = \Prj\Model\Notice::getCopy();
        list($db,$tb)=$obj->dbAndTbName();

        $rs=$db->getRecords($tb,'*',$where,'rsort createTime',null,null);
            foreach($rs as $vdata){
                $data=$vdata;
                $file=array();
                $file[]=$this->getFile('content',$data['oid'],'pc');
                $file[]=$this->getFile('content',$data['oid'],'wap');
                $file[]=$this->getFile('content',$data['oid'],'app');
                $template=array();
                $template[]=$this->getTemplate('content','pc');
                $template[]=$this->getTemplate('content','wap');
                $template[]=$this->getTemplate('content','app');
                foreach($file as $key=>$value){
                    ob_start();
                    include($template[$key]);
                    $this->createHtml($value[0]);
                    $this->refCdnAction('https://www.zhangyuelicai.com/'.$value[1]);
                }
            }

    }


    /**
     *重新生成详情页
     */
    public function noticeCreAgianAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Notice::getByBASE64($strpkey);
        $obj->load();
        if($obj->exists()){
            $oid=$data['oid']=$obj->getField('oid');
            $data['title']=$obj->getField('title');
            $data['onShelfTime']=$obj->getField('onShelfTime');
            $data['linkHtml']=$obj->getField('linkHtml');
            $file=array();
            $file[]=$this->getFile('content',$oid,'pc');
            $file[]=$this->getFile('content',$oid,'wap');
            $file[]=$this->getFile('content',$oid,'app');
            $template=array();
            $template[]=$this->getTemplate('content','pc');
            $template[]=$this->getTemplate('content','wap');
            $template[]=$this->getTemplate('content','app');
            foreach($file as $key=>$value){
                ob_start();
                include($template[$key]);
                $this->createHtml($value[0]);
                $this->refCdnAction('https://www.zhangyuelicai.com/'.$value[1]);
                ob_end_clean();
            }
            $this->listCreateAction();

            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '重新生成成功');
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，重新生成失败');
        }
    }





    /**
     * 列表页分页
     * @param int $current
     * @param int $total
     */
    public function pageAction($current,$total){
        $pageInfo="共".$total."页";
        if($total==1){
            //return   $pageInfo.="<a href='index.html'>首页</a>";
            return $pageInfo;
        }
        if($total==2){
            if($current<$total){
                $pageInfo.="<a>首页</a>";
                $pageInfo.="<a href='list_2.html'>下一页</a>";
                return $pageInfo;
            }else{
                $pageInfo.="<a href='index.html'>上一页</a>";
                $pageInfo.="<a>末页</a>";
                return $pageInfo;
            }
        }
        if($total>2){
            if($current==1){
                $pageInfo.="<a>首页</a>";
                $pageInfo.="<a href='list_2.html'>下一页</a>";
                return $pageInfo;
            }else{
                if($current<$total){
                    $pre=$current-1;
                    $next=$current+1;
                    $pageInfo.="<a href='list_{$pre}.html'>上一页</a>";
                    $pageInfo.="<a>$current</a>";
                    $pageInfo.="<a href='list_{$next}.html'>下一页</a>";
                    return $pageInfo;
                }
            }
        }

    }




    /**
     * 获取生成的页面模板地址
     * @param string $type
     * @param string $channel
     * @return string
     */
    public function getTemplate($type,$channel){
        $templatePath=\Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.templatepath');
        switch($channel){
            case 'pc':
                if($type=='list'){
                    return $templatePath.'/pc/pc_content_list.html';
                }
                if($type=='content'){
                    return $templatePath.'/pc/pc_content.html';
                }
                if($type=='json'){
                    return $templatePath.'/pc/pc_index_json.html';
                }
                break;
            case 'wap':
                if($type=='list'){
                    return $templatePath.'/wap/wap_content_list.html';
                }
                if($type=='content'){
                    return $templatePath.'/wap/wap_content.html';
                }
                if($type=='json'){
                    return $templatePath.'/wap/wap_index_json.html';
                }
                break;
            case 'app':
                if($type=='list'){
                    return $templatePath.'/app/app_content_list.html';
                }
                if($type=='content'){
                    return $templatePath.'/app/app_content.html';
                }
                if($type=='json'){
                    return $templatePath.'/app/app_index_json.html';
                }
                break;
            default:
                break;
        }

    }

    /**
     *获取生成文件的文件路径
     *@param string $type
     *@param string $filename
     *@param string $channel
     *@return string
     */
    public function getFile($type,$filename,$channel){
        $createPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path');
        $urlPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.urlpath');
        if($type=='list'){
            if($filename=='1'){
                if($channel=='app'){
                    $filePath[0]=$createPath.'/'.$channel.'/index.html';
                    $filePath[1]=$urlPath.'/'.$channel.'/index.html';
                    return $filePath;
                }
                $filePath[0]=$createPath.'/'.$channel.'/index.html';
                $filePath[1]=$urlPath.'/'.$channel.'/index.html';
                return $filePath;
            }else{
                $filePath[0]=$createPath.'/'.$channel.'/list_'.$filename.'.html';
                $filePath[1]=$urlPath.'/'.$channel.'/list_'.$filename.'.html';
                return $filePath;
            }

        }
        else if($type=='content'){
            $filePath[0]=$createPath.'/'.$channel.'/'.$filename.'.html';
            $filePath[1]=$urlPath.'/'.$channel.'/'.$filename.'.html';
            return $filePath;
        }
        else if($type=='json'){
            $filePath[0]=$createPath.'/'.$channel.'/index_top.json';
            $filePath[1]=$urlPath.'/'.$channel.'/index_top.json';
            return $filePath;
        }

    }


    /**
     * 生成并且刷新静态文件cdn缓存
     * @param string $path
     * @param string $url
     */
    public function createAndRef($path,$url){
        if($this->createHtml($path)){
            \Sooh2\Misc\Loger::getInstance()->app_trace('生成文件'.$url."成功");
            $this->refCdnAction($url);
        }else{
            \Sooh2\Misc\Loger::getInstance()->app_warning('生成文件'.$url."失败");
        }
    }


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


    /**
     * 生成静态文件
     * @param  string $file 文件路径
     * @return boolean
     */

    public function createHtml($file){
        $data = ob_get_contents();
        ob_clean();
        $dir = dirname($file);
        if(!is_dir($dir)) {
            mkdir($dir, 0775,1);
        }

        $strLen = file_put_contents($file, $data);
        @chmod($file,0775);
        if(!is_writable($file)) {
            echo "文件没有写入权限";
        }
        return $strLen;
    }

}
?>