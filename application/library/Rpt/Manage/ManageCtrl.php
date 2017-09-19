<?php
namespace Rpt\Manage;
//用到的东西很少，所以兼容着yaf直接写了个类，不用一级一级派生

class ManageCtrl extends \Prj\Framework\Ctrl
{
    /**
     * 通过手机获取uid,没找到，返回null
     */
    protected function getUidByPhone($phone)
    {
        $user = \Prj\Model\User::getCopyByPhone($phone);
        $user->load();
        if($user->exists()){
            return $user->getField('oid');
        }else{
            return null;
        }
    }
    public function initBySooh($request, $view) {
        parent::initBySooh($request, $view);
        \Sooh2\BJUI\Broker::getInstance()->init('掌悦运营管理后台', \Sooh2\Misc\Uri::getInstance()->uri(null,'relogin'), 'ignore');
        if($request->getControllerName()!='manager'){
            $this->checkRights();
        }
        $renderType = $request->get(ARGNAME_RENDER_TYPE);
        if(empty($renderType)){
            \Sooh2\Misc\ViewExt::getInstance()->initRenderType('www');
        }
    }
    protected function checkRights()
    {
        $m = strtolower($this->_request->getModuleName());
        $c = strtolower($this->_request->getControllerName());
        $managerId = $this->getMyId();
        $loger = \Sooh2\Misc\Loger::getInstance();
        if(method_exists($loger,'initMoreInfo')){
            $loger->initMoreInfo('LogManager', $managerId);
        }
        //error_log("inManCtrl->checkRights:".$managerId);
        if(empty(!$managerId)){
            $this->manager = \Rpt\Manage\Manager::getByManagerId($managerId);
            $this->manager->load();
            $rights = ','.$this->manager->getField('rights').',';
        }else{
            $rights = ',';
        }
        //error_log("db[$rights]  vs cur<$m-$c>" . var_export(strpos($rights, ",$m-$c,"),true));
        $rights = strtolower($rights);
        if($rights==',*,' || strpos($rights, ",$m-$c,")!==false || strpos($rights, ",$m-*,")!==false){
            return true;
        }else{
            $viewext = \Sooh2\Misc\ViewExt::getInstance();
            $viewext->initRenderType('json');
            $arr = \Sooh2\BJUI\Broker::getInstance()->setResultRelogin($this->_view, '登入超时或权限已收回，请重新登入确认');
            $viewext->onFrameworkRender($arr);
            echo $viewext->renderInstead();
            exit;
        }
    }
    /**
     * 没调用过checkRights的情况下，这里是null
     * @var \Rpt\Manage\Manager 
     */
    protected $manager;
    protected function getMyId()
    {
        return \Rpt\Session\Broker::getManagerId();
    }


    /**
     * 
     * @param \Sooh2\HTML\Page $page
     * @param bool $tpl 支持追加js/css代码
     */
    protected function renderPage($page , $tpl = false)
    {
        if(!$tpl)\Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        echo $page->render();
    }
    
    protected function renderArray($arr)
    {
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        echo json_encode($arr);
    }
}