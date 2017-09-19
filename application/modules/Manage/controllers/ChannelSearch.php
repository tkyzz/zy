<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/24
 * Time: 11:36
 */

use Rpt\Manage\ManageIniCtrl;
use Prj\Model\ZyBusiness;

class ChannelSearchController extends Rpt\Manage\ManageIniCtrl
{
    public function indexAction()
    {
        $form = $this->searchForm();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('渠道ID', 'channelId', 100, '')
            ->addHeader('关键字', 'description', 250, '')
            ->addHeader('新增注册(人)', 'newRegNum', 150, '')
            ->addHeader('新增认证绑卡(人)', 'newBindNum', 150, '')
            ->addHeader('新增投资(人)', 'newBoughtNum', 150, '')
            ->addHeader('新增投资金额', 'boughtAmount', 250)
            ->addHeader('投资金额', 'unexpiredAmount', 150)
            ->addHeader('存量', 'stock', 150)
            ->addHeader('日分布/用户分布', 'op', 250)
            ->initJsonDataUrl($this->urlForList($form));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('搜索引擎统计')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 || !$this->_request->get('__frmCreate__')){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }

    //搜索引擎统计excel下载
    public function listDataDownAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $listWhere = ['status'=>0];
        if( $getWhere['qudao'] && $getWhere['channel']){
            $listWhere['*'.$getWhere['qudao']] = '%'.$getWhere['channel'].'%';
        }
        if( $getWhere['channelId'] ){
            $listWhere['channelId'] = $getWhere['channelId'];
        }else{
            $listWhere['channelId'] = ['248','249','250'];
        }
        $channel = \Prj\Model\ContractInfo::basicChannelSearchList($listWhere);
        $channelAllDeatil = $this->channelDetail($getWhere);
        //数据初始化
        $arr = [];
        foreach ($channel as $k =>$v){
            $agreementId = substr($v['contractCode'],-5);
            $channelId = substr($v['contractCode'],0,4);
            // 渠道ID
            $arr[$k]['channelId'] = $channel[$k]['channelId'];
            // 关键字
            $arr[$k]['description'] = $channel[$k]['description'];
            // 新增注册人数
            $arr[$k]['newRegNum'] = $channelAllDeatil[$agreementId]['newRegNum'] ? $channelAllDeatil[$agreementId]['newRegNum'] : 0;
            // 新增认证绑卡人数
            $arr[$k]['newBindNum'] = $channelAllDeatil[$agreementId]['newBindNum'] ? $channelAllDeatil[$agreementId]['newBindNum'] : 0;
            // 新增投资人数
            $arr[$k]['newBoughtNum'] = $channelAllDeatil[$agreementId]['newBoughtNum'] ? $channelAllDeatil[$agreementId]['newBoughtNum'] : 0;
            // 新增投资总金额
            $arr[$k]['boughtAmount'] = $channelAllDeatil[$agreementId]['boughtAmount'] ? $channelAllDeatil[$agreementId]['boughtAmount'] : 0;
            // 投资总金额
            $arr[$k]['unexpiredAmount'] = $channelAllDeatil[$agreementId]['unexpiredAmount'] ? $channelAllDeatil[$agreementId]['unexpiredAmount'] : 0;
            // 存量
            $arr[$k]['stock'] = $channelAllDeatil[$agreementId]['stock'] ? $channelAllDeatil[$agreementId]['stock'] : 0;

        }
        $titleArr = ['渠道ID','关键字','新增注册(人)','新增认证绑卡(人)','新增投资(人)','新增投资金额','投资金额','存量'];
        //生成EXCEL
        \Lib\Misc\ArrayH::exportCsv('搜索引擎统计',$titleArr,$arr);
    }

    //搜索引擎日统计excel下载
    public function listDayDataDownAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        if( $getWhere['channelId'] ){
            $getWhere['*channelId'] = $getWhere['channelId'].'%';
            unset($getWhere['channelId']);
        }
        if( $getWhere[']ymd']){
            $getWhere[']ymd'] = date('Ymd',strtotime($getWhere[']ymd']));
        }
        if( $getWhere['[ymd'] ){
            $getWhere['[ymd'] = date('Ymd',strtotime($getWhere['[ymd']));
        }
        $res = \Prj\Model\ChannelFinal::getRecords('*',$getWhere);
        $arr = [];
        foreach($res as $k=>$v){
            $arr[$k]['ymd'] = $v['ymd'];
            $arr[$k]['newRegNum'] = $v['newRegNum'];
            $arr[$k]['newBindNum'] = $v['newBindNum'];
            $arr[$k]['newBoughtNum'] = $v['newBoughtNum'];
            $arr[$k]['boughtAmount'] = $v['boughtAmount'];
            $arr[$k]['unexpiredAmount'] = $v['unexpiredAmount'];
        }
        $titleArr = ['时间','新增注册（人）','新增认证绑卡(人)','新增投资(人)','新增投资金额','投资金额'];
        //生成EXCEL
        \Lib\Misc\ArrayH::exportCsv('搜索引擎日统计',$titleArr,$arr);
    }

    //搜索引擎用户统计excel下载
    public function listMemberDataDownAction(){
//        ->addHeader('注册时间', 'ymdReg', 100, '')
//            ->addHeader('用户姓名', 'realname', 150, '')
//            ->addHeader('手机号', 'phone', 150, '')
//            ->addHeader('实名认证', 'realVerifiedTime', 150, '')
//            ->addHeader('首次投资时间', 'orderTime', 250)
//            ->addHeader('首次投资金额', 'amountFirstBuy', 150)
//            ->addHeader('首次投资类型', 'unexpiredAmount', 150)
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
        if( $getWhere['[ymd'] < $getWhere[']ymd']  ){
            return false;
        }
        $res = \Prj\Model\UserFinal::getRecords('*',$where);
        $arr = [];
        foreach( $res as $k=>$v ){

            //筛选
            $date = strtotime(substr($v['contractId'],4,8));
            if( $date >= $getWhere[']ymd'] && $date <= $getWhere['[ymd'] ){
                $arr[$k]['ymdReg'] = $v['ymdReg'].' '.$v['hisReg'];
                $arr[$k]['realname'] = $v['realname'];
                $arr[$k]['phone'] = $v['phone'];
                $arr[$k]['realVerifiedTime'] = $v['realVerifiedTime'] ? '已认证':'未认证';
                $arr[$k]['orderTime'] = $v['orderTime'];
                $arr[$k]['amountFirstBuy'] = $v['amountFirstBuy'];
                $arr[$k]['unexpiredAmount'] = $v['unexpiredAmount'];
            }

        }
        $titleArr = ['注册时间','用户姓名','手机号','实名认证','首次投资时间','首次投资金额','首次投资类型'];
        //生成EXCEL
        \Lib\Misc\ArrayH::exportCsv('搜索引擎用户统计',$titleArr,$arr);
    }
    //首页搜索表单
    protected function searchForm()
    {
        //推广方式列表
        $searchOption = \Prj\Model\ContractInfo::basicChannelNameList();
        array_unshift($spreadOption,'全部');
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'channelSearchindex');

        $form->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_sdate', date("Y-m-d"), '开始日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_edate', date("Y-m-d"), '结束日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_qudao','',"")->initOptions(['description'=>'关键字']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_channel", '', ""))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("eq_channelId", '', '搜索引擎')->initOptions($searchOption));
        $form->isUserRequest($this->_request);
        return $form;
    }


    public function listDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $listWhere = ['status'=>0];
        if( $getWhere['qudao'] && $getWhere['channel']){
            $listWhere['*'.$getWhere['qudao']] = '%'.$getWhere['channel'].'%';
        }
        if( $getWhere['channelId'] ){
            $listWhere['channelId'] = $getWhere['channelId'];
        }else{
            $listWhere['channelId'] = ['248','249','250'];
        }
        $channelAllDeatil = $this->channelDetail($getWhere);
        $channel = \Prj\Model\ContractInfo::basicChannelSearchList($listWhere);
        foreach ($channel as $k =>$v){
            $agreementId = substr($v['contractCode'],-5);
            $channelId = substr($v['contractCode'],0,4);
            // 渠道ID
            $arr[$k]['channelId'] = $channel[$k]['channelId'];
            // 关键字
            $arr[$k]['description'] = $channel[$k]['description'];
            // 新增注册人数
            $arr[$k]['newRegNum'] = $channelAllDeatil[$agreementId]['newRegNum'] ? $channelAllDeatil[$agreementId]['newRegNum'] : 0;
            // 新增认证绑卡人数
            $arr[$k]['newBindNum'] = $channelAllDeatil[$agreementId]['newBindNum'] ? $channelAllDeatil[$agreementId]['newBindNum'] : 0;
            // 新增投资人数
            $arr[$k]['newBoughtNum'] = $channelAllDeatil[$agreementId]['newBoughtNum'] ? $channelAllDeatil[$agreementId]['newBoughtNum'] : 0;
            // 新增投资总金额
            $arr[$k]['boughtAmount'] = $channelAllDeatil[$agreementId]['boughtAmount'] ? $channelAllDeatil[$agreementId]['boughtAmount'] : 0;
            // 投资总金额
            $arr[$k]['unexpiredAmount'] = $channelAllDeatil[$agreementId]['unexpiredAmount'] ? $channelAllDeatil[$agreementId]['unexpiredAmount'] : 0;
            // 存量
            $arr[$k]['stock'] = $channelAllDeatil[$agreementId]['stock'] ? $channelAllDeatil[$agreementId]['stock'] : 0;
            $arr[$k]['op'] = $this->btnDayInfoDatagrid(['channelId'=>$channelId]).' | '.$this->btnMemberInfoDatagrid(['channelId'=>$channelId]);
        }
