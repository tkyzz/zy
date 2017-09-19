<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Framework;

/**
 * Description of ViewInstead
 *
 * @author simon.wang
 */
class ApiView extends \Sooh2\Misc\ViewExt {
    public function initStatusTaskList($classnameOfTask)
    {
        //error_log("????????initStatusTaskList???????????".var_export($classnameOfTask,true));
        return parent::initStatusTaskList($classnameOfTask);
    }
    public function beforeRender($view)
    {
        //error_log("????????beforeRender???????????".var_export($this->extTaskList,true));
        parent::beforeRender($view);
    }
    protected function convertArgStructAndToJson($arrTplVars)
    {
        $uri=\Sooh2\Misc\Uri::getInstance()->getInstance();
        $m = strtolower($uri->currentModule());
        //$c = strtolower($uri->currentController());
        if($m=='manage'){//如果是管理后台，不做转换
            return \Sooh2\Util::toJsonSimple($arrTplVars);
        }

        $newarr = array();
        $newarr['code']=$arrTplVars['code']-0;
        $newarr['message']=$arrTplVars['message'];
        $newarr['serverMsg']=$arrTplVars['serverMsg'];
        $newarr['resTime'] = $arrTplVars['resTime'];
        $newarr['extendInfo']=$arrTplVars['extendInfo'] ?: [];
        if(isset($arrTplVars['data'])){
            $newarr['data'] = $arrTplVars['data'];
        }else{
            $newarr['data'] = array();
        }
        
        unset($arrTplVars['code'],$arrTplVars['message'],$arrTplVars['serverMsg'],$arrTplVars['resTime'],$arrTplVars['data'],$arrTplVars['extendInfo']);
        foreach($arrTplVars as $k=>$v){
            $newarr['data'][$k]=$v;
        }

//        if($m=='actives'  && ($c=='daysign'|| $c=='public'||$c=='xiaomia')){//TODO: 版本兼容，早期出的这几个接口，客户端按数据在根节点做的，所以数据冗余一下
//            foreach($arrTplVars as $k=>$v){
//                $newarr[$k]=$v;
//            }
//        }
        return \Sooh2\Util::toJsonSimple($newarr);
    }
    public function onFrameworkRender($arrTplVars)
    {
        //error_log("????????onFrameworkRender???????????".var_export($this->extTaskList,true));
        switch ($this->renderType){
            case self::type_json:
                self::$output['head']='Content-type: application/json';
                $ret = $this->convertArgStructAndToJson($arrTplVars);
                break;
            case self::type_jsonp:
                $ret = $this->nameJsonP.'('.$this->convertArgStructAndToJson($arrTplVars).')';
                break;
            case self::type_cmd:
                $ret = '';
                foreach($arrTplVars as $k1=>$rs){
                    $ret .= "$k1 :\n";
                    foreach($rs as $k2=>$r){
                        if(is_array($r)){
                            $ret .= "\t$k2 :\n";
                            foreach($r as $k3=>$v){
                                $ret .= "\t\t$k3 :".(is_array($v)?\Sooh2\Util::toJsonSimple($v):$v)."\n";
                            }
                        }else{
                            $ret .= "\t$k2 : $r\n";
                        }
                    }
                }
                break;
            case self::type_echo:
                $ret='';
                break;
            default:
                return false;
        }

        self::$output['body'] = $ret;
        return true;
    }
}
