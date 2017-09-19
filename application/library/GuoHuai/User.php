<?php
namespace  Libs\GuoHuai;
/**
 * !!!代码不要删，新版系统里还在用它做查询！！！！
 * 活期赎回：settlement.order里有，订单号非数字那种，要到mimosa.publisher_incomehole里查
 * 冲销： 目前是提现驳回会在mimosa里有一个冲销记录，settlement里没有记录
 * 现在是一次性还本付息，所以定期还本付息可以直接查
 * 定期还本付息记录里已经是本息总额，不用再统计定期的派息
 * 申购订单 的orderAmount 包含了 voucherAmount
 * 提现订单的orderAmount 里包括了 feeAmount??????????????????????
 * 流标回款   orderAmount里包括了voucherAmount吗？如果包含，计算余额的时候要排除
 * 还本付息订单里orderAmount 包括本和息
 * settlement里的余额是包含提现冻结中的
 * settlement 提现：tpye=2的时候status 0 等待处理， 1成功，5驳回  4 交易处理中
 * LoadByPhone 是 用户对账的入口：根据手机号查基本信息，订单信息，最后计算余额
 * @author simon.wang
 */
class User {
    /**
     * 修复新手标志位
     * @param unknown $phone
     */
    public function patch($phone)
    {
        if($this->phone==$phone){
            $uid = $this->ucUID;
        }else{
            $uid = $this->db->getOne('gh_jz_uc.t_wfd_user', 'oid', array('userAcc'=>$phone));
        }
        $redis = $this->redis->getRecord('m', '*',array('investor'=>$uid));
        

        $mysql = $this->db->getRecord('gh_jz_mimosa.t_money_investor_baseaccount', '*',array('userOid'=>$uid));
        if($mysql['isFreshman']!=$redis['isFreshman']){
            $this->redis->updRecords('m', array('isFreshman'=>$mysql['isFreshman']),array('investor'=>$uid));
            return 'fixed to ' . $mysql['isFreshman'];
        }else{
            return '--';
        }
        //echo "mysql: {$mysql['isFreshman']} vs redis: {$redis['isFreshman']}\n";
//         $this->lock['isFreshman'] = $r['isFreshman'];    
//         if($this->lock['isFreshman']!=$investor['isFreshman']){
//             $this->lock['isFreshman'] = 'mimosa:'.$this->lock['isFreshman']."; redis:".$investor['isFreshman'];
//             $this->errFound.='新手标志位不一致;';
//         }
    }
    public function lock($phone)
    {
        if($this->phone==$phone){
            $uid = $this->ucUID;
        }else{
            $uid = $this->db->getOne('gh_jz_uc.t_wfd_user', 'oid', array('userAcc'=>$phone));
        }
        $this->redis->exec(array(array('sAdd','m:u:ul',$uid)));
        $this->db->updRecords('gh_jz_uc.t_wfd_user', array('status'=>'forbidden'),array('oid'=>$uid));
        $this->db->updRecords('gh_jz_mimosa.t_money_investor_baseaccount', array('status'=>'forbidden'),array('userOid'=>$uid));
        $this->redis->updRecords('m', array('status'=>'forbidden'),array('investor'=>$uid));
        $u = $this->redis->exec(array(array('get', 'c:g:u:ui:'.$uid)));
        $u = json_decode($u,true);
        if(!empty($u)){
            $u['user']['status']='forbidden';
            $this->redis->exec(array(array('set', 'c:g:u:ui:'.$uid, json_encode($u))));
        }else{
            throw  new \ErrorException('Redis: '.'c:g:u:ui:'.$uid.' json_decode failed');
        }
    }
    /*
         string(744) "{"clientId":"ca396b55c07ee60a",
         "user":{"channelid":"100620170310400000","createTime":1494632769000,
         "memberOid":"117UID2017051300000198","oid":"ff8080815b9f2adb015bff0e26ea25fd",
         "payPwd":"d2517542737620f655114df46469359a122963f8","paySalt":"9f40d017da6a9317",
         "salt":"96d25c8684f6cf6b","sceneId":1013164,"source":"frontEnd",
         "status":"normal","updateTime":1494750897232,
         "userAcc":"13810073769","userPwd":"23173447bcda4ab942e55d7e8f51cfc6501363a0"},
         "userBank":[{"bankName":"中国工商银行","cardNumb":"6222020200095370751",
         "createTime":1494806806047,"idNumb":"110103198309061810",
         "name":"孟宪堃","oid":"ff8080815b9f2adb015c096dbe1f30ca",
         "phoneNo":"13810073769","updateTime":1494806806047}],
         "userLoginInfo":{"pwdErrorTimes":0},"wxopenids":{}}"
     */
    public function unlock($phone)
    {
        if($this->phone==$phone){
            $uid = $this->ucUID;
        }else{
            $uid = $this->db->getOne('gh_jz_uc.t_wfd_user', 'oid', array('userAcc'=>$phone));
        }
        
        $this->redis->exec(array(array('sRem','m:u:ul',$uid)));
        
        $this->db->updRecords('gh_jz_uc.t_wfd_user', array('status'=>'normal'),array('oid'=>$uid));
        $this->db->updRecords('gh_jz_mimosa.t_money_investor_baseaccount', array('status'=>'normal'),array('userOid'=>$uid));
        
        //$this->redis->updRecords('m', array('status'=>'normal'),array('investor'=>$uid));
        $u = $this->redis->exec(array(array('get', 'c:g:u:ui:'.$uid)));
        $u = json_decode($u,true);
        if(!empty($u)){
            $u['user']['status']='normal';
            $this->redis->exec(array(array('set', 'c:g:u:ui:'.$uid, json_encode($u))));
        }else{
            throw  new \ErrorException('Redis: '.'c:g:u:ui:'.$uid.' json_decode failed');
        }
    }
    protected function infoInRedis()
    {
    
        $investor = $this->redis->getRecord('m', '*',array('investor'=>$this->ucUID));
        $this->balance['redis'] = round($investor['balance']/100);
        if($this->lock['isFreshman']!=$investor['isFreshman']){
            $this->lock['isFreshman'] = 'mimosa:'.$this->lock['isFreshman']."; redis:".$investor['isFreshman'];
            //$this->errFound.='新手标志位不一致;';
        }
        $this->froze['redis']=round(($investor['investWayBalance']+$investor['onWayBalance'])/100);
        /**
         * array(6) {
         ["balance"]=>"500000000"
         ["isFreshman"]=> "yes"
         ["onWayBalance"]=> "0"
         ["userOid"]=> "ff8080815b9f2adb015bff0e26ea25fd"
         ["monthWithdrawCount"]=> "0"
         ["investWayBalance"]=> "0"
         }         */
        $u = $this->redis->exec(array(array('get', 'c:g:u:ui:'.$this->ucUID)));
        /*
         string(744) "{"clientId":"ca396b55c07ee60a",
         "user":{"channelid":"100620170310400000","createTime":1494632769000,
                 "memberOid":"117UID2017051300000198","oid":"ff8080815b9f2adb015bff0e26ea25fd",
                "payPwd":"d2517542737620f655114df46469359a122963f8","paySalt":"9f40d017da6a9317",
                 "salt":"96d25c8684f6cf6b","sceneId":1013164,"source":"frontEnd",
                "status":"normal","updateTime":1494750897232,
                 "userAcc":"13810073769","userPwd":"23173447bcda4ab942e55d7e8f51cfc6501363a0"},
         "userBank":[{"bankName":"中国工商银行","cardNumb":"6222020200095370751",
                 "createTime":1494806806047,"idNumb":"110103198309061810",
                 "name":"孟宪堃","oid":"ff8080815b9f2adb015c096dbe1f30ca",
                 "phoneNo":"13810073769","updateTime":1494806806047}],
         "userLoginInfo":{"pwdErrorTimes":0},"wxopenids":{}}"
         */
        if(!empty($u)){
            $u = json_decode($u,true);
            if($u['user']['status']!=='normal'){
                $this->lock['redisUser']='locked';
            }
            $this->bindCards['bankcard']['redis']=empty($u['userBank']['cardNumb'])?'':$u['userBank']['cardNumb'];
        }
    
        if(isset($this->lockerA_redis[$this->ucUID])){
            $this->lock['redisList']='locked';
        }
    }
    public function findUserByOrderNo($orderNo)
    {
        $memberid= $this->db->getOne('gh_jz_settlement.t_account_order', 'userOid',array('orderNo'=>$orderNo));
        return $this->db->getOne('gh_jz_uc.t_wfd_user', 'userAcc', array('memberOid'=>$memberid));
    }
    public function findlockedAction()
    {
        $phones = $this->db->getCol('gh_jz_uc.t_wfd_user_bank', 'userOid');
        $lockedInUc = $this->db->getCol('gh_jz_uc.t_wfd_user', 'oid',array('oid'=>$phones,'!status'=>'normal'));
        $lockedInMimosa = $this->db->getCol('gh_jz_mimosa.t_money_investor_baseaccount', 'userOid',array('userOid'=>$phones,'!status'=>'normal'));
        $lockedInRedisOfUC=array();
        foreach ($phones as $uid){
            $u = $this->redis->exec(array(array('get', 'c:g:u:ui:'.$uid)));
            if(!empty($u)){
                $u = json_decode($u,true);
                if($u['user']['status']!=='normal'){
                    $lockedInRedisOfUC[]=$uid;
                }
            }
        }
        $phones = array_merge($lockedInUc,$lockedInMimosa,$lockedInRedisOfUC,$this->lockerA_redis);
        $phones = $this->db->getPair('gh_jz_uc.t_wfd_user', 'userAcc','oid',array('oid'=>$phones));
        foreach($phones as $phone=>$uid){
            if(in_array($uid, $lockedInUc)){
                $phones[$phone] .= ", lockedInUc";
            }
            if(in_array($uid, $lockedInMimosa)){
                $phones[$phone] .= ", lockedInMimosa";
            }
            if(in_array($uid, $lockedInRedisOfUC)){
                $phones[$phone] .= ", lockedInRedisOfUC";
            }
            if(isset($this->lockerA_redis[$uid])){
                $phones[$phone] .= ", lockedByCheck";
            }
        }
        return $phones;
    }
    public function findAllInvestor()
    {
        $phones = $this->db->getCol('gh_jz_uc.t_wfd_user_bank', 'userOid');
        return $this->db->getCol('gh_jz_uc.t_wfd_user', 'userAcc',array('oid'=>$phones));
    }    
    public function findWithdraw($ymd)
    {
        throw new \ErrorException('not support');
//         $members = $this->db->getCol('gh_jz_settlement.t_bank_payment', 'userOid',
//             array('&'=>array('type'=>'02',
//                 '|1'=>array(
//                     '(operatorStatus IS NOT NULL AND auditOperatorStatus IS NULL)',
//                     '(updateStatus IS NOT NULL AND auditUpdateStatus IS NULL)',
//                     '(resetOperatorStatus IS NOT NULL AND auditResetOperatorStatus IS NULL)'
//                 )
//                 )));
//         $members = $this->db->getCol('gh_jz_uc.t_wfd_user', 'userAcc',array('memberOid'=>$members));
//         return $members;
    }
    public function username($userPhones)
    {
        $tmp = $this->db->getPair('gh_jz_uc.t_wfd_user', 'userAcc', 'oid',array('userAcc'=>$userPhones));
        $r = $this->db->getPair('gh_jz_uc.t_wfd_user_bank', 'userOid', 'name',array('userOid'=>$tmp));
        foreach ($tmp as $tel=>$id){
            $tmp[$tel]=$r[$id];
        }
        return $tmp;
    }

