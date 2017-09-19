<?php
class CopartnerController extends \Yaf_Controller_Abstract
{
    protected $enableALL = array(1156, 1157, 1174, 1175);//这几个改用全订单模式
    /**
     * http://copartner.zhangyuelicai.com/rpt4copartner.php?__=copartner/simpledayrpt
     * @var \Sooh2\DB\Interfaces\DB
     */
    private $db;

    private $dbName = 'jz_db';
    private $bussinessDbName = 'zy_business';

    public function copartnerlistAction()
    {
        $ip = \Sooh2\Util::remoteIP();
        $iplist = ','.\Sooh2\Misc\Ini::getInstance()->getIni('application.zhangyueIP').',';
        if(!strpos($iplist, $ip)){
            header('Location: /404.html');
            exit;
        }
        $dbConf = \Sooh2\Misc\Ini::getInstance()->getIni('DB');
        if(isset($dbConf['jz_db'])){
            $this->db = \Sooh2\DB::getConnection($dbConf['jz_db']);
            $this->db->exec(array('set names utf8'));
        }else{
            $this->db = \Sooh2\DB::getConnection($dbConf['mysql']);
            $this->db->exec(array('set names utf8'));
        }
        $copartner= $this->db->getRecords('jz_channel_info','id,channelSource,channelName');
        foreach($copartner as $i=>$r){
            $sign = md5($r['id'].'zyc');
            $copartner[$i]['sign']=$sign;
        }
        $this->_view->assign('copartnerlist', $copartner);
    }

    protected function std($copartner){
//        print_r($copartner);
        $today = $this->_request->get('ymd');
        if(empty($today)){
            $today = date('Ymd');
            $dtToday = strtotime($today);
        }else{
            $dtToday = strtotime($today);
            $today=date('Ymd',$dtToday);
        }

        //todo: sign
        
//        $rptDay=array('新增注册'=>0,'新增绑卡'=>0,'新增投资人数'=>0,'新增投资金额'=>0,'投资金额'=>0);
        $rptDetail=array();
        
        
        //获取渠道用户当日订单信息
        $sql = "SELECT sum(newRegNum) AS newRegNum,sum(newBindNum) AS newBindNum,sum(newBoughtNum) AS newBoughtNum,
                sum(newBoughtAmount) AS newBoughtAmount,sum(boughtAmount) AS boughtAmount
                FROM {$this->dbName}.tb_channel_final WHERE ymd={$today} AND channelId like '{$copartner['channelSource']}%'";
        $todayOrders = $this->db->fetchResultAndFree($this->db->exec(array($sql)));
        //日渠道数据：新增注册，新增绑卡人数，新增投资人数，
            //新增投资金额（当天新投资用户投资的第一次的金额），投资金额（当天新老用户投资的总金额）
        $userBoughtToday=array();
        $rptDay = [
            '新增注册'=>$todayOrders[0]['newRegNum'],
            '新增绑卡'=>$todayOrders[0]['newBindNum'],
            '新增投资人数'=>$todayOrders[0]['newBoughtNum'],
            '新增投资金额'=>$todayOrders[0]['newBoughtAmount'],
            '投资金额'=>$todayOrders[0]['boughtAmount'],
            ];
//        error_log('>>>>>>>>>>>>>>>>>>>>>>.'.var_export($userBoughtToday,true));

        
        
        //获取最近一周注册用户信息
        $where = array('*contractId'=>$copartner['channelSource'].'%','>ymdReg'=>date('Ymd',$dtToday-86400*7),'[ymdReg'=>$today);//
        $userOther = $this->db->getRecords($this->dbName.'.tb_user_final_0',
            'uid,realname,phone,ymdReg,from_unixtime(bindCardTime, \'%Y%m%d\') ymdBindcard,orderTime,orderId',
            $where,'sort ymdReg');
        foreach($userOther as $r){
            $dt = strtotime($r['orderTime']);
            $tmp = array('nm'=> mb_substr($r['realname'], 0,1,'utf-8'),'phone'=>substr($r['phone'],0,4).'****'.substr($r['phone'],-3),'reg'=>$r['ymdReg'],'bind'=>'','1stbuy'=>$r['orderTime'],'prdt'=>'');
            if($r['ymdBindcard']!=19700101){
                $tmp['bind'] = $r['ymdBindcard'];
                if(!empty($r['orderTime'])){

                    $prdtId=$this->db->getOne($this->bussinessDbName.'.tpf_investor_trade_order', 'productId',
                        array('orderNo'=>$r['orderId']),//,']createTime'=>date('Y-m-d 0:00:00',$dt),'<createTime'=>date('Y-m-d 0:00:00',$dt+86400)
                        'sort createTime');
                    $tmp['prdt']= $this->db->getOne($this->dbName.'.tb_gam_product', 'productName',
                        array('productId'=>$prdtId));
                }
            }
            $rptDetail[]=$tmp;
        }
        $this->_view->assign('rptYmd',$today);
        $this->_view->assign('rptDay',$rptDay);
        $this->_view->assign('rptDetail',$rptDetail);
        $this->_view->assign('tplid','std');
    }