//        print_r($channel);
        $this->renderArray($arr);

    }

    //日列表数据
    public function listDayDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        if( $getWhere['channelId'] ){
            $getWhere['*channelId'] = $getWhere['channelId'].'%';
            unset($getWhere['channelId']);
        }
        if( $getWhere[']ymd']){
            $getWhere[']ymd'] = date('Ymd',strtotime($getWhere[']ymd']));
        }
        if( $getWhere['[ymd'] ){
            $getWhere['[ymd'] = date('Ymd',strtotime($getWhere['[ymd']));
        }
//        print_r($getWhere);
        $res = \Prj\Model\ChannelFinal::getRecords('*',$getWhere);
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
        if( $getWhere['[ymd'] < $getWhere[']ymd']  ){
            return false;
        }
        $res = \Prj\Model\UserFinal::getRecords('*',$where);
        foreach( $res as $k=>$v ){

            //筛选
            $date = strtotime(substr($v['contractId'],4,8));
            if( $date >= $getWhere[']ymd'] && $date <= $getWhere['[ymd'] ){
                $res[$k]['ymdReg'] = $v['ymdReg'].' '.$v['hisReg'];
                $res[$k]['realVerifiedTime'] = $v['realVerifiedTime'] ? '已认证':'未认证';
                $arr[] = $res[$k];
            }

        }
        $this->renderArray($arr);
    }
    

    //渠道日分布列表
    public function dayInfoAction(){
        $strpkey = $this->decodePkey($this->_request->get('__pkey__'))['channelId'];
        $uri = \Sooh2\Misc\Uri::getInstance();
        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'channelSearchDay');
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
            ->init('搜索引擎日信息')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listDayDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 ){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }

    //渠道用户列表
    public function memberInfoAction(){
        $strpkey = $this->decodePkey($this->_request->get('__pkey__'))['channelId'];
        $uri = \Sooh2\Misc\Uri::getInstance();
        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'channelSearchMember');
        $form
            ->appendHiddenFirst('__pkey__', $strpkey)->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('gt_ymd', date("Y-m-d",strtotime('-1month')), '开始日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('lt_ymd', date("Y-m-d"), '结束日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_filter', $strpkey, '筛选')->initOptions(['1'=>'注册时间','2'=>'投资时间']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('eq_channelId', $strpkey, '渠道ID','hidden'));
        $form->isUserRequest($this->_request);
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('注册时间', 'ymdReg', 100, '')
            ->addHeader('用户姓名', 'realname', 150, '')
            ->addHeader('手机号', 'phone', 150, '')
            ->addHeader('实名认证', 'realVerifiedTime', 150, '')
            ->addHeader('首次投资时间', 'orderTime', 250)
            ->addHeader('首次投资金额', 'amountFirstBuy', 150)
            ->addHeader('首次投资类型', 'unexpiredAmount', 150)
            ->initJsonDataUrl($this->urlForList($form,'listMemberData'));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('搜索引擎用户信息')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listMemberDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 ){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }

    //获取渠道统计总信息
    public function channelDetail($getWhere){
        if($getWhere['sdate']) {
            $where[']ymd'] = date('Ymd',strtotime($getWhere['sdate']));
        }else{
        }
        if($getWhere['edate']) {
            $where['[ymd'] = date('Ymd',strtotime($getWhere['edate']));
        }else{
        }
        $res = \Prj\Model\ChannelFinal::getRecords('*',$where);
        if( !empty($res) ){
            $arr = [];
            foreach( $res as $k => $v ){
                $arr[$v[agreementId]] = $res[$k];
            }
        }
        return $arr;
    }

    public function decodePkey($strpkey)
    {
        return json_decode(hex2bin($strpkey),true);
    }


    /*日分布*/
    protected function btnDayInfoDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey),'fresh'=>1),'dayInfo');
        return  '<a href="'.$url.'" data-toggle="navtab" data-options="{id:\'manage-Search-dayInfo\', title:\'搜索日数据列表\', mask:true,width:800, height:500,fresh:true}">日分布</a>&nbsp;';
    }

    /*用户分布*/
    protected function btnMemberInfoDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey),'fresh'=>1),'memberInfo');
        return  '<a href="'.$url.'" data-toggle="navtab" data-options="{id:\'manage-Search-memberInfo\', title:\'搜索用户分布\', mask:true,width:800, height:500,fresh:true}">用户分布</a>&nbsp;';
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