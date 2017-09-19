<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/9/11
 * Time: 15:05
 */

use Manage\Controller;
class CouponFinalStatController extends \Rpt\Manage\ManageIniCtrl
{
    public $OperateType = [
        null => "全部",
        'BUSINESS' => "运营",
        'WECHAT' => "微信",
        'MARKET' => "市场",
        'CELLSALE' => "电销"
    ];

    //是否浮动
    public $ISFloat = ['否', '是'];

    //首页搜索表单
    protected function searchForm()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $couponOption = \Prj\Model\Coupon::getAdminOption();
        array_unshift($couponOption,'全部');
        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'couponFinalStat');

        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_type', '', "优惠券类型")->initOptions($couponOption))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_name", '', "优惠券名称"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("eq_uType", '', '优惠券用途')->initOptions($this->OperateType))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_startdate', '', '统计日期'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_enddate', '', ''));
        $form->isUserRequest($this->_request);
        return $form;
    }

    // 优惠券统计
    public function indexAction(){
        $form = $this->searchForm();
//        print_r($where = $form->getWhere());
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('统计日期', 'ymd', 200, '')
            ->addHeader('用途', 'purposeCode', 200, '')
            ->addHeader('优惠券名称', 'title', 200, '')
            ->addHeader('优惠券类型', 'typeCode', 200, '')
            ->addHeader('面额浮动', 'isFloat', 100, '')
            ->addHeader('优惠券面额', 'amount', 150, '')
            ->addHeader('起投金额(元)', 'investAmount', 180)
            ->addHeader('使用数量', 'useCount', 100)
            ->addHeader('使用成本(元)', 'useCost', 100)
            ->addHeader('创建时间', 'createTime', 300)
            ->initJsonDataUrl($this->urlForList($form));
//        print_r($form->getWhere());
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('优惠券使用统计  【使用总成本：'.$this->sumCost($form->getWhere()).'】')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 || !$this->_request->get('__frmCreate__')){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }
    //获取列表数据
    public function listDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $where = ' WHERE 1="1"';
        if( $getWhere['type'] ){
            $where .= ' AND c.typeCode = "'.$getWhere['type'].'"';
        }
        if( $getWhere['name'] ){
            $where .= ' AND c.title like "%'.$getWhere['name'].'%"';
        }
        if( $getWhere['uType'] ){
            $where .= ' AND c.purposeCode = "'.$getWhere['uType'].'"';
        }
        if( $getWhere['startdate'] ){
            $where .= ' AND cf.ymd >='.date('Ymd',strtotime($getWhere['startdate']));
        }
        if( $getWhere['enddate'] ){
            $where .= ' AND cf.ymd <='.date('Ymd',strtotime($getWhere['enddate']));
        }
//        echo $where;die();
        $data = \Prj\Model\CouponFinal::CouponFinalStat($where);//print_r($data);
        $couponType = \Prj\Model\Coupon::getAdminOption();
        foreach($data as $k => $v){
            // 用途
            $data[$k]['purposeCode'] = $this->OperateType[strtoupper($v['purposeCode'])];
            // 面额
            $data[$k]['amount'] = $v['amount'] ? number_format($v['amount']/100,2) : '-';
            // 起投金额
            $data[$k]['investAmount'] = $v['investAmount'] ? number_format($v['investAmount']/100,2) : '0';
            // 使用数量
            $data[$k]['useCount'] = $v['useCount'] ? $v['useCount'] : '0';
            // 使用成本
            $data[$k]['useCost'] = number_format($v['useCost']/100,2);
            // 是否浮动
            $data[$k]['isFloat'] = $this->ISFloat[$v['isFloat']];
            // 类型
            $data[$k]['typeCode'] = $couponType[strtoupper($v['typeCode'])];
            // 统计日期
            if( $v['minymd'] == $v['maxymd'] ){
                $data[$k]['ymd'] = date('Y.m.d',strtotime($v['minymd']));
            }else{
                $data[$k]['ymd'] = date('Y.m.d',strtotime($v['minymd'])).'--'.date('Y.m.d',strtotime($v['maxymd']));
            }

            // 创建时间
            $data[$k]['createTime'] = date('Y.m.d H:i:s',strtotime($v['createTime']));
        }

        $this->renderArray($data);
    }

    // 计算总成本
    protected function sumCost($getWhere){
        $where = ' WHERE 1="1"';
        if( $getWhere['type'] ){
            $where .= ' AND c.typeCode = "'.$getWhere['type'].'"';
        }
        if( $getWhere['name'] ){
            $where .= ' AND cf.title like "%'.$getWhere['name'].'%"';
        }
        if( $getWhere['uType'] ){
            $where .= ' AND cf.purposeCode = "'.$getWhere['uType'].'"';
        }
        if( $getWhere['startdate'] ){
            $where .= ' AND cf.ymd >='.date('Ymd',strtotime($getWhere['startdate']));
        }
        if( $getWhere['enddate'] ){
            $where .= ' AND cf.ymd <='.date('Ymd',strtotime($getWhere['enddate']));
        }
        $data = \Prj\Model\CouponFinal::CouponFinalStat($where);
        $cost = 0;
//        print_r($data);
        foreach($data as $k=>$v) {
            $cost += $v['useCost']/100;
        }
        return number_format($cost,2);
    }

    // 筛选条件格式化
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

    /*详情*/
    protected function btnLookAtDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'usage');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'使用情况\', mask:true,width:1200, height:800,fresh:true}">使用情况</a>&nbsp;';
    }
}