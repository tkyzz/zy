<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/9/8
 * Time: 15:29
 *
 * Desc 优惠券统计后台
 */
use Manage\Controller;
class CouponFinalController extends \Rpt\Manage\ManageIniCtrl
{
    public $OperateType = [
         null       =>  "全部",
        'BUSINESS'  =>  "运营",
        'WECHAT'    =>  "微信",
        'MARKET'    =>  "市场",
        'CELLSALE'  =>  "电销"
    ];
    public $durationPeriodDaysUnit = ['1'=>'天','2'=>'月'];
    //是否浮动
    public $ISFloat = ['否','是'];
    //首页搜索表单
    protected function searchForm()
    {
//        $pkey =  $this->decodePkey($this->_request->get('__pkey__'));//print_r($pkey);
//        $date = $pkey['ymd'] ? $pkey['ymd'] : date("Y-m-d");
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'listData');

        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_type','',"优惠券类型")->initOptions(\Prj\Model\Coupon::getAdminOption()))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_name", '', "优惠券名称"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("eq_uType", '', '优惠券用途')->initOptions($this->OperateType))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_startdate', '', '统计时间'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_enddate', '', ''));
        $form->isUserRequest($this->_request);
        return $form;
    }

    //优惠券使用情况
    public function indexAction()
    {
        $form = $this->searchForm();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('用途', 'purposeCode', 200, '')
            ->addHeader('优惠券名称', 'title', 200, '')
            ->addHeader('优惠券类型', 'typeCode', 200, '')
            ->addHeader('面额浮动', 'isFloat', 100, '')
            ->addHeader('优惠券面额', 'amount', 150, '')
            ->addHeader('起投金额', 'investAmount', 180)
            ->addHeader('发行数量', 'count', 150)
            ->addHeader('领取数量', 'leadCount', 100)
            ->addHeader('使用数量', 'useCount', 100)
            ->addHeader('使用成本', 'useCost', 100)
            ->addHeader('投资金额', 'investAmount', 100)
            ->addHeader('使用率', 'usage', 100)
            ->addHeader('ROI', 'ROI', 100)
            ->addHeader('查看人数', 'checkUsersNum', 100)
            ->addHeader('创建时间', 'createTime', 300)
            ->addHeader('操作', 'op', 150)
            ->initJsonDataUrl($this->urlForList($form));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('优惠券使用情况')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 || !$this->_request->get('__frmCreate__')){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }


    // 使用情况
    public function infoAction(){
        $strpkey = $this->_request->get('__pkey__');
        $params = null;
        if( $strpkey ){
            $params = ['__pkey__'=>$strpkey];
        }
        $coupon =  $this->decodePkey($this->_request->get('__pkey__'));
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("订单ID", "orderNo", 300)
            ->addHeader("用户ID", "userId", 300)
            ->addHeader("产品名称", "productName", 300)
            ->addHeader("投资期限", "durationPeriodDays", 300)
            ->addHeader("预期年化收益率(%)", "interestTotal", 300)
            ->addHeader("投资时间", "ocreateTime", 300)
            ->addHeader("投资成本(元)", "useCost", 300)
            ->addHeader("投资金额", "orderAmount", 300)
            ->initJsonDataUrl($uri->uri($params, "listInfoData"));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init("使用情况")->initDatagrid($table);
        echo '优惠券ID: '.$coupon['couponId'].'    使用数量：   '.$coupon['useCount'].'    使用成本：   '.$coupon['useCost'].'    投资金额：    '.$coupon['investAmount'].'元';
        echo '<br />';
        $this->renderPage($page);
    }

    // 获取使用情况
    public function listInfoDataAction(){
        $strpkey = $this->decodePkey($this->_request->get('__pkey__'))['couponId'];
        $data = \Prj\Model\CouponFinal::couponProductAndOrderInfo($strpkey);
        foreach($data as $k => $v){
            $data[$k]['ocreateTime'] = date('Y.m.d H:i:s',strtotime($v['ocreateTime']));
            // durationPeriodDays 第一位是单位，后面的是数目
            $data[$k]['durationPeriodDays'] = substr($v['durationPeriodDays'],1).$this->durationPeriodDaysUnit[substr($v['durationPeriodDays'],0,1)];
            // 投资成本
            switch (strtoupper($v['couponType'])){
                // 优惠券
                case 'COUPON':
                case 'RATEPACKETS':
                // 现金红包
                case 'REDPACKETS':$data[$k]['useCost'] = $v['couponAmount'];break;
                // 加息券
                case 'RATECOUPON':$data[$k]['useCost'] = $v['orderAmount'] * $v['couponAmount'];break;
            }
        }
        $this->renderArray($data);
    }

    //获取列表数据
    public function listDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
