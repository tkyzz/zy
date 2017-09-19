<?php

/**
 * 管理员基本类，主要处理登入，登出
 *
 */
class ManagerController extends \Rpt\Manage\ManageCtrl {

    public function indexAction() {
        
        $managerId = $this->getMyId();
        if(!empty($managerId)){
            $uri = \Sooh2\Misc\Uri::getInstance();
            $acc = \Rpt\Manage\Manager::getByManagerId($managerId);
            $acc->load();
            $this->_view->assign('whoami',$acc->getField('nickname'));
            $data=array();
            $data['ip'] = $acc->getField('lastIP');
            $data['ymd'] = date('Y-m-d H:i:s', $acc->getField('lastYmd'));
            $this->_view->assign('lastAccess', $data);
            
            $rights=$acc->getField('rights');
            if($rights==='*'){
                $where = null;
            }else{
                $where=array('|'=>array());
                $r = explode(',',$rights);
                foreach($r as $s){
                    if(substr($s,-1)!='*'){
                        $where['|']['modulecontroller'][]=$s;
                    }else{
                        $where['|'][]='modulecontroller like \''.substr($s,0,-1).'%\'';
                    }
                }
            }
            $tmp = \Rpt\Manage\ManageMenu::getCopy(null);
            list($db,$tb) = $tmp->dbAndTbName();
            $topmenu = $db->getCol($tb,'distinct(topmenu)',$where,'sort menuid');
            $r = array();
            foreach($topmenu as $s){
                $r[$s]=$uri->uri(array('topmenu'=> bin2hex($s)),'menu');
            }
            $loginOut = \Sooh2\Misc\Uri::getInstance()->uri([],'loginOut');
            \Prj\Loger::outVal("loginOut",$loginOut);
            $this->_view->assign("loginOut",$loginOut);
            $this->_view->assign("changePwd",\Sooh2\Misc\Uri::getInstance()->uri([],'changePwd'));
            $this->_view->assign('topMenu',$r);
        }else{
            header('Location: '.\Sooh2\Misc\Uri::getInstance()->uri(null, 'login'));
            exit;
        }
    }

    public function menuAction() {
        $topmenu = hex2bin($this->_request->get('topmenu'));
        $managerId = $this->getMyId();
        if(!empty($managerId)){
            $uri = \Sooh2\Misc\Uri::getInstance();
            $acc = \Rpt\Manage\Manager::getByManagerId($managerId);
            $acc->load();
            $rights=$acc->getField('rights');
            $where = array('topmenu'=>$topmenu);
            if($rights!='*'){
                $where['modulecontroller']= explode(',', $rights);
            }
            $tmp = \Rpt\Manage\ManageMenu::getCopy(null);
            $db = $tmp->dbWithTablename();
            $menus = $db->getRecords($db->kvobjTable(),'sidemenu,modulecontroller,actionname',$where,'sort menuid');
        }else{
            $menus = array();
        }
        $uri = \Sooh2\Misc\Uri::getInstance();
        
        $sidemenu = array(
            array(
                'name'=>'[['.$topmenu.']]',
                'children'=>array(
//                    array("id"=>"base-navtab", "name"=>"Navtab", "target"=>"navtab", "url"=>"html/base/navtab.html"),
//                    array("id"=>"manage-managers", "name"=>"管理员一览", "target"=>"navtab", "url"=>$uri->uri(null,'index','managers')),
                )
            ),
        );
        foreach($menus as $r){
            list($m,$c) = explode('-',$r['modulecontroller']);
            $sidemenu[0]['children'][] = array("id"=>$r['modulecontroller'], "name"=>$r['sidemenu'], "target"=>"navtab", "url"=>$uri->uri(null,$r['actionname'],$c,$m));
        }
        $this->renderArray($sidemenu);
    }

