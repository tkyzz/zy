<?php
use Sooh2\DB\Cases\OrdersChk\BatchRecord as BatchRecord;
use Sooh2\DB\Cases\OrdersChk\OrderStatus as OrderStatus;
/**
 * 对账后台
 * By Hand
 */
class OrderscheckController extends \Rpt\Manage\ManageIniCtrl
{
    protected $batchTypes = array('investor'=>'投资人订单');
    protected $batchType='investor';
    protected $batchStatus = array(
        BatchRecord::status_checking=>'对账未结束',
        BatchRecord::status_confirmed=>'已确认',
        BatchRecord::status_toBeConfirm=>'待确认',
        //BatchRecord::status_refused=>'放弃',
            );
    protected $ordersTypes= array(
        'RECHARGE'=>'充值(现金券)','WITHDRAW'=>'提现','buyt'=>'购买定期','rebuy'=>'活期派息','interest'=>'回款','buyc'=>'申购活期','unbuy'=>'活期赎回'
    );
    /**
     * 用户详情
     */
    public function ordersAction()
    {
        $uid = $this->_request->get('uid');
        $userDetail ='userDetail..(todo)';
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
                ->addHeader('订单号', 'orderId', 260, '')
                ->addHeader('最后更新时间', 'dt', 120, '')
                ->addHeader('有错', 'haserr', 80, '')
                ->addHeader('错误信息', 'errs', 200, '')
                ->addHeader('订单类型', 'orderType', 120, '')
                ->addHeader('订单说明', 'orderDesc', 300, '')
                ->addHeader('订单状态', 'status', 90, '')
                ->addHeader('订单金额', 'orderAmount', 90, '')
                ->addHeader('实付金额', 'payAmount', 90, '')
                ->addHeader('手续费', 'feeAmount', 90, '')
                ->initJsonDataUrl($uri->uri(array('uid'=>$uid),'ordersdata'));
        
        $page = \Sooh2\BJUI\Pages\ListWithPannel::getInstance()
                ->init('投资人对账')
                ->initPannel($userDetail)->initDatagrid($table);
        
        $this->renderPage($page);
        

    }

    /**
     * 用户订单数据
     */
    public function ordersdataAction() {
        $uid = $this->_request->get('uid');
        list($db,$tb)= \Rpt\OrderCheck\Investor\Orders::getCopy(null)->dbAndTbName();
        $ret = $db->getRecords($tb,"orderId,FROM_UNIXTIME(dt,'%m-%d %H:%s') dt,err,orderType,orderDesc,"
                . "status1,status2,orderAmount1,orderAmount2,feeAmount1,feeAmount2,payAmount1,payAmount2",array('uid'=>$uid),'rsort dt');
        $ks = array('orderAmount','payAmount','feeAmount');
        foreach($ret as $i=>$r){
            foreach($ks as $k){
                if($r[$k.'1']==$r[$k.'2']){
                    $ret[$i][$k]=sprintf('%.2f',$r[$k.'1']);
                }else{
                    $ret[$i][$k]='pay:'.sprintf('%.2f',$r[$k.'1']).' biz:'.sprintf('%.2f',$r[$k.'2']);
                }
                unset($ret[$i][$k.'1'],$ret[$i][$k.'2']);
            }
            $k = 'status';
            if($r[$k.'1']==$r[$k.'2']){
                $ret[$i][$k]=$r[$k.'1'];
            }else{
                $ret[$i][$k]='pay:'.$r[$k.'1'].' biz:'.$r[$k.'2'];
            }
            unset($ret[$i][$k.'1'],$ret[$i][$k.'2']);
            $ret[$i]['orderType']=$this->ordersTypes[$r['orderType']];
            if($r['err']== \Rpt\OrderCheck\Investor\Orders::err_none){
                $ret[$i]['haserr'] = 'n';
                $ret[$i]['err'] = '';
            }else{
                $ret[$i]['haserr'] = 'Y';
            }
        }
        $this->renderArray($ret);
    }
    /**
     * 用户一栏（对账情况）
     */
    public function usersAction()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
                ->addHeader('用户id', 'uid', 120, '')
                ->addHeader('有无错误', 'hasError', 80, '')
                ->addHeader('错误情况', 'errors', 400, '')
                ->addHeader('余额情况', 'balance', 400, '')
                
                ->addHeader('操作', 'op', 200, '')
                ->initJsonDataUrl($uri->uri(null,'usersdata'));
        
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
                ->init('投资人对账')->initStdBtn(null, null)
                ->initDatagrid($table);
        
        $this->renderPage($page);
    }
    /**
     * 用户一览 - 数据
     */
    public function usersdataAction() {
        $ret = array();
        $url0 = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('batchType'=>'investor','uid'=>'{uid}'), 'orders');
        list($db,$tbAcc)= \Rpt\OrderCheck\Investor\AccountMirror::getCopy(null)->dbAndTbName();
        $rs = $db->getRecords($tbAcc,'uid,hasError,errors, balance',null,'rsort hasError');
        //TODO: 新旧余额，已确认和未确认的用户（两张表）
        foreach($rs as $r){
            $ret[]= array(
                'uid'=>$r['uid'],
                'hasError'=>$r['hasError']?1:0,
                'errors'=>$r['errors'],
                'balance'=>$r['balance'],
                'op'=> $this->btnNewtab(str_replace('{uid}', $r['uid'], $url0), '投资人详情', 'orderscheck-orderslist', '投资人详情'),
            );
        }
        $this->renderArray($ret);
    }

    
    /**
     * 所有对账任务批次一览
     */
    public function indexAction() {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
                ->addHeader('日期', 'batchYmd', 120, '')
                ->addHeader('类型', 'batchType', 120, '')
                ->addHeader('状态', 'batchStatus', 80, '')
                ->addHeader('摘要', 'batchBrief', 700, '')
                ->addHeader('投资人列表', 'op', 200, '')
                ->initJsonDataUrl($uri->uri(null,'indexdata'));
        
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
                ->init('对账一览')->initStdBtn(null, null)
                ->initDatagrid($table);
        
        $this->renderPage($page);
    }

    /**
     * 所有对账任务批次 - 数据
     */
    public function indexdataAction() {
        $ret = array();
        $db = \Rpt\OrderCheck\Investor\BatchRecord::getCopy(null)->dbWithTablename();
        $rs = $db->getRecords($db->kvobjTable(), '*',null,'rsort batchYmd');
        $url0 = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('batchType'=>'investor','batchYmd'=>'{batchYmd}'), 'users');
        foreach($rs as $r){
            $ret[]= array(
                'batchYmd'=>$r['batchYmd'],
                'batchType'=>$this->batchTypes['investor'],
                'batchStatus'=>$this->batchStatus[$r['batchStatus']],
                'batchBrief'=>$r['smalllog'],
                //<a href="/manage/orderscheck/index" data-toggle="navtab" data-options="{id:'manage-orderscheck', title:'对账php'}"><i class="fa fa-caret-right"></i>&nbsp;对账php</a>
                'op'=> $this->btnNewtab(str_replace('{batchYmd}', $r['batchYmd'], $url0), '投资人列表', 'orderscheck-userlist', '最新情况'),
            );
        }
        $this->renderArray($ret);
    }

}
