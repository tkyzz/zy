<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/9/18
 * Time: 10:27
 * Desc 渠道订单详情
 */
use Rpt\Manage\ManageIniCtrl;
use Prj\Model\ZyBusiness;

class ChannelOrderController extends Rpt\Manage\ManageIniCtrl
{

    /**
     * @desc 渠道订单搜索表单
     * @author zhuyi
     * @date 2017-09-18
     */
    protected function searchForm()
    {
        $pkey =  $this->decodePkey($this->_request->get('__pkey__'));
        $date = $pkey['ymd'] ? $pkey['ymd'] : date("Y-m-d");
        //推广方式列表
//        $spreadOption = ZyBusiness\PlatformChannel::getChannel();
//        array_unshift($spreadOption,'全部');
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'channelorderindex');

        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_qudao','',"")->initOptions(['name'=>'渠道名称','channelId'=>'渠道ID']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_channel", '', ""))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_startdate', $date, '开始'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('eq_enddate', $date, '结束'));
//            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("eq_spreadId", '', '推广方式')->initOptions($spreadOption));
        $form->isUserRequest($this->_request);
        return $form;
    }

    /**
     * @desc 渠道订单
     * @author zhuyi
     * @date 2017-09-18
     */
    public function indexAction(){
        $form = $this->searchForm();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('投资时间', 'createTime', 200, '')
            ->addHeader('投资标的', 'productName', 250, '')
            ->addHeader('投资金额(元)', 'orderAmount', 150, '')
            ->initJsonDataUrl($this->urlForList($form));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('渠道订单')
            ->initForm($form)->initDatagrid($table)->initStdBtn(null,$this->urlForList($form,'listDataDown'));
        //是否刷新
        if( $this->_request->get('fresh') == 1 || !$this->_request->get('__frmCreate__')){
            $this->renderPage($page,true);
        }else{
            $this->renderPage($page);
        }
    }

    /**
     * @desc 渠道订单获取数据
     * @author zhuyi
     * @date 2017-09-18
     */
    public function listDataAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $where = array();
        $data = [];
        $channelId = '';
        if( $getWhere['qudao'] && $getWhere['channel']){
            if( $getWhere['qudao'] == 'name' ){
                $listWhere['name'] = $getWhere['channel'];
                $channelId = \Prj\Model\ContractInfo::basicChannelList($listWhere)[0]['contractCode'];
                $channelId = substr($channelId,0,4);
            }elseif( $getWhere['qudao'] == 'channelId' ){
                $channelId = $getWhere['channel'];
            }

        }
        if( $getWhere['startdate'] ){
            $where[']date(createTime)'] = $getWhere['startdate'];
        }
        if( $getWhere['enddate'] ){
            $where['[date(createTime)'] = $getWhere['enddate'];
        }
        if( $channelId ){
            $userId = \Prj\Model\UserFinal::getRecords('uid',['*contractId'=>$channelId.'%']);
            $userId = array_column($userId,'uid');
            if( $userId ){
                $where['userId'] = $userId;
            }
        }


        if( empty($channelId) && $getWhere['channel'] ){
            $data = [];
        }else{
            $data = \Prj\Model\ZyBusiness\TradOrder::getRecords('createTime as createTime,orderAmount,productId',$where);
            foreach($data as &$v){
                $v['productName'] = \Prj\Model\Product::getRecord('productName',['productId'=>$v['productId']])['productName'];
                $v['orderAmount'] = number_format($v['orderAmount'],2);
            }
        }

        $this->renderArray($data);

    }

    /**
     * @desc 渠道订单获取数据
     * @author zhuyi
     * @date 2017-09-18
     */
    public function listDataDownAction(){
        $getWhere =  $this->_request->get('__wHeRe__');
        $getWhere = $this->decodePkey($getWhere);
        $where = array();
        $data = [];
        $channelId = '';
        if( $getWhere['qudao'] && $getWhere['channel']){
            if( $getWhere['qudao'] == 'name' ){
                $listWhere['name'] = $getWhere['channel'];
                $channelId = \Prj\Model\ContractInfo::basicChannelList($listWhere)[0]['contractCode'];
                $channelId = substr($channelId,0,4);
            }elseif( $getWhere['qudao'] == 'channelId' ){
                $channelId = $getWhere['channel'];
            }

        }
        if( $getWhere['startdate'] ){
            $where[']date(createTime)'] = $getWhere['startdate'];
        }
        if( $getWhere['enddate'] ){
            $where['[date(createTime)'] = $getWhere['enddate'];
        }
        if( $channelId ){
            $userId = \Prj\Model\UserFinal::getRecords('uid',['*contractId'=>$channelId.'%']);
            $userId = array_column($userId,'uid');
            if( $userId ){
                $where['userId'] = $userId;
            }
        }

        $arr = [];
        if( empty($channelId) && $getWhere['channel'] ){
            $data = [];
        }else{
            $data = \Prj\Model\ZyBusiness\TradOrder::getRecords('createTime as createTime,orderAmount,productId',$where);
            foreach($data as $k=>&$v){
                $arr[$k]['createTime'] = $v['createTime'];
                $arr[$k]['productName'] = \Prj\Model\Product::getRecord('productName',['productId'=>$v['productId']])['productName'];
                $arr[$k]['orderAmount'] = number_format($v['orderAmount'],2);
            }
        }


        $titleArr = ['投资时间','投资标的','投资金额'];
        //生成EXCEL
        \Lib\Misc\ArrayH::exportCsv('渠道订单统计',$titleArr,$arr);

    }

    /**
     * @desc 渠道订单参数解析
     * @author zhuyi
     * @date 2017-09-18
     */
    public function decodePkey($strpkey)
    {
        return json_decode(hex2bin($strpkey),true);
    }

    /**
     * @desc 渠道订单参数合成
     * @author zhuyi
     * @date 2017-09-18
     */
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