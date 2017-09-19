<?php
/**
 * 手动发消息
 * By Hand
 */
class HandMsgController extends \Rpt\Manage\ManageIniCtrl
{

//    public function pageAddAction()
//    {
//        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
//        $edtForm->appendHiddenFirst('dept', 'skip');
//        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory('msgContent', '','内容'));
//        $this->appendRightsToForm($edtForm,'');
//
//        if($edtForm->isUserRequest($this->_request)){
//
//        }else{
//            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
//            $page->init('添加信息');
//            $page->initForm($edtForm);
//            $this->renderPage($page,true);
//        }
//
//    }
    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory('msgContent','','手动录入信息'));

        if($form->isUserRequest($this->_request)){
            $msgContent = $this->_request->get('msgContent');

            $msgContent = explode("\r\n",$msgContent);


            try{
                $flag = true;
                foreach($msgContent as $k => $v){
                    if(empty($v)) continue;
                    $obj = \Rpt\Manage\HandMail::getCopy(null);
                    $arr = explode("\t",$v);
                    $obj->setField('phone',trim(array_shift($arr)));
                    $obj->setField('msgContent',trim(array_shift($arr)));
                    $obj->setField('stateCode',0);
                    $ret = $obj->saveToDB();
                    if(!$ret) $flag=false;
                }
                if($flag){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'手动导入短信成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'手动导入短信失败，再次导入');
                }
            }catch (Exception $ex){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('手动发送短信')->initForm($form);
            $this->renderPage($page,true);
        }
    }
    /**
     * 
     * @param \Sooh2\BJUI\Forms\Edit $edtForm
     */
//    protected function appendRightsToForm($edtForm,$rightsCurrent)
//    {
//        if(is_array($rightsCurrent)){
//            $rightsCurrent = explode(',',$rightsCurrent);
//        }
//        $db = \Prj\Model\HandMsg::getCopy(null)->dbWithTablename();
//
//
//
//    }
    protected function recoverRightsInForm($inputs)
    {
        $db = \Rpt\Manage\ManageMenu::getCopy(null)->dbWithTablename();
        $topmenu = $db->getCol($db->kvobjTable(),'distinct(topmenu)',null,'sort menuid');
        $all = array();
        foreach($topmenu as $s){
            $s = 'right_'.bin2hex($s);
            if(!empty($inputs[$s])){
                $all = array_merge($all,$inputs[$s]);
                
            }
            unset($inputs[$s]);
        }
        $inputs['rights']=is_array($all)?implode(',',$all):$all;
        return $inputs;
    }
    /**
     * 获取用于说明对象的信息，每个controller需要自定义
     * @param type $arrOrObj
     * @return string
     */
    protected function fmtIntro($arrOrObj)
    {
        if(is_array($arrOrObj)){
            return str_replace('"', '', \Sooh2\Util::toJsonSimple($arrOrObj));
        }else{
            return $arrOrObj->getField('nickname');
        }
    }

    public function listdataAction() {
//        $db = Prj\Model\HandMsg::getCopy(null)->dbWithTablename();
//        $arr = $db->getCol('','phone,msgContent,stateCode','');
//
//        $arr = $db->getRecords();
//        foreach($arr as $i=>$r){
//            $pkey = array('cameFrom'=>$r['cameFrom'],'loginName'=>$r['loginName']);
//
//            $arr[$i]['op'] = $this->btnEdtInDatagrid($pkey).' ' . $this->btnDelInDatagrid($pkey);
//        }
//        $this->renderArray($arr);
        $sql ='SELECT * FROM jz_db.tb_hand_msg_0';
        $arr = \Prj\Model\User::query($sql);

        \Prj\Loger::out($arr);
        foreach($arr as $i=> $r){
            if($arr[$i]['stateCode']== 0){
                $arr[$i]['stateCode'] = '未发送';
            }elseif($arr[$i]['stateCode']==4){
                $arr[$i]['stateCode'] = '发送失败';
            }elseif($arr[$i]['stateCode'] ==8){
                $arr[$i]['stateCode'] = '发送成功';
            }
        }

        $this->renderArray($arr);
    }

    public function indexAction() {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
                ->addHeader('手机号', 'phone', 200, '')
                ->addHeader('信息内容', 'msgContent', 400, '')
                ->addHeader('状态', 'stateCode', 150, '')
                ->addHeader('操作', 'op', 100, '')
                ->initJsonDataUrl($uri->uri(null,'listdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
                ->init('手动发短信')->initStdBtn($uri->uri(null,'pageAdd'),'','',$uri->uri('','allSend'),'发送全部')
                ->initDatagrid($table);
        
        $this->renderPage($page);
    }
}