    public function __construct()
    {
        $this->dbINI = \Sooh2\Misc\Ini::getInstance()->getIni('DB');
        $this->db = \Sooh2\DB::getConnection($this->dbINI['mysql']);
        $this->redis = \Sooh2\DB::getConnection($this->dbINI['redis']);
        $this->db->exec(array('set names utf8'));
        $this->prdts = $this->db->getPair('gh_jz_mimosa.t_gam_product', 'oid', 'name');
        $this->currentProducts = $this->db->getCol('gh_jz_mimosa.t_gam_product','oid',array('type'=>'PRODUCTTYPE_02'));
        foreach ($this->currentProducts as $k){
            $this->prdts[$k] = '#'.$this->prdts[$k];
        }
        $this->currentProducts = array_combine($this->currentProducts, range(1, sizeof($this->currentProducts)));
        //$this->prdtsRepayed = $this->db->getCol('gh_jz_mimosa.t_gam_product', 'oid', array('repayInterestStatus'=>'repayed'));
        $this->prdtsRepayed = $this->db->getCol('gh_jz_mimosa.t_gam_product', 'oid', array('state'=>'CLEARED'));
        foreach ($this->prdtsRepayed as $k){
            $this->prdts[$k] = $this->prdts[$k].'!';
        }
        $this->lockerA_redis = $this->redis->exec(array(array('sGetMembers', 'm:u:ul')));
        if(!empty($this->lockerA_redis)){
            $this->lockerA_redis = array_combine($this->lockerA_redis, $this->lockerA_redis);
        }
    }
    protected $lockerA_redis;
    protected $prdts;
    protected $prdtsRepayed;
    public $dbINI = array(
        'mysql'=>array('server'=>'127.0.0.1','port'=>3306,'dbType'=>'Myisam','pass'=>'c7La7bU6Q%k7','dbName'=>'gh_jz_uc','user'=>"root"),
        'redis'=>array('server'=>'127.0.0.1','port'=>6379,'dbType'=>'Redis','pass'=>'AK90QFXLuS4sbQ4B','dbName'=>0,'user'=>"ignore"),
    );
    /**
     *
     * @var \Sooh2\DB\Interfaces\DB
     */
    public $redis;
    /**
     * 
     * @var \Sooh2\DB\Interfaces\DB
     */
    public $db;
    public function LoadByPhone($phone)
    {
        $r = $this->db->getRecord('gh_jz_uc.t_wfd_user', '*',array('userAcc'=>$phone));
        if(empty($r)){
            throw new \ErrorException('user '.$phone.' not found');
        }
        if($r['status']!='normal'){
            $this->lock = array('uc'=>'locked');
        }else{
            $this->lock = array();
        }
        $this->phone=$phone;
        $this->memberId=$r['memberOid'];
        $this->ucUID = $r['oid'];
        $this->contractId = $r['channelid'];
        
        $r = $this->db->getRecord('gh_jz_uc.t_wfd_user_bank', '*',array('userOid'=>$this->ucUID));
        $this->realname = $r['name'];
        $this->bindCards=array(
            'bankcard'=> array('uc'=>$r['cardNumb'])
        );
        
        $this->balance=array('mimosa'=>0,'settlement'=>0,);
        $this->froze=array('mimosa'=>0,'settlement'=>0,);
        $this->orders = array();
        $this->ordersTime=array();
        $this->errFound='';
        $this->arrCalc=array();
        $this->currentDeposit=0;
        $this->timDeposit=0;
        $this->prdtHold=array();
        $this->infoInMimosa();
        $this->infoInSettlement();
        $this->infoInRedis();
        $this->checkOrders();
        
        
        //$this->infoInRedis();
    }
    
