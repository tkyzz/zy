<?php
namespace Rpt\Manage;
//用到的东西很少，所以兼容着yaf直接写了个类，不用一级一级派生

class ManageLogCtrl extends ManageCtrl
{
    /**
     * 
     * @param \Sooh2\HTML\Form\Base $form
     * @param type $act
     * @return type
     */
    protected function urlForListLog($form,$act = 'listlog')
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $where = $form->getWhere();
        if(empty($where)){
            return $uri->uri(null,$act);
        }else{
            return $uri->uri(array('__wHeRe__'=> bin2hex(json_encode($where))),$act);
        }
        
    }
    protected function whereForListLog()
    {
        $s =  $this->_request->get('__wHeRe__');
        if(empty($s)){
            return array();
        }else{
            return json_decode(hex2bin($s),true);
        }
    }
}