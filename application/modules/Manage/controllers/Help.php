<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/8/25
 * Time: 15:27
 */

class HelpController extends \Rpt\Manage\ManageIniCtrl
{
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("帮助类别", "ret", 300)
            ->addHeader("操作", "op", 300)
//            ->addHeader("排序", "set", 300)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(null, "listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init("帮助配置")->initStdBtn($uri->uri('','pageAdd'))->initDatagrid($table);
        $this->renderPage($page);
    }


    public function listDataAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        if( !empty($strpkey) ){
            $obj = \Prj\Model\DataTmp::getByBASE64($strpkey);
            $obj->load();
            if (!$obj->exists()) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
                return;
            }
            $arr = $obj->getField('value');
            foreach($arr as $k=>$v){
                $content = explode('&&',$v['content']);
                if( $content[1] && strpos($content[1],'http')!=-1 ){
                    $arr[$k]['content'] = $content[0];
                    $arr[$k]['link'] = $content[1];
                }
            }
        }else{
            $arr = \Prj\Model\DataTmp::getRecords("*", ['type' => 'help']);
            foreach ($arr as $k => $v) {
                if( $k == 0 ){
                    $arr[$k]['set'] = $this->btnAjax(['oid' => $v['key'],'set'=>'down'] , 'move' , '下移' , '确定下移么？');
                }elseif( $k == count($arr)-1 ){
                    $arr[$k]['set'] = $this->btnAjax(['oid' => $v['key'],'set'=>'up'] , 'move' , '上移' , '确定上移么？');
                }else{
                    $arr[$k]['set'] =
                        $this->btnAjax(['oid' => $v['key'],'set'=>'up'] , 'move' , '上移' , '确定上移么？').' | '.
                        $this->btnAjax(['oid' => $v['key'],'set'=>'down'] , 'move' , '下移' , '确定下移么？');
                }
                $arr[$k]['op'] = $this->btnLookAtDatagrid(['`key`' => $v['key']]).' | '.$this->btnEdtInDatagrid(['`key`' => $v['key']]).' | '.
                    $this->btnDelInDatagrid(['`key`' => $v['key']]);
            }
        }

        $this->renderArray($arr);

    }

    public function infoAction(){
        $strpkey = $this->_request->get('__pkey__');
        $params = null;
        if( $strpkey ){
            $params = ['__pkey__'=>$strpkey];
        }
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("标题", "title", 300)
            ->addHeader("内容", "content", 300)
            ->addHeader("链接", "link", 300)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri($params, "listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init("查看帮助配置")->initDatagrid($table);
        $this->renderPage($page);
    }

    //更改配置项
    public function pageupdAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\DataTmp::getByBASE64($strpkey);
        $obj->load();
        if (!$obj->exists()) {
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $arr = $obj->getField('value');
        if( !is_array($arr) ){
            $arr = [];
        }
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey);
        for($i=0;$i<10;$i++){
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("title[]", $arr[$i]['title'], "标题".($i+1)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("rank[]", $arr[$i]['rank'], "排序"))
                ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("content[]", $arr[$i]['content'], "内容".($i+1)));
        }
        if ($edtForm->isUserRequest($this->_request)) {
            $title = $this->_request->get('title');
            $content = $this->_request->get('content');
            $rank = $this->_request->get('rank');
            //去除空值
            foreach($rank as $k=>$v){
                if( !$v ){
                    unset($rank[$k]);
                }
            }
            if( count(array_unique ( $rank )) != count( $rank ) ){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '排序值不能重复');
            }
            $arr = array();
            //组成数组
            for($i=0;$i<10;$i++){
                if( $title[$i] && $content[$i] ){
                    $rank[$i] = $rank[$i] ? $rank[$i] : 0;
                    $arr[] = ['title'=>$title[$i],'content'=>$content[$i],'rank'=>$rank[$i]];
                }else{
                    continue;
                }
            }
            // 按rank升序排
            foreach ($arr as $key => $val) {
                $tmp[$key] = $val['rank'];
            }
            array_multisort($tmp,SORT_ASC,$arr);
            $obj->setField('value',json_encode($arr));
            $ret = $obj->saveToDB();
            if ($ret) {
                \Prj\Bll\Help::flushHtml();
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功', true);
            } else {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '操作失败');
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('帮助配置[PS:带网址的内容直接用英文符号&&连接在后面]')->initForm($edtForm);
            $this->renderPage($page,true);
        }

    }

    /**
     * 新增帮助配置
     * @param type $arrOrObj
     * @return string
     */
    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('key', "", '类别英文名')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("ret", "", "类别标题")->initChecker(new \Sooh2\Valid\Str(true)));
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value", "","value")->initChecker(new \Sooh2\Valid\Str(true)));
        //判断是否提交
        if($form->isUserRequest($this->_request)){
            //获取表单数据
            $inputs = $form->getInputs();
            $obj = \Prj\Model\DataTmp::getCopy(null);
            $obj->setField("`type`","help");
            //生成一个唯一的key
            $obj->setField("`key`",uniqid());
            $obj->setField("`ret`",$inputs['ret']);
            $obj->setField("`value`",[]);
            try{
                //入库
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '添加配置成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加配置失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("新增帮助页面配置");
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    //删除配置
    public function delAction(){
        $obj = \Prj\Model\DataTmp::getByBASE64($this->_request->get('__pkey__'));
        $obj->load();
        if($obj->exists()){
            $key = reset($obj->pkey());
            //删除数据
            \Prj\Model\DataTmp::deleteOne(['`key`'=>$key]);
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功');
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '无此记录，操作失败');
        }
    }

    //排序移动操作
    public function moveAction(){
        \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功');
    }

    //查看内容
    protected function btnLookAtDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'info');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'查看\', mask:true,width:800, height:500}">查看</a>&nbsp;';
    }
    //修改内容
    protected function btnEdtInDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'pageupd');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'修改\', mask:true,width:750, height:768}">修改</a>&nbsp;';
    }
}