    protected $prdtHold;
    protected function infoInMimosa()
    {
        $r = $this->db->getRecord('gh_jz_mimosa.t_money_investor_baseaccount', '*',array('userOid'=>$this->ucUID));
        $this->lock['isFreshman'] = $r['isFreshman'];
        if($r['status']!=='normal'){
            $this->lock['mimosa'] = 'lockecd';
        }
        $this->balance['mimosa']=round($r['balance']*100);
        $this->froze['mimosa'] = round($r['investWayBalance']*100)+round($r['onWayBalance']*100);
        $this->mimosaUID=$r['oid'];
        $investorId = $r['oid'];
        
           //申购赎回等交易记录
        $rs = $this->db->getRecords('gh_jz_mimosa.t_money_investor_tradeorder', '*',array('investorOid'=>$investorId));
        foreach($rs as $r){
            //echo json_encode($r)."\n";
            $orderId = $r['orderCode'];
            $this->markOrderTime($r['createTime'], $orderId);
            if(isset($this->orders[$orderId])){
                $this->orders[$orderId]['OERROR']='duplicate record found';
            }else{
                $this->orders[$orderId]['type']['mimosa']=$type=$this->orderTypeByMimosa($r['orderType']);
                $this->orders[$orderId]['product'] = $this->prdts[$r['productOid']];
                $this->orders[$orderId]['status']['mimosa']=$this->orderStatusByMimosa($r['orderStatus']);
                $this->orders[$orderId]['holdStatus']=$this->holdStatusByMimosa($r['holdStatus']);
                if($type=='普赎' || $type=='还本/付息' || $type=='清盘普赎' || $type=='流标退款'){
                    $this->orders[$orderId]['orderAmount']['mimosa']=round($r['orderAmount']*100);
                    $this->orders[$orderId]['voucherAmount']['mimosa']=0;
                    $this->orders[$orderId]['feeAmount']['mimosa']-0;
                }else{
                    $this->orders[$orderId]['orderAmount']['mimosa']=round($r['payAmount']*100)+round($r['couponAmount']*100);
                    $this->orders[$orderId]['voucherAmount']['mimosa']=round($r['couponAmount']*100);
                }
                if($type=='投资' && $this->isMimosaStatusSuccess($this->orders[$orderId]['status']['mimosa'])){
                    $this->prdtHold[$r['productOid']]['mimosa.user']+=$this->orders[$orderId]['orderAmount']['mimosa'];
                }
                $this->orders[$orderId]['time'] = $r['createTime'];
            }
        }
        if(!empty($this->prdtHold)){
            $rs = $this->db->getPair('gh_jz_mimosa.t_money_publisher_hold', 'productOid', 'totalInvestVolume',array('investorOid'=>$this->mimosaUID,'!accountType'=>'SPV'));
            foreach($rs as $id=>$n){
                $this->prdtHold[$id]['mimosa.hold'] = round($n*100);
            }
        }
        //var_dump($this->prdtHold);
        foreach ($this->prdtHold as $id=>$r){
            if((substr($this->prdts[$id],-1)!='!') && $r['mimosa.hold']!=$r['mimosa.user']){
                $this->errFound.="mimosa产品户金额不匹配（{$id}:".$this->prdts[$id].":".json_encode($r)."）, ";
            }
        }
           //充值、提现、现金红包的交易记录
        $rs = $this->db->getRecords('gh_jz_mimosa.t_money_investor_bankorder', '*',array('investorOid'=>$investorId));
        foreach($rs as $r){
//            echo ">>".var_export($r,true);
            $orderId = $r['orderCode'];
            $this->markOrderTime($r['createTime'], $orderId);
            if(isset($this->orders[$orderId])){
                $this->orders[$orderId]['OERROR']='duplicate record found';
            }else{
                $this->orders[$orderId]['type']['mimosa']=$this->orderTypeByMimosa($r['orderType']);
                $this->orders[$orderId]['status']['mimosa']=$this->orderStatusByMimosa($r['orderStatus']);
                $this->orders[$orderId]['orderAmount']['mimosa']=round($r['orderAmount']*100);
                if($r['feePayer']=='user'){
                    $this->orders[$orderId]['feeAmount']['mimosa']=round($r['fee']*100);
                }else{
                    $this->orders[$orderId]['feeAmount']['mimosa']=0;
                }
                $this->orders[$orderId]['time'] = $r['createTime'];
            }
        }
    }
    protected $lock;
    protected function infoInSettlement()
    {
        //         $r = $this->db->getRecord('gh_jz_mimosa.t_money_investor_baseaccount', '*',array('userOid'=>$this->ucUID));
        //         $this->balance['mimosa']=$r['balance'];
        //         $this->froze['mimosa'] = $r['investWayBalance']+$r['onWayBalance'];
        //         $investorId = $r['oid'];
        $r = $this->db->getPair('gh_jz_settlement.t_account_info', 'accountType', 'balance', array('userOid'=>$this->memberId,'accountType'=>array('05','10')));

        $this->froze['settlement'] = round($r['05']*100);
        $this->balance['settlement']=round($r['10']*100)-round($r['05']*100);
        //echo "  EEE    -----        ".round($r['05']*100)." vs ".(round($r['10']*100)-round($r['05']*100)).' == '.json_encode($this->froze)."\n";
        
        $incomeFound=array();
        $rs = $this->db->getRecords('gh_jz_settlement.t_account_order', '*',array('userOid'=>$this->memberId));
        foreach($rs as $r){
            
            $type=$this->orderTypeBySettlement($r['orderType']);
            if($type=='增加发行额'){
                continue;
            }
            $prdt = $r['relationProductNo'];
            
            //if($type=='赎回'||$type=='派息'){//赎回和派息的时候，没找到发起订单，使用产品作为订单号 ，定期始终是 0@sdfghaiuhiur
            //    $orderId = $prdt.'@'.substr($r['createTime'],0,10);
            //    $incomeFound[]=$prdt;
            //}else{
                $orderId = $r['orderNo'];
                if(strlen($orderId)>18 && ($type=='派息' || $type=='赎回')){//活期赎回
                    $orderId = $prdt.'@'.substr($r['createTime'],0,10);
                    $incomeFound[]=$prdt;
                }
            //}
            $this->markOrderTime($r['createTime'], $orderId);
            $this->orders[$orderId]['status']['settlement']=$this->orderStatusBySettlement($r['orderStatus']);
            if(!empty($r['relationProductNo'])){
                $this->orders[$orderId]['product']=$this->prdts[$r['relationProductNo']];
            }
            $this->orders[$orderId]['type']['settlement']=$type;
            
            if($type=='充值' || $type=='提现'){
                $this->orders[$orderId]['orderAmount']['settlement']=round($r['balance']*100);//sprintf('%.2f',(round($r['balance']*100)-round($r['fee']*100))/100);
                $this->orders[$orderId]['feeAmount']['settlement']=round($r['fee']*100);
            }elseif($type=='派息'){
                $this->orders[$orderId]['incomeAmount']['settlement']=round($r['balance']*100);
            }elseif($type=='赎回'){
                $this->orders[$orderId]['orderAmount']['settlement']=round($r['balance']*100);
            }else{
                $this->orders[$orderId]['orderAmount']['settlement']=round($r['balance']*100)+round($r['voucher']*100);
                $this->orders[$orderId]['voucherAmount']['settlement']=round($r['voucher']*100);
            }
        }
        $rs1 = $this->db->getRecords('gh_jz_mimosa.t_money_publisher_hold','productOid,holdVolume as type01,holdTotalIncome as Type02',array('investorOid'=>$this->mimosaUID,'productOid'=>$this->currentProducts,''));
        $rs2 = array();
        foreach($rs1 as $r){
            if($r['type01']>0){
                $rs2[$r['productOid']]['01']=round($r['type01']*100);
            }
            if($r['type02']>0){
                $rs2[$r['productOid']]['02']=round($r['type02']*100);
            }
        }
        $rs1 = $this->db->getRecords('gh_jz_settlement.t_account_info','relationProduct,accountType,balance',array('userOid'=>$this->memberId,'relationProduct'=>$this->currentProducts));
        foreach($rs1 as $r){
            if($r['balance']>0){
                $pid = $r['relationProduct'];
                $cmp = round($r['balance']*100);
                if($cmp==$rs2[$pid][$r['accountType']]){
                    unset($rs2[$pid][$r['accountType']]);
                    if(empty($rs2[$pid])){
                        unset($rs2[$pid]);
                    }
                }
            }
        }
        if(!empty($rs2)){
            $this->errFound .= '活期（'. implode(',', array_keys($rs2)).'）产品户金额不对；';
        }
        
//         //根据产品，从mimosa那里查出派息赎回记录 -- 定期
//         $rs = $this->db->getRecords('gh_jz_mimosa.t_money_publisher_hold', 'productOid,totalInvestVolume,totalBaseIncome,updateTime',array('investorOid'=>$this->mimosaUID,'productOid'=>$incomeFound));
//         foreach ($rs as $r){
//             $prdt = $r['productOid'];
//             $orderId = $prdt.'@'.substr($r['updateTime'],0,10);
//             $this->orders[$orderId]['incomeAmount']['mimosa']=round($r['totalBaseIncome']*100);
//             $this->orders[$orderId]['orderAmount']['mimosa']=round($r['totalInvestVolume']*100)+round($r['totalBaseIncome']*100);
//         }
//         //根据产品，从mimosa那里查出派息赎回记录 -- 活期
        if(!empty($incomeFound)){
            $rs = $this->db->getRecords('gh_jz_mimosa.t_money_publisher_investor_holdincome', 'productOid,incomeAmount,updateTime',array('investorOid'=>$this->mimosaUID,'productOid'=>$incomeFound));
            foreach ($rs as $r){
                $prdt = $r['productOid'];
                $orderId = $prdt.'@'.substr($r['updateTime'],0,10);
                $this->orders[$orderId]['status']['mimosa']=$this->orderStatusByMimosa('done');
                $this->orders[$orderId]['type']['mimosa']=$this->orderTypeByMimosa('repayInterest');
                $this->orders[$orderId]['incomeAmount']['mimosa']=round($r['incomeAmount']*100);
                //$this->orders[$orderId]['orderAmount']['mimosa']=round($r['totalInvestVolume']*100)+round($r['totalBaseIncome']*100);
            }
        }
    }
    protected function markOrderTime($time,$orderId)
    {
        $dt = strtotime($time);
        while(isset($this->ordersTime[$dt])){
            $dt++;
        }
        $this->ordersTime[$dt]=$orderId;
    }
    protected function checkOrders()//检查订单
    {
        $calc=0;
        $errFound=false;
        $froze = 0;
        $ignoreByFailed = array('已拒绝','已作废','申请失败','支付失败','已申请');
        foreach($this->orders as $orderId=>$r){
            //忽略  两边都是未成功的状况（包括待支付）
            
            if (in_array($r['status']['mimosa'],$ignoreByFailed)){
                if(empty($r['status']['settlement']) || $r['status']['settlement']=='失败'){
                    unset($this->orders[$orderId]);
                    continue;
                }
            }
            if($r['status']['settlement']=='失败'){
                if(empty($r['status']['mimosa'])){
                    unset($this->orders[$orderId]);
                    continue;
                }
            }
            if($r['type']['mimosa']=='冲销' && empty($r['status']['settlement'])){
                unset($this->orders[$orderId]);
                continue;
            }
            if($r['type']['mimosa']=='充值' && $r['status']['mimosa']=='待支付'){
                unset($this->orders[$orderId]);
                continue;
            }

            //统一订单类型说明
            if($r['type']['mimosa']=='投资' && $r['type']['settlement']=='申购'){
                $this->orders[$orderId]['type']='投资';
            }elseif($r['type']['mimosa']=='流标退款' && $r['type']['settlement']=='赎回'){
                $this->orders[$orderId]['type']='流标退款';
            }elseif($r['type']['mimosa']=='清盘普赎' && $r['type']['settlement']=='赎回'){
                $this->orders[$orderId]['type']='清盘普赎';
            }elseif($r['type']['mimosa']=='普赎' && $r['type']['settlement']=='赎回'){
                $this->orders[$orderId]['type']='普赎';
            }elseif($r['type']['mimosa']=='还本/付息' && $r['type']['settlement']=='赎回'){
                $this->orders[$orderId]['type']='还本/付息';
            }elseif($r['type']['mimosa']== $r['type']['settlement']){
                $this->orders[$orderId]['type']=$r['type']['mimosa'];
            }elseif($r['type']['mimosa']=='付息' && $r['type']['settlement']=='派息'){
                $this->orders[$orderId]['type']='付息';
            }else{
                $errFound=true;
                $this->orders[$orderId]['OERROR'].='订单类型不匹配；';
            }
            if($this->orders[$orderId]['type']=='普赎'){
                unset($this->orders[$orderId]['holdStatus']);
            }

            //检查交易金额
            if($r['orderAmount']['mimosa']==$r['orderAmount']['settlement']){
                $this->orders[$orderId]['orderAmount']=$r['orderAmount']['mimosa'];
            }else{
                $errFound=true;
                $this->orders[$orderId]['OERROR'].='订单金额错误；';
            }
            
            if(isset($r['voucherAmount']['mimosa'])){
                if($r['voucherAmount']['mimosa']==$r['voucherAmount']['settlement']){
                    $this->orders[$orderId]['voucherAmount']=$r['voucherAmount']['mimosa'];
                }else{
                    $errFound=true;
                    $this->orders[$orderId]['OERROR'].='抵扣券金额错误；';
                }
            }
               
            
            if(isset($r['feeAmount']['mimosa'])){
                if($r['feeAmount']['mimosa']==$r['feeAmount']['settlement']){
                    $this->orders[$orderId]['feeAmount']=$r['feeAmount']['mimosa'];
                }else{
                    $errFound=true;
                    $this->orders[$orderId]['OERROR'].='手续费金额错误；';
                }
            }
                
            if(isset($r['incomeAmount']['mimosa'])){
                if($r['incomeAmount']['mimosa']==$r['incomeAmount']['settlement']){
                    $this->orders[$orderId]['incomeAmount']=$r['incomeAmount']['mimosa'];
                }else{
                    $errFound=true;
                    $this->orders[$orderId]['OERROR'].='利息金额错误；';
                }
            }
            
            $success = array(
                'paySuccess'=>'支付成功',
                'accepted'=>'已受理',
                'confirmed'=>'份额已确认',
                'done'=>'交易成功',
            );
               //检查订单状态
            if($r['status']['settlement']=='成功'){
                if($this->isMimosaStatusSuccess($r['status']['mimosa'])){
                    $this->orders[$orderId]['status'] = $r['status']['mimosa'];
                }else{
                    $errFound=true;
                    $this->orders[$orderId]['OERROR'].='订单状态不匹配；';
                }
            
                //没出错的情况下，尝试重新计算余额；
                if(empty($this->orders[$orderId]['OERROR'])){
                    $this->calcInOrder( $orderId);
                }
            }else{
                if($r['status']['settlement']=='受理' && $r['status']['mimosa']=='待支付'){
                    $this->orders[$orderId]['status'] = $r['status']['mimosa'];
                    $this->calcInOrder( $orderId);
                }else{
                    if($this->isMimosaStatusSuccess($r['status']['mimosa'])){
                        $errFound=true;
                        $this->orders[$orderId]['OERROR'].='订单状态不匹配；';
                    }else{
                        $this->orders[$orderId]['status'] = $r['status']['mimosa'];
                    }
                }
                
            }
        }
        if($errFound){
            $this->errFound .='有错误订单；';
        }
        //echo ">1>".json_encode($this->froze)."\n";
        if($this->froze['mimosa']!=$this->froze['settlement']){
            $this->errFound.='用户冻结余额不匹配；';
        }elseif($this->froze['redis']!=$this->froze['settlement']){
            $this->errFound.='用户冻结余额不匹配；';
        }else{
            $this->froze =$this->froze['mimosa'];
        }
        //echo ">2>".json_encode($this->froze)."\n";
        //$this->balance['calcMap'] = $this->arrCalc;
        //         $this->balance['calcMap'][0] = array_sum($this->arrCalc);
        $this->balance['calc'] = array_sum($this->arrCalc);
        
        if($this->balance['mimosa']!=$this->balance['settlement']){
            $this->errFound.='用户余额不匹配；';
        }elseif($this->balance['mimosa']!=$this->balance['calc']){
            $this->errFound.='用户余额不匹配；';
        }elseif($this->balance['redis']!=$this->balance['calc']){
            $this->errFound.='用户余额不匹配；';
        }else{
            $this->balance = $this->balance['mimosa'];
        }
        if(!is_array($this->froze)){
            if($this->froze>0){
                if($this->froze!=-$this->arrCalc['withdrawFrose']){
                    $this->errFound.='冻结金额不对；';
                }
            }
        }
        if(!is_array($this->balance)){
            if($this->balance<0){
                $this->errFound.='用户余额<0；';
            }
        }

    }
    private $arrCalc;
    private function calcInOrder($orderId)
    {
        $orderAmount = $this->orders[$orderId]['orderAmount'];
        $voucherAmount= $this->orders[$orderId]['voucherAmount'];
        $feeAmount = $this->orders[$orderId]['feeAmount'];
        $incomeAmount= $this->orders[$orderId]['incomeAmount'];
        $prdt = $this->orders[$orderId]['product'];
        $trace = " $prdt $orderId {$this->orders[$orderId]['status']} {$this->orders[$orderId]['type']}";

        switch ($this->orders[$orderId]['type']){
            case '流标退款':
                //echo "calc 普赎  + $orderAmount $trace \n";
                $this->arrCalc['returnTime'] += $orderAmount;
                if($prdt[0]=='#'){//活期
                    //echo " ？currentDeposit？ 赎回  - $orderAmount $trace \n";
                    $this->timDeposit -=$orderAmount ;
                }
                break;
            case '清盘普赎':
                //echo "calc 普赎  + $orderAmount $trace \n";
                $this->arrCalc['returnCurrent'] += $orderAmount;
                if($prdt[0]=='#'){//活期
                    //echo " ？currentDeposit？ 活期 清盘普赎  - $orderAmount $trace \n";
                    $this->currentDeposit -=$orderAmount ;
                }
                break;
            case '付息':
                if($prdt[0]=='#'){//活期的付息
                    //echo " ？currentDeposit？ 日息 + $incomeAmount $trace \n";
                    $this->currentDeposit += $incomeAmount;
                }
                break;
            case '普赎':
                //echo "calc 普赎  + $orderAmount $trace \n";
                $this->arrCalc['returnCurrent'] += $orderAmount;
                if($prdt[0]=='#'){//活期
                    //echo " ？currentDeposit？ 赎回  - $orderAmount $trace \n";
                    $this->currentDeposit -=$orderAmount ;
                }
                break;
            case '还本/付息'://这里只处理定期
                if($prdt[0]=='#'){//活期持有中或已结算（结算也先扣，在派息中返还）
                }else{//定期持有中
                    //echo "calc 定还   + $orderAmount $trace \n";
                    $this->arrCalc['returnTime'] += $orderAmount;
                }
                break;
            case '投资':
                if($prdt[0]=='#'){//所有活期申购
                    //echo "calc 活申    - $orderAmount + $voucherAmount  $trace\n";
                    $this->arrCalc['buyCurrent']=$this->arrCalc['buyCurrent'] - $orderAmount + $voucherAmount;
                    //if(substr($prdt,-1)!='!'){//如果是未清盘的产品
                    //    echo " ？currentDeposit？ 申购 + $orderAmount $trace \n";
                        $this->currentDeposit +=$orderAmount;
                    //}
                }else{//所有定期申购
                    //echo "calc 定申    - $orderAmount + $voucherAmount  $trace\n";
                    $this->arrCalc['buyTime']=$this->arrCalc['buyTime'] - $orderAmount + $voucherAmount;
                    if(substr($prdt,-1)!='!'){//如果是未还清的产品
                        //echo "OERROR: ？timeDeposit？ + $orderAmount $trace \n";
                        $this->timDeposit +=$orderAmount;
                    }
                }
                break;

            case '现金红包':
            case '充值':
                //echo "calc 充值   + $orderAmount = ".($this->arrCalc['recharges']+$orderAmount)." $trace\n";
                $this->arrCalc['recharges'] += $orderAmount ;
                break;
            case '提现':
                //echo "calc 提现   - $orderAmount  $trace(todo:审核中)\n";
                if($this->orders[$orderId]['status']=='待支付'){
                    $this->arrCalc['withdrawFrose'] -= $orderAmount ;
                }else{
                    $this->arrCalc['withdraw'] -= $orderAmount ;
                }
                break;
            }        
    }
    
