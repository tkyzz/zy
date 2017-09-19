<?php
class MsgsentlogController extends \Rpt\Manage\ManageIniCtrl

{
    //添加消息
    public function pageaddAction()
    {
        $info =[];
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('msgid','','消息编号')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('titletpl','','消息标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contenttpl','','模板内容')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('ways',$info['ways'],'消息通道')->initChecker(new \Sooh2\Valid\Str(true))->initOptions(
              [
               'push'=>'推送',
               'smsnotice'=>'消息提醒',
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
            \Prj\Loger::out($changed);
            if($changed['ways'][0]=='001'){
               $changed['ways'][0]='push';
            }
            if($changed['ways'][1]=='002'){
                $changed['ways'][1]='smsnotice';
            }
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
    protected function exchangeWays()
    {
        $ini_key = [];
        $db = \Prj\Model\Msgtpl::getCopy(null)->dbWithTablename();
        $arr = $db->getRecords($db->kvobjTable(),'*');
        foreach($arr as $i=>$r){
            $ini = \Sooh2\Misc\Ini::getInstance()->getIni('Messager');
            foreach($ini as $k=>$v){
               $ini_key = key($k);
               if(in_array($r['ways'],$ini_key)){
                   $r['ways'] = $v['name'];
               }else{
                   $r['ways'] = $r['ways'];
               }
            }
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

        /*foreach($topmenu as $s){
            $options = $db->getPair($db->kvobjTable(),'ways','contenttpl',array('titletpl'=>$s),'sort msgid');
            if($rightsCurrent=='*'){
                $defaultVal = array_keys($options);
            }
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\MultiSelect::factory('right_'.bin2hex($s), $defaultVal)->initOptions($options));
        }*/
    }

    protected function recoverRightsInForm($inputs)
    {

        $db = \Prj\Model\Msgtpl::getCopy(null)->dbWithTablename();
        $topmenu = $db->getCol($db->kvobjTable(),'distinct(titletpl)',null,'sort msgid');
        //\Prj\Loger::out($topmenu);
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
    public function pageupdAction()
    {
        $info = [];
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Msgtpl::getByBASE64($strpkey);
        $obj->load();
        //\Prj\Loger::out($obj->load());
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'记录没找到');
            return;
        }
        $tmp =$obj->getField('ways');
        \Prj\Loger::out($tmp);
        $info = explode(',',$tmp);
        \Prj\Loger::out($info);
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__',$strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('titletpl',$obj->getField('titletpl'),'消息标题'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('contenttpl',$obj->getField('contenttpl'),'模板内容'))
            //->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('ways',$obj->getField('ways'),'消息通道'));
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('ways',$info,'消息通道')->initChecker(new \Sooh2\Valid\Str(true))->initOptions(
                [
                    'push'=>'推送',
                    'smsnoctice'=>'消息提醒',
                ]
            ));
        //\Prj\Loger::out($info);
        $this->appendRightsToForm($edtForm,$obj->getField('msgid'));

        if($edtForm->isUserRequest($this->_request)){//用户提交的请求
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $this->recoverRightsInForm($edtForm->getInputs());
            if($changed['ways'][0]=='001'){
                $changed['ways'][0]='push';
            }
            if($changed['ways'][1]=='002'){
                $changed['ways'][1]='smsnotice';
            }
            if(is_array($changed['ways'])){
                $changed['ways']=implode(',',$changed['ways']);
            }
            \Prj\Loger::out($changed);
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
        $ini_key=[];
        $db = \Prj\Model\Msgsentlog::getCopy(null)->dbWithTablename();
        $arr = $db->getRecords($db->kvobjTable(),'*');
       // \Prj\Loger::out($arr);
        foreach($arr as $i=>$r){

            //\Prj\Loger::out($r);
            $ini = \Sooh2\Misc\Ini::getInstance()->getIni('Messager');
            //\Prj\Loger::out($ini);
            /*foreach($ini as $k=>$v){

                $ini_key =key($k);
                if(!empty($ini_key) && $ini_key==$r['ways']){
                    $r['ways']=$ini_key['name'];
               }else{
                    $r['ways']=$r['ways'];
                }

            }*/

            $pkey = array('msgid'=>$r['msgid']);

            $arr[$i]['op'] = $this->btnEdtInDatagrid($pkey);
        }

        $this->renderArray($arr);
    }


    //列表显示
    public function indexAction() {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('日志编号','logid',100,'')
            ->addHeader('消息编号','evtid',100,'')
            ->addHeader('发送时间','ymdhis',200,'')
            ->addHeader('消息标题','msgtitle',100,'')
            ->addHeader('消息内容','msgcontent',300,'')
            ->addHeader('用户名','users',100,'')
            ->addHeader('消息通道','ways',100,'')
            ->addHeader('发送结果','sentret',100,'')
            ->addHeader('操作','op',50,'')
            ->initJsonDataUrl($uri->uri(null,'listdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('消息发送日志')->initStdBtn($uri->uri(null,'pageadd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }



}