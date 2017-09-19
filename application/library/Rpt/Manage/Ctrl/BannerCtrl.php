<?php
namespace Rpt\Manage\Ctrl;

class BannerCtrl extends \Rpt\Manage\ManageCtrl
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
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'bannerUpd');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'noticeUpd\', title:\'修改\', mask:true,width:1000, height:600}">修改</a>';
    }
    protected function btnDelInDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=>\Rpt\KVObjBase::base64EncodePkey($pkey)),'bannerDel');
        return '<a href="'.'" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'你确定要删除'.str_replace('"','',\Sooh2\Util::toJsonSimple($pkey)).'！\', okCall:function(){mydelcmd(\''.$url .'\');}}">删除</a>';
    }


    protected function btnCreateInDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'noticeCreate');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'noticeCretae\', title:\'生成内容页\', mask:true,width:400, height:400}">重新生成</a>';
    }

    protected function btnAprInDatagrid($pkey){
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'BannerApr');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'bannerApr\', title:\'审核\', mask:true,width:400, height:400}">审核</a>';
    }

    protected function btnReleaseDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=>\Rpt\KVObjBase::base64EncodePkey($pkey)),'bannerRelease');
        return '<a href="'.$url.'" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'你确定要上架'.str_replace('"','',\Sooh2\Util::toJsonSimple($pkey)).'！\', okCall:function(){mydelcmd(\''.$url .'\');}}">上架</a>';
    }
	 protected function btnUnreleaseDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=>\Rpt\KVObjBase::base64EncodePkey($pkey)),'bannerUnrelease');
        return '<a href="'.$url.'" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'你确定要下架'.str_replace('"','',\Sooh2\Util::toJsonSimple($pkey)).'！\', okCall:function(){mydelcmd(\''.$url .'\');}}">下架</a>';
    }

    protected function btnDetailInDatagrid($pkey){
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'bannerDetail');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'bannerDetail\', title:\'详情\', mask:true,width:1000, height:800}">详情</a>';
    }

    protected function btnSortDatagrid($pkey){
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'bannerSort');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'bannerSort\', title:\'详情\', mask:true,width:400, height:400}">排序</a>';
    }


    /*
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
    }*/
}