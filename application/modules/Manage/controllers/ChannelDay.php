<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/8/30
 * Time: 16:51
 */

use Rpt\Manage\ManageIniCtrl;
use Prj\Model\ZyBusiness;
// 渠道日整体统计
class ChannelDayController extends Rpt\Manage\ManageIniCtrl
{
    public function indexAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'channelDayindex');

        $form
            ->appendHiddenFirst('__pkey__', $strpkey)->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('gt_ymd','', '开始日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('lt_ymd', date("Y-m-d"), '结束日期'));
        $form->isUserRequest($this->_request);
        //添加控制器
        $form->_controller = __CLASS__;
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('日期', 'ymd', 100, '')
            ->addHeader('新增注册(人)', 'newRegNum', 150, '')
            ->addHeader('新增认证绑卡(人)', 'newBindNum', 150, '')
            ->addHeader('新增投资(人)', 'newBoughtNum', 150, '')
            ->addHeader('新增投资金额', 'boughtAmount', 250)
            ->addHeader('投资金额', 'unexpiredAmount', 150)
            ->addHeader('存量', 'stock', 150)
            ->addHeader('日用户/用户分布', 'op', 150)
            ->initJsonDataUrl($this->urlForList($form,'listDayData'));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('渠道日信息')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listDayDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 || !$this->_request->get('__frmCreate__')){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }

    public function listDayDataAction(){
        $strpkey = $this->_request->get('__pkey__');
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        if( $getWhere[']ymd'] ){
            $getWhere[']ymd'] = strtotime($getWhere[']ymd']);
            }else{
            $getWhere[']ymd'] = '';
        }
        if( $getWhere['[ymd'] ){
            $getWhere['[ymd'] = strtotime($getWhere['[ymd']);
        }else{
            $getWhere['[ymd'] = strtotime('today');
        }
        $res = \Prj\Model\ChannelFinal::getChannelDayRecord();
        $channel = array();
        foreach ($res as $k => $v){
            $time = strtotime($v['ymd']);
            if( $time >= $getWhere[']ymd'] && $time <= $getWhere['[ymd'] ){
                $v['op'] = $this->btnChannelIndexDatagrid(['ymd'=>date('Y-m-d',strtotime($v['ymd']))]);
                $channel[] = $v;
            }
        }
//        print_r($channel);
        $this->renderArray($channel);
    }

    //下载EXCEL
    public function listDayDataDownAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        if( $getWhere[']ymd'] ){
            $getWhere[']ymd'] = strtotime($getWhere[']ymd']);
        }else{
            $getWhere[']ymd'] = 0;
        }
        if( $getWhere['[ymd'] ){
            $getWhere['[ymd'] = strtotime($getWhere['[ymd']);
        }else{
            $getWhere['[ymd'] = strtotime('today');
        }
        $res = \Prj\Model\ChannelFinal::getChannelDayRecord();
        // 导出的数据
        $arr = [];
        foreach($res as $k=>$v){
            // 日期
            $arr[$k]['ymd'] = $v['ymd'];
            // 新增注册(人)
            $arr[$k]['newRegNum'] = $v['newRegNum'];
            // 新增认证绑卡(人)
            $arr[$k]['newBindNum'] = $v['newBindNum'];
            // 新增投资(人)
            $arr[$k]['newBoughtNum'] = $v['newBoughtNum'];
            // 新增投资金额
            $arr[$k]['boughtAmount'] = $v['boughtAmount'];
            // 投资金额
            $arr[$k]['unexpiredAmount'] = $v['unexpiredAmount'];
            // 存量
            $arr[$k]['stock'] = $v['stock'];
        }
        $titleArr = ['日期','新增注册(人)','新增认证绑卡(人)','新增投资(人)','新增投资金额','投资金额','存量'];
        //生成EXCEL
        \Lib\Misc\ArrayH::exportCsv('日整体统计',$titleArr,$arr);
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

    public function decodePkey($strpkey)
    {
        return json_decode(hex2bin($strpkey),true);
    }

    /*日渠道分布*/
    protected function btnChannelIndexDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey),'fresh'=>1),'index','ChannelStatistics','manage');
        return  '<a href="'.$url.'" data-toggle="navtab" data-options="{id:\'manage-ChannelStatistics\', title:\'渠道整体统计\', mask:true,width:800, height:500,fresh:true}">日渠道分布</a>&nbsp;';
    }
}