    protected function isMimosaStatusSuccess($status)
    {
        switch ($status){
            case "支付成功":
            case "份额已确认":
            case "已受理":
            case "交易成功":
//            case "已申请":
                return true;
            default:
                return false;
        }
    }
    public function dump()
    {
        $ret= array(
            'basic'=>array(
                'phone'=>$this->phone,'realname'=>$this->realname,'contractId'=>$this->contractId,     
                'memberId'=>$this->memberId,'ucUid'=>$this->ucUID,'mimosaUid'=>$this->mimosaUID,
                'froze'=>$this->froze,
                'depositHolding'=>array('current'=>sprintf('%.2f',$this->currentDeposit/100),'time'=>sprintf('%.2f',$this->timDeposit/100)),
                'balance'=>is_array($this->balance)?$this->balance:sprintf('%.2f',$this->balance/100),
                
                'calcTotal'=>$this->arrCalc,
                'locked'=>$this->lock,
            ),

            'bankcards'=>$this->bindCards,
            'orders'=>$this->orders,
        );
        if($this->errFound){
            $ret['basic']['UERROR']='('.$this->phone.')'.$this->errFound;
        }
        return $ret;
    }
    protected $bindCards=array();
    protected $balance=array();
    protected $froze = array();
    protected $contractId;
    protected $phone;
    protected $realname;
    protected $memberId;
    protected $ucUID;
    protected $mimosaUID;
    protected $orders;
    public $errFound;
    protected $currentProducts;
    protected $currentDeposit;//活期存款
    protected $timDeposit;//定期存款
    protected $ordersTime;//订单时间，可用于排序
    

    
    protected function holdStatusByMimosa($step){

        $map = array(
            'toConfirm'=>'待确认',
            'holding'=>'持有中',
            'expired'=>'已到期',
            'partHolding'=>'部分持有',
            'closed'=>'已结算',
            'abandoned'=>'已作废',
        );
        if(isset($map[$step])){
            return $map[$step];
        }else{
            return '？'.$step;
        }
    }
    protected function orderTypeByMimosa($typeid)
    {
        $map = array(
            'invest'=>'投资',
            'fastRedeem'=>'快赎',
            'normalRedeem'=>'普赎',
            'clearRedeem'=>'清盘普赎', 
            'refund'=>'退款',
            'cash'=>'还本/付息',
            'repayLoan'=>'还本',
            'repayInterest'=>'付息',
            'buy'=>'买卖',
            'writeOff'=>'冲销',
            'deposit'=>'充值',
            'withdraw'=>'提现',
            'redEnvelope'=>'现金红包',
            'cashFailed'=>'流标退款',
        );
        if(isset($map[$typeid])){
            return $map[$typeid];
        }else{
            return '？'.$typeid;
        }
    }
    protected function orderTypeBySettlement($typeid)
    {
        $map = array(
            '01'=>'申购',
            '02'=>'赎回',
            '03'=>'派息',
            '04'=>'赠送体验金',
            '05'=>'体验金到期',
            '06'=>'增加发行额',
            '50'=>'充值',
            '51'=>'提现',
            '56'=>'现金红包',
        );
        if(isset($map[$typeid])){
            return $map[$typeid];
        }else{
            return '！'.$typeid;
        }
    }
    protected function orderStatusByMimosa($status)
    {
        $map = array(
            'submitted'=>'已申请',
            'refused'=>'已拒绝',
            'toPay'=>'待支付',
            'payFailed'=>'支付失败', 
            'paySuccess'=>'支付成功',
            'payExpired'=>'支付超时',
            'accepted'=>'已受理',
            'confirmed'=>'份额已确认',
            'done'=>'交易成功',
            'refunded'=>'已退款',
            'abandoned'=>'已作废',
            'submitFailed'=>'申请失败',
        );
        if(isset($map[$status])){
            return $map[$status];
        }else{
            return '？'.$status;
        }
    }
    protected function orderStatusBySettlement($status)
    {
        if($status==1){
            return '成功';
        }elseif($status==0){
            return '受理';
        }elseif($status==2){
            return '失败';
        }else{
            return '！'.$status;
        }
    }



}