    protected function allorder($copartner)
    {
        $today = $this->_request->get('ymd');
        if(empty($today)){
            $today = date('Ymd');
            $dtToday = strtotime($today);
        }else{
            $dtToday = strtotime($today);
            $today=date('Ymd',$dtToday);
        }
        $this->_view->assign('rptYmd',$today);
        $this->_view->assign('tplid','allorder');
        // 查询渠道用户信息
        $tmp  = $this->db->getRecords($this->dbName.'.tb_user_final_0','uid,phone,realname',array('*contractId'=>$copartner['channelSource'].'%'));
        if(empty($tmp)){
            return ;
        }
        // 将uid设置为键
        $users = [];
        foreach($tmp as $k => $v){
            $users[$v['uid']] = $v;
        }
        // 查询订单信息
        $allOrder = $this->db->getRecords($this->bussinessDbName.'.tpf_investor_trade_order', 'userId,productId,orderAmount,createTime',
                array('userId'=> array_keys($users),']date(createTime)'=>$today,'[date(createTime)'=>$today)
                );
        // 读取产品列表
        $prdArr = $this->db->getPair($this->dbName.'.tb_gam_product', 'productId','productName');
        $this->_view->assign('trace', var_export($allOrder,true));
        $this->_view->assign('orders',$allOrder);
        $this->_view->assign('prdt',$prdArr);
        $this->_view->assign('users',$users);
        
    }
    protected $allprdt; 
    public function simpledayrptAction()
    {
        \Prj\Loger::setKv('Copartner');
        $dbConf = \Sooh2\Misc\Ini::getInstance()->getIni('DB');
        if(isset($dbConf['jz_db'])){
            $this->db = \Sooh2\DB::getConnection($dbConf['jz_db']);
            $this->db->exec(array('set names utf8'));
        }else{
            $this->db = \Sooh2\DB::getConnection($dbConf['mysql']);
            $this->db->exec(array('set names utf8'));
        }
        $sign = $this->_request->get('sn');
        $this->allprdt = $this->db->getPair($this->dbName.'.t_gam_product', 'oid', 'name');
        $copartnerid = $this->_request->get('id')-0;
        $copartner= $this->db->getRecord($this->dbName.'.jz_channel_info','id,channelSource,channelName', array('channelSource'=>$copartnerid));
        if(md5($copartner['id'].'zyc')!=$sign && 1==2){
            header('Location: /404.html');
            exit;
        }
        $this->_view->assign('copartner',$copartner['channelName']);
//        print_r($copartner);
        if(in_array($copartner['channelSource'], $this->enableALL) ){
            $this->allorder($copartner);
        }else{
            $this->std($copartner);
        }
        
    }

}