    protected function _chkPwd($manager,$pwdInput)
    {
        $pass = $manager->getField('passwd');
        if(strlen($pass) != 32)$pass = md5($pass);
        return $pass==md5($pwdInput);
    }
    public function reloginAction()
    {
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('cameFrom', 'local');
        //$edtForm->items[] = \Sooh2\BJUI\FormItem\Select::factory('cameFrom', 'local','归属')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsCameFrom);
        $edtForm->items[] = \Sooh2\BJUI\FormItem\Text::factory('loginName', '','账号')->initChecker(new \Sooh2\Valid\Str(true));
        $edtForm->items[] = \Sooh2\BJUI\FormItem\Password::factory('passwd', '','密码')->initChecker(new \Sooh2\Valid\Str(true));

        if($edtForm->isUserRequest($this->_request)){
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }

            $changed = $edtForm->getInputs();
            $cameFrom = $changed['cameFrom'];
            $loginName = $changed['loginName'];
            
            $obj = \Rpt\Manage\Manager::getByLoginname($cameFrom, $loginName);
            $obj->load();

            if(!$obj->exists() || !$this->_chkPwd($obj, $changed['passwd'])){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '账号密码错误');
                return;
            }
            \Rpt\Session\Broker::sessionStart($obj->getField('loginName').'@'.$obj->getField('cameFrom'));
            try{
                $obj->saveToDB();
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '欢迎回来!!',true);
                
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '欢迎回来，更新最后登入时间失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\ReloginDlg::getInstance();
            $page->init('登入');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
        
    }
    public function loginAction() {
        $u = $this->_request->get('loginname');
        $p = $this->_request->get('passwd');
        $f = $this->_request->get('from', 'local');
        //$rember = $this->_request->get('remember', 0);

        
        if (!empty($u) && !empty($p)) {
            $acc = \Rpt\Manage\Manager::getByLoginname($f, $u);
            $acc->load();
            if ($acc->exists() && $this->_chkPwd($acc, $p)) {
                \Rpt\Session\Broker::sessionStart($acc->getField('loginName').'@'.$acc->getField('cameFrom'));

                $acc->setField('lastIP', \Sooh2\Util::remoteIP());
                $acc->setField('lastYmd', time());
                $acc->saveToDB();
                $this->saveLog();
                //error_log('>>>>>>>>>>>>>>> redir to >>> '.\Sooh2\Misc\Uri::getInstance()->uri(null, 'index'));
                header('Location: '.\Sooh2\Misc\Uri::getInstance()->uri(null, 'index'));
                exit;
            } else {
                $this->_view->assign('msgForLoginFailed','账户密码错误');
                //\Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '账户密码错误');
                //$this->returnError('用户名错误或密码错误或帐号已禁用');
                //$this->returnError(\Sooh2\Misc\Ini::getInstance()->getLang('manager.admin_error_or_password_error_or_account_forbidden'));
            }
        }
        $this->_view->assign('errTrans', $this->_request->get('errTrans'));
    }


    public function saveLog(){
        $fields = array(
            'ymd'=>date('Ymd',time()),'his'=>date('His',time()),'managerid'=> \Rpt\Session\Broker::getManagerId(),
            'objtable'=>"SESSION",'chgcontent'=>"登录",'rowVersion'=>1
        );
        \Prj\Model\ManageLog::saveOne($fields);
    }


    public function loginOutAction(){

//        $manager = \Rpt\Session\Broker::getManagerId();

        $sessionId = $_COOKIE['ManagerSessId'];

        setcookie("ManagerSessId","",-1,'/');

        $obj = \Rpt\Session\Data::getCopy($sessionId);
        $obj->load();
        $obj->setField("dtExpire",time()-3600);
        $obj->saveToDB();
        echo "<script language=JavaScript> location.replace(location.href);</script>";
        exit;

    }



    public function changePwdAction(){
        $managerId = $this->getMyId();
        \Prj\Loger::outVal("massd",$managerId);
        $manageInfo = explode("@",$managerId);
        \Prj\Loger::outVal("dffggg",$manageInfo);
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('oldPwd', "",'旧密码')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("newPwd","","新密码")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("newPwd2","","确认密码")->initChecker(new Sooh2\Valid\Str(true)));
        if($edtForm->isUserRequest($this->_request)){
            $oldPwd = $this->_request->get("oldPwd");
            $newPwd = $this->_request->get("newPwd");
            $newPwd2 = $this->_request->get("newPwd2");
            if(empty($oldPwd)) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,"旧密码不能为空");
            if(empty($newPwd)) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,"新密码不能为空");
            if(empty($newPwd2)) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,"确认密码不能为空");
            if($newPwd2!=$newPwd) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,"两次新密码输入不一致");
            $acc = \Rpt\Manage\Manager::getByLoginname($manageInfo[1],$manageInfo[0]);
            $acc->load();

            if ($acc->exists() && $this->_chkPwd($acc,$oldPwd )){
                $acc->setField("passwd",$newPwd);
                $acc->saveToDB();
                $obj =  \Rpt\Session\Data::getCopy(true);

                list($db,$tb) = $obj->dbAndTbName();
                $ret = $db->updRecords($tb,['dtExpire'=>time()],['userId'=>$managerId]);
                \Prj\Loger::outVal("dfhhhhh",$ret);

                setcookie("ManagerSessId","",-1,'/');
                echo "<script language=JavaScript> location.replace(location.href);</script>";return;
//                return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,"修改密码成功，请刷新重新登录",true);
            }else{
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,"原密码输入错误");
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('修改密码');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }






}