//        print_r($getWhere);
        $where = [];
        $fWhere = [];
        if( $getWhere['type'] ){
            $where['typeCode'] = $getWhere['type'];
        }
        if( $getWhere['name'] ){
            $where['*title'] = '%'.$getWhere['name'].'%';
        }
        if( $getWhere['uType'] ){
            $where['purposeCode'] = $getWhere['uType'];
        }
        if( $getWhere['startdate'] ){
            $fWhere[']ymd'] = date('Ymd',strtotime($getWhere['startdate']));
        }
        if( $getWhere['enddate'] ){
            $fWhere['[ymd'] = date('Ymd',strtotime($getWhere['enddate']));
        }
        $data = \Prj\Model\Coupon::getRecords('*',$where);
        $final_tmp = \Prj\Model\CouponFinal::couponFinalList($fWhere);
        $couponType = \Prj\Model\Coupon::getAdminOption();
        $final = [];
        foreach($final_tmp as $k=>$v){
            $final[$v['couponId']] = $v;
        }
        foreach($data as $k => &$v){
            $oid = $v['oid'];
            // 用途
            $v['purposeCode'] = $this->OperateType[strtoupper($v['purposeCode'])];
            if( $v['isFloat'] =='1' ){
                $v['amount'] = '-';
            }else{
                $v['amount'] = $v['amount'] ? number_format($v['amount']/100,2) : '0';
            }
            // 类型
            $v['typeCode'] = $couponType[strtoupper($v['typeCode'])];

            // 起投金额
            $v['investAmount'] = $v['investAmount'] ? number_format($v['investAmount']/100,2) : '0';
            // 发行数量
            $v['count'] = $v['count'] ? number_format($v['count']) : '0';
            // 是否浮动
            $v['isFloat'] = $this->ISFloat[$v['isFloat']];
            // 领取数量
            $v['leadCount'] = $final[$oid]['leadCount'] ? number_format($final[$oid]['leadCount']) : '0';
            // 使用数量
            $v['useCount'] = $final[$oid]['useCount'] ? number_format($final[$oid]['useCount']) : '0';
            // 使用成本
            $v['useCost'] = $final[$oid]['useCost'] ? number_format($final[$oid]['useCost'],2) : '0';
            // 投资金额
            $v['investAmount'] = $final[$oid]['investAmount'] ? number_format($final[$oid]['investAmount']/100,2) : '0';
            // 使用率
            $v['usage'] = $final[$oid]['usage'] ? ($final[$oid]['usage']*100).'%' : '0';
            // 查看人数
            $v['checkUsersNum']  = $final[$oid]['checkUsersNum'] ? $final[$oid]['checkUsersNum'] :0;
            // ROI
            $v['ROI'] = $final[$oid]['ROI'] ? ($final[$oid]['ROI']*100).'%' : '0';
            // 操作
            $v['op'] = $this->btnLookAtDatagrid(['couponId'=>$v['oid'],'useCount'=>$v['useCount'],'useCost'=>$v['useCost'],'investAmount'=>$v['investAmount']]);
            // 创建时间
            $v['createTime'] = date('Y.m.d H:i:s',strtotime($v['createTime']));

        }
//        print_r($data);
        $this->renderArray($data);
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
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'info');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'使用情况\', mask:true,width:1200, height:800,fresh:true}">使用情况</a>&nbsp;';
    }
}