<?php
/**
 * 管理员一览
 * By Hand
 */
class ManagersController extends \Rpt\Manage\ManageIniCtrl
{
    protected $optionsCameFrom = array('local'=>'掌悦');
    public function pageaddAction()
    {
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('dept', 'skip');
        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('cameFrom', 'local','归属')->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->optionsCameFrom))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('loginName', '','账号')->initChecker(new \Sooh2\Valid\Str(true)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('passwd', '','密码')->initChecker(new \Sooh2\Valid\Str(true)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('nickname', '','昵称')->initChecker(new \Sooh2\Valid\Str(true)));
        $this->appendRightsToForm($edtForm,'');

        if($edtForm->isUserRequest($this->_request)){
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }

            $changed = $this->recoverRightsInForm($edtForm->getInputs());
            $cameFrom = $changed['cameFrom'];
            $loginName = $changed['loginName'];
            unset($changed['cameFrom'], $changed['loginName']);
            $obj = \Rpt\Manage\Manager::getByLoginname($cameFrom, $loginName);
            $obj->load();
            if($obj->exists()){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '用户已经存在');
                return;
            }
            foreach($changed as $k=>$v){
                $obj->setField($k, $v);
            }
            try{
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添加管理员：'.$loginName,true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败（管理员已存在？）');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('添加管理员');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }
    /**
     * 
     * @param \Sooh2\BJUI\Forms\Edit $edtForm
     */
    protected function appendRightsToForm($edtForm,$rightsCurrent)
    {
        if(!is_array($rightsCurrent)){
            $defaultVal = explode(',', $rightsCurrent);
        }else{
            $defaultVal = $rightsCurrent;
        }
        
        $db = \Rpt\Manage\ManageMenu::getCopy(null)->dbWithTablename();
        
        $topmenu = $db->getCol($db->kvobjTable(),'distinct(topmenu)',null,'sort menuid');
        foreach($topmenu as $s){
            $options = $db->getPair($db->kvobjTable(),'modulecontroller','sidemenu',array('topmenu'=>$s),'sort menuid');
//            print_r($options);exit;
            if($rightsCurrent=='*'){
                $defaultVal = array_keys($options);
            }
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\MultiSelect::factory('right_'.bin2hex($s), $defaultVal, '权限:'.$s)->initOptions($options));
        }
    }
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
    public function pageupdAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Rpt\Manage\Manager::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
            
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Show::factory('cameFrom', $obj->getField('cameFrom'),'归属')->initOptions($this->optionsCameFrom))
            ->addFormItem(\Sooh2\BJUI\FormItem\Show::factory('loginName', $obj->getField('loginName'),'账号'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('nickname', $obj->getField('nickname'),'昵称')->initChecker(new \Sooh2\Valid\Str(true,1,12)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('dtForbidden', $obj->getField('dtForbidden'),'状态')->initOptions(array(0=>'正常',2147483647=>'禁用')));
        $this->appendRightsToForm($edtForm,$obj->getField('rights'));
        if($edtForm->isUserRequest($this->_request)){//用户提交的请求
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $this->recoverRightsInForm($edtForm->getInputs());
            unset($changed['cameFrom'], $changed['loginName']);
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
        $db = \Rpt\Manage\ManageMenu::getCopy(null)->dbWithTablename();
        $this->menuRights = $db->getPair($db->kvobjTable(), 'modulecontroller', 'concat(topmenu,\'.\',sidemenu)');
        list($db2,$tb) = $this->manager->dbAndTbName();
        $arr = $db2->getRecords($tb,'cameFrom,dept,rights,loginName,nickname,lastIP,lastYmd');
        foreach($arr as $i=>$r){
            //时间戳处理
            $arr[$i]['lastYmd'] = $r['lastYmd']?date('Y-m-d H:i:s',$arr[$i]['lastYmd']):'无';
            $pkey = array('cameFrom'=>$r['cameFrom'],'loginName'=>$r['loginName']);
            if ($r['rights']=='*'){
                $arr[$i]['rights']='超级管理员';
            }else{
                $all = array();
                $tmp = explode(',', $r['rights']);
                foreach($tmp as $k){
                    if(isset($this->menuRights[$k])){
                        list($top,$side) = explode('.', $this->menuRights[$k]);
                        $all[$top][]=$side;
                    }
                }

                $arr[$i]['rights']='';
                foreach($all as $top=>$r){
                    if(!empty($r)){
                        $arr[$i]['rights'].= $top."(".implode(',', $r).") ";
                    }
                }
            }

            $arr[$i]['op'] = $this->btnEdtInDatagrid($pkey).' ' . $this->btnDelInDatagrid($pkey);
        }
        $this->renderArray($arr);
    }
    protected $menuRights;
    public function indexAction() {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
                ->addHeader('来源', 'cameFrom', 160, '')
                ->addHeader('登录名', 'loginName', 160, '')
                ->addHeader('部门', 'dept', 80, '')
                ->addHeader('昵称', 'nickname', 160, '')
                ->addHeader('权限', 'rights', 300, '')
                ->addHeader('最后登入IP', 'lastIP', 160, '')
                ->addHeader('最后登入时间', 'lastYmd', 200, '')
                ->addHeader('操作', 'op', 200, '')
                ->initJsonDataUrl($uri->uri(null,'listdata'));
        
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
                ->init('管理员一览')->initStdBtn($uri->uri(null,'pageadd'), $uri->uri(null,'pageupd'), 'del')
                ->initDatagrid($table);
        
        $this->renderPage($page);
    }


}
