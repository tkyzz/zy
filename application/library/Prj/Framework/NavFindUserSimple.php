<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Framework;

/**
 * Description of NavFindUserSimple
 *
 * @author simon.wang
 */
class NavFindUserSimple {
    protected $_vars=array();
    protected $_navtab_options;
    protected $_id_for_phone;
    /**
     * 
     * @return \Prj\Framework\NavFindUserSimple
     */
    public static function factory()
    {
        $uri = \Sooh2\Misc\Uri::getInstance(); 
        $navid = $uri->currentModule()."-".$uri->currentController();
        $navurl = $uri->uri(array('phone'=>''));
        
        $o = new NavFindUserSimple;
        $o->_navtab_options = "{id:'{$navid}', url:'{$navurl}'}";
        $o->_id_for_phone = 'phone4_'.$navid;
        return $o;
    }

    public function render($content='')
    {
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        $btnDefault ='<div class="btn-group"><button type="button" class="btn-green" data-icon="search"  '
                .'onclick="var tmp='.$this->_navtab_options.';tmp.url=tmp.url+$(\'#'.$this->_id_for_phone.'\').val();BJUI.navtab(tmp);">开始搜索！</button></div>';
        
        echo '<div class="bjui-pageHeader" style="background-color:#fefefe; border-bottom:none;">';
        echo '<div style="margin:0; padding:1px 5px 5px;">
            <span>手机号: </span><input class="form-control" style="width:200px;" id="'.$this->_id_for_phone.'" type="text" value="" data-rule=" required">';
        echo $btnDefault.'';
        echo '</div></div>';
        echo '<div class="bjui-pageContent">';
        if(substr($content,0,1)=='<'){
            echo $content;
        }else{
            echo '<pre>'.$content.'</pre>';
        }
        echo '</div>';
    }
}
