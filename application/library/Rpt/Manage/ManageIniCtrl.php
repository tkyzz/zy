<?php
namespace Rpt\Manage;
//用到的东西很少，所以兼容着yaf直接写了个类，不用一级一级派生

class ManageIniCtrl extends ManageCtrl
{
    /**
     * 获取用于说明对象的信息，每个controller需要自定义
     * @param type $arrOrObj
     * @return string
     */
    protected function fmtIntro($arrOrObj)
    {
        return 'unset(todo)';//这里可能是数组，可能是KVObj
    }
    
    protected function btnEdtInDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'pageupd');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'修改\', mask:true,width:800, height:500}">修改</a>&nbsp;';
    }
    protected function btnDelInDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=>\Rpt\KVObjBase::base64EncodePkey($pkey)),'del');
        return '<a href="'.'" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'你确定要删除'.str_replace('"','',\Sooh2\Util::toJsonSimple($pkey)).'！\', okCall:function(){mydelcmd(\''.$url .'\');}}">删除</a>&nbsp;';
    }

    protected function btnAjax($pkey , $action ,$btnName , $warn = '你确定要进行此操作?'){
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=>\Rpt\KVObjBase::base64EncodePkey($pkey)),$action);
        return '<a href="'.$url.'" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\''. $warn .'\', okCall:function(){mydelcmd(\''.$url .'\');}}">'. $btnName .'</a>&nbsp;';
    }
    
    protected function btnNewtab($url,$capt,$tabid=null,$tabtitle=null)
    {
        if($tabtitle===null){
            $tabtitle = $capt;
        }
        if($tabid===null){
            $tabid = 'autobyrand'.rand(100000,999999);
        }
        return "<a href=". $url." data-toggle=navtab data-options=\"{id:'".$tabid."', title:'".$tabtitle."'}\">".$capt."</a>";
    }
    
    protected function btnDetail($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'detail');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'detail\', title:\'修改\', mask:true,width:800, height:500}">详情</a>&nbsp;';
    }
    
    public function delAction()
    {
        $dropedObj = \Rpt\Manage\Manager::getByBASE64($this->_request->get('__pkey__'));
        $dropedObj->load();
        if($dropedObj->exists()){
            $dropedObj->delete();
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功删除'.$this->fmtIntro($dropedObj));
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，删除失败');
        }
    }

    public function pageupdAction(){}

    protected function returnError($msg = ''){
        $msg = $msg ? $msg : '操作失败';
        if(is_array($msg)){
            $msg = $msg['message'];
        }
        return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, $msg);
    }

    protected function returnOk($msg = '' , $close = true , $data = []){
        $msg = $msg ? $msg : '操作成功';
        return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, $msg , $close);
    }

    protected function getPkey(){
        $code = $this->_request->get('__pkey__');
        return \Lib\Misc\StringH::base64DecodePkey($code);
    }

    protected function log($msg , $level = LOG_INFO){
        \Prj\Loger::out($msg , $level);
        return true;
    }

    /**
     * Hand 自动搜索
     * @return bool
     */
    protected function autoSearch(){
        if (!$this->_request->get("__frmCreate__")) {
            echo "<script type=\"text/javascript\">$(function(){
                $($.CurrentNavtab[0]).find('[data-icon=\"search\"]').click();
             })
            </script>";
        }
        return true;
    }

    /**
     * Hand echarts线图
     * @param $uri
     * @return string
     */
    protected function echartsLineHtml($uri){
        return '<div style="mini-width:400px;height:350px;max-width:1000px" data-toggle="echarts" data-type="bar,line" data-url="'. $uri .'"></div>';
    }

    /**
     * Hand echarts饼图
     * @param $uri
     * @return string
     */
    protected function echartsPieHtml($uri){
        return '<div style="mini-width:400px;height:350px;max-width:1000px" data-toggle="echarts" data-type="pie,funnel" data-url="'. $uri .'"></div>';
    }
}