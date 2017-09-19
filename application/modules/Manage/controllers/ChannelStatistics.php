<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/24
 * Time: 11:36
 */

use Rpt\Manage\ManageIniCtrl;
use Prj\Model\ZyBusiness;

class ChannelStatisticsController extends Rpt\Manage\ManageIniCtrl
{

    //首页搜索表单
    protected function searchForm()
    {
        $pkey =  $this->decodePkey($this->_request->get('__pkey__'));//print_r($pkey);
        $date = $pkey['ymd'] ? $pkey['ymd'] : date("Y-m-d");
        //推广方式列表
        $spreadOption = ZyBusiness\PlatformChannel::getChannel();
        array_unshift($spreadOption,'全部');
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'channelstatindex');

        $form->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_date', $date, '日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_qudao','',"")->initOptions(['name'=>'渠道名称','channelId'=>'渠道ID']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_channel", '', ""))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("eq_spreadId", '', '推广方式')->initOptions($spreadOption));
        $form->isUserRequest($this->_request);
        return $form;
    }

    public function indexAction()
    {
        $form = $this->searchForm();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('渠道ID', 'channelId', 100, '')
            ->addHeader('渠道名称', 'name', 250, '')
            ->addHeader('新增注册(人)', 'newRegNum', 150, '')
            ->addHeader('新增认证绑卡(人)', 'newBindNum', 150, '')
            ->addHeader('新增投资(人)', 'newBoughtNum', 150, '')
            ->addHeader('新增投资金额', 'boughtAmount', 250)
            ->addHeader('投资金额', 'unexpiredAmount', 150)
            ->addHeader('存量', 'stock', 150)
            ->addHeader('日分布/用户分布', 'op', 250)
            ->initJsonDataUrl($this->urlForList($form));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('渠道统计')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 || !$this->_request->get('__frmCreate__')){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }

    // 渠道详情
    public function channelInfoAction(){
        $strpkey =$this->_request->get('__pkey__');
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('渠道ID', 'channelId', 100, '')
            ->addHeader('渠道名称', 'name', 250, '')
            ->addHeader('新增注册(人)', 'newRegNum', 150, '')
            ->addHeader('新增认证绑卡(人)', 'newBindNum', 150, '')
            ->addHeader('新增投资(人)', 'newBoughtNum', 150, '')
            ->addHeader('新增投资金额', 'boughtAmount', 250)
            ->addHeader('投资金额', 'unexpiredAmount', 150)
            ->addHeader('存量', 'stock', 150)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(['__pkey__'=>$strpkey],'listInfoData'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init('渠道统计')
            ->initDatagrid($table);
        $this->renderPage($page);
    }

    //渠道日分布列表
    public function dayInfoAction(){
        $strpkey = $this->decodePkey($this->_request->get('__pkey__'))['channelId'];
        $uri = \Sooh2\Misc\Uri::getInstance();
        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'channelstatDay');
        $form
            ->appendHiddenFirst('__pkey__', $strpkey)->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('gt_ymd', date("Y-m-d",strtotime('-1month')), '开始日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('lt_ymd', date("Y-m-d"), '结束日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('eq_channelId', $strpkey, '渠道ID'));
        $form->isUserRequest($this->_request);
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('时间', 'ymd', 100, '')
            ->addHeader('新增注册（人）', 'newRegNum', 150, '')
            ->addHeader('新增认证绑卡(人)', 'newBindNum', 150, '')
            ->addHeader('新增投资人', 'newBoughtNum', 150, '')
            ->addHeader('新增投资金额', 'boughtAmount', 250)
            ->addHeader('投资金额', 'unexpiredAmount', 150)
            ->initJsonDataUrl($this->urlForList($form,'listDayData'));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('渠道日信息')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listDayDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 ){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }

    }


    //首页数据
    public function listDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $listWhere = ['status'=>0];
        $listWhere['!channelId'] = ['248','249','250'];
        if( $getWhere['qudao'] && $getWhere['channel']){
            $listWhere[$getWhere['qudao']] = $getWhere['channel'];
        }
        $channel = \Prj\Model\ContractInfo::basicChannelList($listWhere);
        $data = $this->getChannelDetail($channel,$getWhere);
        $this->renderArray($data);

    }



    //日列表数据
    public function listDayDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $where = [];
        if( $getWhere['channelId'] ){
            $where['*channelId'] = $getWhere['channelId'].'%';
        }
        if( $getWhere[']ymd']){
            $where[']ymd'] = date('Ymd',strtotime($getWhere[']ymd']));
        }
        if( $getWhere['[ymd'] ){
            $where['[ymd'] = date('Ymd',strtotime($getWhere['[ymd']));
        }
        $res = \Prj\Model\ChannelFinal::getRecords('*',$where);
        $this->renderArray($res);
    }

    //日用户列表数据
    public function listMemberDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $arr = [];
        if( $getWhere['channelId'] ){
            $where['*contractId'] = $getWhere['channelId'].'%';
        }
        if( $getWhere[']ymd']){
            $getWhere[']ymd'] = strtotime($getWhere[']ymd']);
        }
        if( $getWhere['[ymd'] ){
            $getWhere['[ymd'] = strtotime($getWhere['[ymd']);
        }
        if( $getWhere['filter'] == 2 ){
            //筛选已投资
            $where['!ymdFirstBuy'] = 0;
        }
        $res = \Prj\Model\UserFinal::getRecords('*',$where);
        if( !empty($res) ){
            $arr = $this->getChannelMemberDetail($res,$getWhere);
        }
        $this->renderArray($arr);
    }
    //详情
    public function listInfoDataAction(){
        //推广方式列表
        $spreadOption = ZyBusiness\PlatformChannel::getChannel();
        $pkey =  $this->decodePkey($this->_request->get('__pkey__'));
        $channelId = $pkey['channelId'];
        $name = $pkey['name'];
        $ymd = $pkey['ymd'];
        $where = ['*channelId'=>$channelId.'%','ymd'=>$ymd];
        $channel = \Prj\Model\ChannelFinal::getRecords('*',$where);
        foreach ($channel as $k =>$v){
            $channel[$k]['channelId'] = $channelId;
            $channel[$k]['name'] = $name.$spreadOption[$v['spreadId']];
            // 新增注册人数
            $channel[$k]['newRegNum'] = $v['newRegNum'] ? $v['newRegNum'] : 0;
            // 新增认证绑卡人数
            $channel[$k]['newBindNum'] = $v['newBindNum'] ? $v['newBindNum'] : 0;
            // 新增投资人数
            $channel[$k]['newBoughtNum'] = $v['newBoughtNum'] ? $v['newBoughtNum'] : 0;
            // 新增投资总金额
            $channel[$k]['boughtAmount'] = $v['boughtAmount'] ? $v['boughtAmount'] : 0;
            // 投资总金额
            $channel[$k]['unexpiredAmount'] = $v['unexpiredAmount'] ? $v['unexpiredAmount'] : 0;
            //存量
            $channel[$k]['stock'] = $v['stock'] ? $v['stock'] : 0;

        }
        $this->renderArray($channel);

    }

    //渠道用户列表
    public function memberInfoAction(){
        $strpkey = $this->decodePkey($this->_request->get('__pkey__'))['channelId'];
        $uri = \Sooh2\Misc\Uri::getInstance();
        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'channelstatMember');
        $form
            ->appendHiddenFirst('__pkey__', $strpkey)->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('gt_ymd', date("Y-m-d",strtotime('-1month')), '开始日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('lt_ymd', date("Y-m-d"), '结束日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_filter', $strpkey, '筛选')->initOptions(['1'=>'注册时间','2'=>'投资时间']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('eq_channelId', $strpkey, '渠道ID','hidden'));
        $form->isUserRequest($this->_request);
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('注册时间', 'ymdReg', 200, '')
            ->addHeader('用户姓名', 'realname', 150, '')
            ->addHeader('手机号', 'phone', 150, '')
            ->addHeader('实名认证', 'realVerifiedTime', 150, '')
            ->addHeader('首次投资时间', 'ymdFirstBug', 250)
            ->addHeader('首次投资金额', 'amountFirstBuy', 150)
            ->addHeader('首次投资类型', 'orderCodeFirstBuy', 150)
            ->initJsonDataUrl($this->urlForList($form,'listMemberData'));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('渠道用户信息')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listMemberDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 ){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }

    // excel 渠道统计下载
    public function listDataDownAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $listWhere = ['status'=>0];
        $listWhere['!channelId'] = ['248','249','250'];
        if( $getWhere['qudao'] && $getWhere['channel']){
            $listWhere[$getWhere['qudao']] = $getWhere['channel'];
        }
        $channel = \Prj\Model\ContractInfo::basicChannelList($listWhere);
        $titleArr = ['渠道ID','渠道名称','新增注册(人)','新增认证绑卡(人)','新增投资(人)','新增投资金额','投资金额','存量'];
        $data = $this->getChannelDetail($channel,$getWhere);
        $arr = [];
        foreach ($data as $k => $v){
            $arr[$k]['channelId'] = $v['channelId'];
            $arr[$k]['name'] = $v['name'];
            $arr[$k]['newRegNum'] = $v['newRegNum'];
            $arr[$k]['newBindNum'] = $v['newBindNum'];
            $arr[$k]['newBoughtNum'] = $v['newBoughtNum'];
            $arr[$k]['boughtAmount'] = $v['boughtAmount'];
            $arr[$k]['unexpiredAmount'] = $v['unexpiredAmount'];
            $arr[$k]['stock'] = $v['stock'];
        }
        //生成EXCEL
        \Lib\Misc\ArrayH::exportCsv('渠道整体统计',$titleArr,$arr);
    }

    // excel 渠道日统计下载
    public function listDayDataDownAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $where = [];
        if( $getWhere['channelId'] ){
            $where['*channelId'] = $getWhere['channelId'].'%';
        }
        if( $getWhere[']ymd']){
            $where[']ymd'] = date('Ymd',strtotime($getWhere[']ymd']));
        }
        if( $getWhere['[ymd'] ){
            $where['[ymd'] = date('Ymd',strtotime($getWhere['[ymd']));
        }
        $data = \Prj\Model\ChannelFinal::getRecords('*',$where);
        $titleArr = ['时间','新增注册(人)','新增认证绑卡(人)','新增投资(人)','新增投资金额','投资金额'];
        $arr = [];
        foreach ($data as $k => $v){
            $arr[$k]['ymd'] = $v['ymd'];
            $arr[$k]['newRegNum'] = $v['newRegNum'];
            $arr[$k]['newBindNum'] = $v['newBindNum'];
            $arr[$k]['newBoughtNum'] = $v['newBoughtNum'];
            $arr[$k]['boughtAmount'] = $v['boughtAmount'];
            $arr[$k]['unexpiredAmount'] = $v['unexpiredAmount'];
        }
        //生成EXCEL
        \Lib\Misc\ArrayH::exportCsv('渠道日统计',$titleArr,$arr);
    }

    // excel 渠道用户统计下载
    public function listMemberDataDownAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $arr = [];
        if( $getWhere['channelId'] ){
            $where['*contractId'] = $getWhere['channelId'].'%';
        }
        if( $getWhere[']ymd']){
            $getWhere[']ymd'] = strtotime($getWhere[']ymd']);
        }
        if( $getWhere['[ymd'] ){
            $getWhere['[ymd'] = strtotime($getWhere['[ymd']);
        }
        if( $getWhere['filter'] == 2 ){
            //筛选已投资
            $where['!ymdFirstBuy'] = 0;
        }
        $res = \Prj\Model\UserFinal::getRecords('*',$where);
        if( !empty($res) ){
            $data = $this->getChannelMemberDetail($res,$getWhere);
            $arr = [];
            foreach ($data as $k => $v){
                $arr[$k]['ymdReg'] = $v['ymdReg'];
                $arr[$k]['realname'] = $res[$k]['realname'];
                $arr[$k]['phone'] = $res[$k]['phone'];
                $arr[$k]['realVerifiedTime'] = $v['realVerifiedTime'];
                $arr[$k]['ymdFirstBug'] = $v['ymdFirstBug'];
                $arr[$k]['amountFirstBuy'] = $v['amountFirstBuy'];
                $arr[$k]['orderCodeFirstBuy'] = $v['orderCodeFirstBuy'];
            }
        }
        $titleArr = ['注册时间','用户姓名','手机号','实名认证','首次投资时间','首次投资金额','首次投资类型'];
        //生成EXCEL
        \Lib\Misc\ArrayH::exportCsv('渠道用户统计',$titleArr,$arr);
    }

    public function decodePkey($strpkey)
    {
        return json_decode(hex2bin($strpkey),true);
    }

    //获取渠道统计信息
    public function getChannelDetail($channel,$getWhere){

//        print_r($getWhere);
        foreach ($channel as $k =>$v){
            $channelId = substr($v['contractCode'],0,4);
            $where = ['*channelId'=>$channelId.'%'];
            if($getWhere['date']) {
                $where['ymd'] = date('Ymd',strtotime($getWhere['date']));
            }else{
                $where['ymd'] = date('Ymd');
            }
            if( $getWhere['spreadId'] ){
                $where['spreadId'] = $getWhere['spreadId'];
            }
            $res = \Prj\Model\ChannelFinal::getRecord('left(channelId,4) as channelId,ymd,sum(stock) as stock,sum(newRegNum) as newRegNum,sum(newBindNum) as newBindNum,sum(newBoughtNum) as newBoughtNum,sum(boughtAmount) as boughtAmount,sum(unexpiredAmount) as unexpiredAmount',$where);
            // 新增注册人数
            $channel[$k]['newRegNum'] = $res['newRegNum'] ? $res['newRegNum'] : 0;
            // 新增认证绑卡人数
            $channel[$k]['newBindNum'] = $res['newBindNum'] ? $res['newBindNum'] : 0;
            // 新增投资人数
            $channel[$k]['newBoughtNum'] = $res['newBoughtNum'] ? $res['newBoughtNum'] : 0;
            // 新增投资总金额
            $channel[$k]['boughtAmount'] = $res['boughtAmount'] ? $res['boughtAmount'] : 0;
            // 投资总金额
            $channel[$k]['unexpiredAmount'] = $res['unexpiredAmount'] ? $res['unexpiredAmount'] : 0;
            // 存量
            $channel[$k]['stock'] = $res['stock'] ? $res['stock'] : 0;
            $channel[$k]['op'] = $this->btnLookAtDatagrid(['channelId'=>$channelId,'name'=>$channel[$k]['name'],'ymd'=>$res['ymd']]).' | '.$this->btnDayInfoDatagrid(['channelId'=>$channelId]).' | '.$this->btnMemberInfoDatagrid(['channelId'=>$channelId]);
        }
        return $channel;
    }

    //获取渠道用户统计信息
    public function getChannelMemberDetail($res,$getWhere){

        foreach( $res as $k=>$v ){

            if( $getWhere['[ymd'] > $getWhere[']ymd']  ){
                //筛选
                $date = strtotime(substr($v['contractId'],4,8));
                if( $date >= $getWhere[']ymd'] && $date <= $getWhere['[ymd'] ){

                    $res[$k]['ymdReg'] = date('Y-m-d H:i:s',strtotime($v['ymdReg'].' '.$v['hisReg']));
                    $res[$k]['ymdFirstBug'] = $v['ymdFirstBuy'] ? date('Y-m-d H:i:s',$v['ymdFirstBuy']):'';
                    //首次投资类型
                    $res[$k]['orderCodeFirstBuy'] = \Prj\Model\ZyBusiness\InverstorTradeOrder::getProductDetail($v['orderCodeFirstBuy'])['productName'];
                    $res[$k]['realVerifiedTime'] = $v['realVerifiedTime'] ? '已认证':'未认证';
                    $arr[] = $res[$k];
                }
            }

        }
        return $arr;
    }


    /*详情*/
    protected function btnLookAtDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'channelInfo');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'详情\', mask:true,width:1200, height:800,fresh:true}">详情</a>&nbsp;';
    }

    /*日分布*/
    protected function btnDayInfoDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey),'fresh'=>1),'dayInfo');
        return  '<a href="'.$url.'" class="channelDay" data-toggle="navtab" data-options="{id:\'manage-Statistics-dayInfo\', title:\'渠道日数据列表\', mask:true,width:800, height:500,fresh:true}">日分布</a>&nbsp;';
    }

    /*用户分布*/
    protected function btnMemberInfoDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey),'fresh'=>1),'memberInfo');
        return  '<a href="'.$url.'" class="channelMember" data-toggle="navtab" data-options="{id:\'manage-Statistics-memberInfo\', title:\'渠道用户分布\', mask:true,width:800, height:500,fresh:true}">用户分布</a>&nbsp;';
    }

    protected function urlForList($form, $act = 'listData')
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $where = $form->getWhere();

        if (empty($where)) {
            return $uri->uri(null, $act);
        } else {
            return $uri->uri(array('__wHeRe__' => bin2hex(json_encode($where))), $act);
        }

    }
}