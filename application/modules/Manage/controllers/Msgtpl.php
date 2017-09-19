<?php
class MsgtplController extends \Rpt\Manage\ManageIniCtrl

{    //添加消息
    public function pageaddAction()
    {

        $arr_ini = $this->getinimsgAction();
        $info =[];
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('msgid','','消息编号')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('titletpl','','消息标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contenttpl','','模板内容')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('ways',$info['ways'],'消息通道')->initChecker(new \Sooh2\Valid\Str(true))->initOptions(
              [
               'push'=>$arr_ini['push'],
               'smsnotice'=>$arr_ini['smsnotice'],
                  'msg' =>  $arr_ini['msg']
              ]
                ));
        $this->appendRightsToForm($edtForm,'');


        if ($edtForm->isUserRequest($this->_request)) {
            $err = $edtForm->getErrors();
            if (!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'输入数据错误',implode(',',$err));
                return;
            }

            $changed = $this->recoverRightsInForm($edtForm->getInputs());

            $changed['ways']=implode(',',$changed['ways']);
            $msgId = $changed['msgid'];
            unset($changed['msgid']);
            $obj = \Prj\Model\Msgtpl::getCopy($msgId);
            $obj->load();
            if($obj->exists()){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '消息模板已存在');
                 return;
            }


            foreach ($changed as $k=>$v){
                $obj->setField($k,$v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'成功添加消息模板',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'添加失败（消息模板已存在？）');
                }
            }catch (Exception $ex){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'添加失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('消息模板');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }



    }

    protected function appendRightsToForm($edtForm,$rightsCurrent)
    {
        if(!is_array($rightsCurrent)){
            $defaultVal = explode(',', $rightsCurrent);
        }else{
            $defaultVal = $rightsCurrent;
        }

        $db = \Prj\Model\Msgtpl::getCopy(null)->dbWithTablename();

        $topmenu = $db->getCol($db->kvobjTable(),'distinct(titletpl)',null,'sort msgid');


    }

    protected function recoverRightsInForm($inputs)
    {

        $db = \Prj\Model\Msgtpl::getCopy(null)->dbWithTablename();
        $topmenu = $db->getCol($db->kvobjTable(),'distinct(titletpl)',null,'sort msgid');

        $all = array();
        foreach($topmenu as $s){
            $s = 'right_'.bin2hex($s);
            if(!empty($inputs[$s])){
                $all = array_merge($all,$inputs[$s]);

            }
            unset($inputs[$s]);
        }
        //$inputs['ways']=is_array($all)?implode(',',$all):$all;
        return $inputs;
    }
    protected function fmtIntro($arrOrObj)
    {
        if(is_array($arrOrObj)){
            return str_replace('"', '', \Sooh2\Util::toJsonSimple($arrOrObj));
        }else{
            return $arrOrObj->getField('msgid');
        }
    }
    //修改
    public function getinimsgAction(){
        $ini = \Sooh2\Misc\Ini::getInstance()->getIni('Messager');
        $ini_key = array();
        foreach($ini as $k=>$v){
            $ini_key[$k]=$v['name']? $v['name']:$k;

        }
        return $ini_key;
    }
    public function pageupdAction()
    {
        $info = [];
        $arr_ini = $this->getinimsgAction();

        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Msgtpl::getByBASE64($strpkey);
        $obj->load();

        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'记录没找到');
            return;
        }
        $tmp =$obj->getField('ways');
        $info = explode(',',$tmp);
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__',$strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('titletpl',$obj->getField('titletpl'),'消息标题'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contenttpl',$obj->getField('contenttpl'),'模板内容'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('ways',$info,'消息通道')->initChecker(new \Sooh2\Valid\Str(true))->initOptions(
                [
                    'push'=>$arr_ini['push'],
                    'smsnotice'=>$arr_ini['smsnotice'],
                    'msg'       =>  $arr_ini['msg']
                ]
            ));

        $this->appendRightsToForm($edtForm,$obj->getField('msgid'));

        if($edtForm->isUserRequest($this->_request)){//用户提交的请求
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $this->recoverRightsInForm($edtForm->getInputs());

            if(is_array($changed['ways'])){
                $changed['ways']=implode(',',$changed['ways']);
            }

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

        }else{//展示页面
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init($this->fmtIntro($obj));
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }
    
    public function listdataAction() {

        $db = \Prj\Model\Msgtpl::getCopy(null)->dbWithTablename();
        $arr = $db->getRecords($db->kvobjTable(),'*');
       $arr_ini = $this->getinimsgAction();

        foreach($arr as $i=>$r){
            foreach($arr_ini as $o=>$s){
                if($arr[$i]['ways']==$o){
                    $arr[$i]['ways']=$s;
                }else{
                    $arr[$i]['ways']=$arr[$i]['ways'];
                }
            }

            $pkey = array('msgid'=>$r['msgid']);

            $arr[$i]['op'] = $this->btnEdtInDatagrid($pkey);
        }
        $this->renderArray($arr);
    }


    //显示消息
    public function indexAction() {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('消息编号','msgid',170,'')
            ->addHeader('消息标题','titletpl',150,'')
            ->addHeader('模板内容','contenttpl',700,'')
            ->addHeader('消息通道','ways',100,'')
            ->addHeader('操作','op',50,'')
            ->initJsonDataUrl($uri->uri(null,'listdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('消息模板管理')->initStdBtn($uri->uri(null,'pageadd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }



}