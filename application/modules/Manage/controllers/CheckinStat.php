<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/9/18
 * Time: 16:18
 * 签到优惠券统计
 */
class CheckinStatController extends \Rpt\Manage\ManageIniCtrl
{

    /**
     * @desc 渠道订单搜索表单
     * @author zhuyi
     * @date 2017-09-18
     */
    protected function searchForm()
    {
        $pkey =  $this->decodePkey($this->_request->get('__pkey__'));
        $sdate = $pkey['ymd'] ? $pkey['ymd'] : date("Y-m-d",strtotime('-15day'));
        $date = $pkey['ymd'] ? $pkey['ymd'] : date("Y-m-d");
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'checkinstatindex');

        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('gt_ymd', $sdate, '开始'))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('lt_ymd', $date, '结束'));
        $form->isUserRequest($this->_request);
        return $form;
    }

    /**
     * @desc 渠道订单搜索表单
     * @author zhuyi
     * @date 2017-09-18
     */
    public function indexAction()
    {
        $form = $this->searchForm();
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('签到统计')
            ->initForm($form);
        $where = bin2hex(json_encode($form->getWhere()));
//        if( date('Ymd',strtotime($where['[ymd']))- date('Ymd',strtotime($where[']ymd'])) > 30 ){
//
//        }
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        $this->renderPage($page);
        if( $where ){
            echo $this->echartsLineHtml('/manage/CheckinStat/echartsData?__wHeRe__='.$where);
            echo $this->echartsLineHtml('/manage/CheckinStat/echartsData2?__wHeRe__='.$where);
            $this->listDataAction($where);
        }else{
            echo $this->echartsLineHtml('/manage/CheckinStat/echartsData');
            echo $this->echartsLineHtml('/manage/CheckinStat/echartsData2');
            $this->listDataAction();
        }
        $this->autoSearch();
    }

    /**
     * Hand 折线图
     */
    public function echartsDataAction(){
        $where = $this->decodePkey($this->_request->get('__wHeRe__'));
        if( $where[']ymd'] ){
            $where[']ymd'] = date('Ymd',strtotime($where[']ymd']));
        }
        if( $where['[ymd'] ){
            $where['[ymd'] = date('Ymd',strtotime($where['[ymd']));
        }
        $where['*title'] = '签到%';
        $data = \Prj\Model\CouponFinal::getRecords('*',$where);
        $list = [];
        foreach($data as $k=>$v){
            $list[$v['ymd']][] = $v['ymd'];
            $list[$v['ymd']][] = $v['leadCount'];
        }
        for($i=$where[']ymd'];$i<=$where['[ymd'];$i++){
            if( empty($list[$i][0]) ){
                $list[$i][0] = $i;
                $list[$i][1] = 0;
            }
        }
        //排序
        asort($list);
        $eData = \Prj\View\Echarts\DataLine::getInstance();
        $eData->addLine('签到人数趋势');
        foreach ($list as $v){
            $eData->addPoint($v[0] , $v[1]);
        }

        $eData->render();

    }
    /**
     * Hand 折线图
     */
    public function echartsData2Action(){
        $where = $this->decodePkey($this->_request->get('__wHeRe__'));
        if( $where[']ymd'] ){
            $where[']ymd'] = date('Ymd',strtotime($where[']ymd']));
        }
        if( $where['[ymd'] ){
            $where['[ymd'] = date('Ymd',strtotime($where['[ymd']));
        }
        $where['*title'] = '签到%';
        $data = \Prj\Model\CouponFinal::getRecords('*',$where);
        $list = [];
        foreach($data as $k=>$v){
            $list[$v['ymd']][] = $v['ymd'];
            $list[$v['ymd']][] = $v['useCount']? number_format(($v['useCount']/ $v['leadCount'])*100,2):0;
        }
        for($i=$where[']ymd'];$i<=$where['[ymd'];$i++){
            if( empty($list[$i][0]) ){
                $list[$i][0] = $i;
                $list[$i][1] = 0;
            }
        }
        //排序
        asort($list);
        $eData = \Prj\View\Echarts\DataLine::getInstance();
        $eData->addLine('签到比例趋势(%)');
        foreach ($list as $v){
            $eData->addPoint($v[0] , $v[1]);
        }
        $eData->render();

    }

    public function listDataAction($where=''){
        $where = $this->decodePkey($where);
        if( $where[']ymd'] ){
            $where[']ymd'] = date('Ymd',strtotime($where[']ymd']));
        }
        if( $where['[ymd'] ){
            $where['[ymd'] = date('Ymd',strtotime($where['[ymd']));
        }
        $where['*title'] = '签到%';
        $data = \Prj\Model\CouponFinal::getRecords('*',$where);
        $html = '';

        foreach($data as $k=>$v){
            $html .= "<tr><td>{$v['ymd']}</td><td>{$v['useCost']}</td><td>{$v['leadCount']}</td><td>{$v['useCount']}</td><td>".($v['leadCount']-$v['useCount'])."</td><td>".($v['useCount']?number_format(($v['useCount']/$v['leadCount'])*100,2).'%':0)."</td></tr>";
        }
        echo '<table border="1"  class="table"><tr>
            <th>日期</th><th>优惠券面额</th><th>领取数量</th><th>使用数量</th><th>未使用数量</th><th>使用率</th></tr>'.$html.'</table></div>';
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