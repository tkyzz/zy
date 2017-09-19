<?php
namespace Prj\GH;

/**
 * Description of Register
 *
 * @author simon.wang
 */
class Register {
    //注册成功后生成的uc oid
    public $ucOid;
    public $userAcc;
    public $myInviteCode;
    /**
     * 
     * @var \Sooh2\DB\Interfaces\DB 
     */
    protected $db;
    /**
     * 
     * @var \Sooh2\DB\Interfaces\DB 
     */
    protected $redis;
    /**
     * 
     * @param type $phone 注册手机号
     * @param type $passwd 密码
     * @param type $contractId contractId
     * @param type $inviteBy  邀请人: 手机号或邀请码或ucOid
     * @return int 0 注册失败， 1 uc注册成功   99全部成功
     */
    public function doit($phone,$passwd,$contractId,$inviteBy,$uOid=null)
    {
        $step = 0;
        $conf = \Sooh2\Misc\Ini::getInstance()->getIni('DB');
        if(isset($conf['gh'])){
            $this->db = \Sooh2\DB::getConnection($conf['gh']);
        }else{
            $this->db = \Sooh2\DB::getConnection($conf['mysql']);
        }
        $this->redis = \Sooh2\DB::getConnection($conf['redis']);
        $this->invitorPhone = $this->phoneOfInvotor($inviteBy);
        if($this->inUc($phone, $passwd, $contractId,$uOid)){
            try{
                $step++;
                $this->inMimosa();
                $step++;
                $this->inSettlement();
                $step++;
                $this->inTulip();
                $step++;
                $this->finalAutoLogin();
                $step = 99;
            }catch(\ErrorException $e){
                error_log('register_proxy_error:'.$e->getMessage()."\n".$e->getTraceAsString());
            }
            return $step;
        }else{
            return 0;
        }
    }
    /**
     * 更新uc库
     * @return bool 是否成功的在uc库中建立user
     */
    protected function inUc($phone,$passwd,$contractId,$uOid=null)
    {
        //INSERT INTO `t_wfd_recommender` VALUES ('oid', '被邀请15d2f9c2ae90006', '邀请d2f756a970005', '2017-07-11 11:05:50', '2017-07-11 11:05:50');
        //INSERT INTO `t_wfd_user` VALUES ('ff8080815d1a7e0f015d2f9c2ae90006', '13032105140', '131UID2017071100000002', null, null, null, null, 'normal', 'frontEnd', '6', '999920170228100001', '2017-07-11 11:05:50', '2017-07-11 11:05:50');
        if(!empty($passwd)){
            //$this->getField('userPwd')==bin2hex(sha1(hex2bin($this->getField('salt')).$pwdInput, true));   
            $salt = rand(1000,9999);
            $passwd = bin2hex(sha1($salt.$passwd, true));
            $salt = bin2hex($salt);
        }else{
            $salt=null;
            $passwd=null;
        }
        //新注册的用户的邀请id=   c:g:u:sceneid  int  6  这个在 inUc里处理了
        $this->myInviteCode = $this->redis->exec(array( array('incr','c:g:u:sceneid:',) ));
        
        if($uOid==null){
            $retry = 5;
        }else{
            if(strlen($uOid)>28){
                throw new \ErrorException('strlen(uOid) must = 28');
            }
            $retry = 1;
        }
        while($retry){
            $retry--;
            if($uOid){
                $this->ucOid = $uOid;
                $uOid=null;
            }else{
                $this->ucOid = substr(md5(microtime()),0,28);
            }
            
            $this->userAcc=$phone;
            try{
                $dt = time();
                $fields=array(
                    'channelid'=>$contractId,
                    'createTime'=>$dt.'000',
                    'memberOid'=>$this->ucOid,
                    'oid'=>$this->ucOid,
                    'payPwd'=>null,
                    'paySalt'=>null,
                    'sceneId'=>$this->myInviteCode,
                    'source'=>'frontEnd',
                    'status'=>'normal',
                    'updateTime'=>$dt.'000',
                    "userAcc"=>$phone,
                );
                $ret = $this->db->addRecord('gh_jz_uc.t_wfd_user', 
                                        array_merge($fields,array(
                                            'userPwd'=>$passwd,
                                            'salt'=>$salt,
                                            'createTime'=>date('Y-m-d H:i:s',$dt),
                                            'updateTime'=>date('Y-m-d H:i:s',$dt),
                                        )));


                if($ret){
                    //更新redis
                    $this->redis->exec(array(array('set','c:g:u:ua:'.$this->userAcc,$this->ucOid)));
                    if(empty($this->invitor)){
                        $this->redis->exec(array(array('set','c:g:u:ui:'.$this->ucOid,
                                '{"user":'.json_encode($fields).',"userBank": [],"userLoginInfo": {"pwdErrorTimes": 0},"wxopenids": {}}'
                            )));

                    }else{
                        $this->redis->exec(array(array('set','c:g:u:ui:'.$this->ucOid,
                                '{"recommender": "'.$this->invitor['phone'].'","user":'.json_encode($fields).',"userBank": [],"userLoginInfo": {"pwdErrorTimes": 0},"wxopenids": {}}'
                            )));
                    }
                    //记录邀请关系
                    if(!empty($this->invitor)){
                        $this->db->addRecord('gh_jz_uc.t_wfd_recommender', array(
                            'oid'=>$this->ucOid,
                            'userOid'=>$this->ucOid,
                            'recommendLoginName'=>$this->db->getOne('gh_jz_uc.t_wfd_user', 'oid',array('userAcc'=>$this->invitor['phone'])),
                            'updateTime'=>date('Y-m-d H:i:s'),
                            'createTime'=>date('Y-m-d H:i:s'),
                        ));
                    }
                    return true;
                }
            } catch (\ErrorException $e){
                error_log('add user to gh_jz_uc.t_wfd_user failed '.$e->getMessage()."\n".$e->getTraceAsString());
            }
        }
        return false;
    }

    protected $invitor=null;//array('phone'=>1234123124,'oid'=>'ff125725378rguf')
    /**
     * 获取邀请人的oid和手机号
     */
    protected function phoneOfInvotor($inviteBy)
    {
        if(empty($inviteBy)){
            return null;
        }
        if(is_numeric($inviteBy)){
            if($inviteBy>=10000000000 && $inviteBy<=90000000000){
                return $this->db->getRecord('gh_jz_uc.t_wfd_user', 'oid,userAcc as phone',array('userAcc'=>$inviteBy))-0;
            }else{
                return $this->db->getRecord('gh_jz_uc.t_wfd_user', 'oid,userAcc as phone',array('sceneId'=>$inviteBy))-0;
            }
        }else{//oid
            return $this->db->getRecord('gh_jz_uc.t_wfd_user', 'oid,userAcc as phone',array('oid'=>$inviteBy))-0;
        }
    }
    

    
    protected function inTulip()
    {
        //注册发奖不在走这里，这个表应该不用补充了
//INSERT INTO `t_gateway_request_log` VALUES ('ff8080815d1a7e75015d2f9c2b68005a', 'mimosa', 'onRegister', '{\"createTime\":1499742350175,\"friendId\":\"ff8080815d1a7e0f015d2f756a970005\",\"phone\":\"13032105140\",\"userId\":\"ff8080815d1a7e0f015d2f9c2ae90006\"}', '<200 OK,BaseResp(errorCode=0, errorMessage=null),{}>', 'success', '0', '127.0.0.1', '2017-07-11 11:05:50', '注册事件');
//INSERT INTO `t_gateway_request_log` VALUES ('ff8080815d1a7e75015d2f9c2c5e005b', 'mimosa', 'getEventInfo', '{\"errorCode\":0,\"eventType\":\"register\",\"couponType\":\"coupon\"}', '<200 OK,BaseResp(errorCode=-1, errorMessage=活动异常或奖励方式异常！),{}>', 'success', '0', '127.0.0.1', '2017-07-11 11:05:50', '获取活动奖励金额');
//INSERT INTO `t_gateway_request_log` VALUES ('ff8080815d1a7e75015d2f9c2c8d005c', 'mimosa', 'getEventInfo', '{\"errorCode\":0,\"eventType\":\"investment\",\"couponType\":\"coupon\"}', '<200 OK,BaseResp(errorCode=-1, errorMessage=活动异常或奖励方式异常！),{}>', 'success', '0', '127.0.0.1', '2017-07-11 11:05:50', '获取活动奖励金额');

        
        $this->db->addRecord('gh_jz_tulip.t_user_invest_log', array(
            'oid'=>md5(microtime()),
            'userId'=>$this->ucOid,
            'registerTime'=>date('Y-m-d H:i:s'),
            'friendId'=>empty($this->invitor)?null:$this->invitor['oid'],
            'phone'=>$this->userAcc,

        ));
        //邀请人的邀请计数加一
        if(!empty($this->invitor)){
            $this->db->updRecords('gh_jz_tulip.t_user_invest_log', array('friends=friends+1'),array('userId'=>$this->invitor['oid']));
        }
////INSERT INTO `t_user_invest_log` VALUES ('ff8080815d1a7e75015d2f756ada0057', 'ff8080815d1a7e0f015d2f756a970005', '0.00', '1', '0', '0', null, '2017-07-11 10:23:31', '0.00', null, '17717555734', null, null);
//INSERT INTO `t_user_invest_log` VALUES ('ff8080815d1a7e75015d2f9c2b650059', 'ff8080815d1a7e0f015d2f9c2ae90006', '0.00', '0', '0', '0', null, '2017-07-11 11:05:50', '0.00', 'ff8080815d1a7e0f015d2f756a970005', '13032105140', null, null);
    }
    
    protected function inSettlement()
    {
//        INSERT INTO `t_account_info` VALUES ('ff8080815d1a7f6f015d2f9c2b33006f', '131AN2017071100000002', '131UID2017071100000002', 'T1', '10', null, '投资人账户基本户', '2017-07-11 11:05:50', null, '0.0000', 'SUBMIT', 'NORMAL', null, '2017-07-11 11:05:50', '2017-07-11 11:05:50', null, null);
//      INSERT INTO `t_account_userinfo` VALUES ('ff8080815d1a7f6f015d2f9c2b32006e', 'ff8080815d1a7e0f015d2f9c2ae90006', '131UID2017071100000002', 'mimosa', 'T1', null, null, null, null, null, '13032105140', null, '2017-07-11 11:05:50', '2017-07-11 11:05:50');

        $this->db->addRecord('gh_jz_settlement.t_account_info', array(
            'oid'=>$this->ucOid,
            'accountNo'=>$this->ucOid,
            'userOid'=>$this->ucOid,
            'userType'=>'T1',
            'accountType'=>'10',
            'accountName'=>'投资人账户基本户',
            'openTime'=>date('Y-m-d H:i:s'),
            'balance'=>'0.0000',
            //'SUBMIT', 'NORMAL', null, '2017-07-11 11:05:50', '2017-07-11 11:05:50', null, null
            'status'=>'SUBMIT',
            'frozenStatus'=>'NORMAL',
            'updateTime'=>date('Y-m-d H:i:s'),
            'createTime'=>date('Y-m-d H:i:s'),
        ));
        $this->db->addRecord('gh_jz_settlement.t_account_userinfo', array(
            'oid'=>$this->ucOid,
            'systemUid'=>$this->ucOid,
            'userOid'=>$this->ucOid,
            'systemSource'=>'mimosa',
            'userType'=>'T1',
            'phone'=>$this->userAcc,
            'updateTime'=>date('Y-m-d H:i:s'),
            'createTime'=>date('Y-m-d H:i:s'),
        ));


    }
    
    protected function inMimosa()
    {
        //redis 执行记录
        $this->db->addRecord('gh_jz_mimosa.t_money_cache_execute_log', array(
                        'oid'=>$this->ucOid,
                        'batchNo'=>'createInvestor:'.$this->ucOid,
                        'executeCommand'=>'HMSET',
                        'hkey'=>'m:investor:'.$this->ucOid,
                                  //'DEL', 'SUCCESS', 'FAILED', '0', '2017-07-11 11:05:50', '2017-07-11 11:05:50'
                        'errorCommand'=>'DEL',
                        'executeSuccessStatus'=>'SUCCESS',
                        'errorCount'=>'0',
                        'executeTime'=>date('Y-m-d H:i:s'),
                        'createTime'=>date('Y-m-d H:i:s')
                    ));
//        $this->db->addRecord('gh_jz_mimosa.t_money_coupon_log', array(
////         TODO   注册发券，先忽略
//                    ));
//INSERT INTO `t_money_cache_execute_log` VALUES ('ff8080815d1a803d015d2f9c2b8a41cf', 'createInvestor:ff8080815d1a7e0f015d2f9c2ae90006', 'HMSET', 'm:investor:ff8080815d1a7e0f015d2f9c2ae90006', null, null, null, 'DEL', 'SUCCESS', 'FAILED', '0', '2017-07-11 11:05:50', '2017-07-11 11:05:50');
//INSERT INTO `t_money_coupon_log` VALUES ('ff8080815d1a803d015d2f9c2b9941d0', 'FAILED', 'REGISTER', '0', '3', '2017-07-11 11:05:50', 'ff8080815d1a7e0f015d2f9c2ae90006', '2017-07-11 11:05:50', '2017-07-11 11:05:50');

        //mimosa 账户
//        INSERT INTO `t_money_investor_baseaccount` VALUES ('ff8080815d1a803d015d2f9c2b4741ca', 'ff8080815d1a7e0f015d2f9c2ae90006', '131UID2017071100000002', '13032105140', null, null, '6', 'normal', '0.00', 'investor', 'yes', '0.00', '0.00', '2017-07-11 11:05:50', '2017-07-11 11:05:50');
        $this->db->addRecord('gh_jz_mimosa.t_money_investor_baseaccount', array(
            'oid'=>$this->ucOid,'userOid'=>$this->ucOid,'memberId'=>$this->ucOid,'phoneNum'=>$this->userAcc,
            'realName'=>null,'idNum'=>null,'uid'=>$this->myInviteCode,'status'=>'normal','balance'=>'0.00','owner'=>'investor','isFreshman'=>'yes',
            'investWayBalance'=>'0.00','onWayBalance'=>'0.00','updateTime'=>date('Y-m-d H:i:s'),'createTime'=>date('Y-m-d H:i:s'),
                ));
        
//INSERT INTO `t_money_investor_baseaccount_refer_details` VALUES ('ff8080815d1a803d015d2f9c2b5b41cd', 'ff8080815d1a803d015d2f756ac2414d', 'ff8080815d1a803d015d2f9c2b4741ca', '2017-07-11 11:05:50', '2017-07-11 11:05:50');
//INSERT INTO `t_money_investor_baseaccount_referee` VALUES ('ff8080815d1a803d015d2f756ac2414d', 'ff8080815d1a803d015d2f756ac2414b', '1', '0', '0', '0.0000', '2017-07-11 11:05:50', '2017-07-11 10:23:30');
//INSERT INTO `t_money_investor_baseaccount_referee` VALUES ('ff8080815d1a803d015d2f9c2b4741cc', 'ff8080815d1a803d015d2f9c2b4741ca', '0', '0', '0', '0.0000', '2017-07-11 11:05:50', '2017-07-11 11:05:50');

        // 邀请管理
        $this->db->addRecord('gh_jz_mimosa.t_money_investor_baseaccount_referee', array(
            'oid'=>$this->ucOid,'investorOid'=>$this->ucOid,'referRegAmount'=>0,'yesterdayRecommenders'=>0,'referPurchasePeopleAmount'=>0,'referPurchaseMoneyVolume'=>'0.0000','updateTime'=>date('Y-m-d H:i:s'),'createTime'=>date('Y-m-d H:i:s')
        ));
        if(!empty($this->invitor)){
            $refOid = $this->db->getOne('gh_jz_mimosa.t_money_investor_baseaccount_referee', 'oid',array('investorOid'=>$this->invitor['oid']));
            $this->db->updRecords('gh_jz_mimosa.t_money_investor_baseaccount_referee', 
                array('referRegAmount=referRegAmount+1'),
                array('oid'=>$refOid));
            $this->db->addRecord('gh_jz_mimosa.t_money_investor_baseaccount_refer_details', array(
                'oid'=>md5(microtime()),'refereeOid'=>$refOid,'investorOid'=>$this->ucOid,'updateTime'=>date('Y-m-d H:i:s'),'createTime'=>date('Y-m-d H:i:s')
            ));
        }

        //统计数据
//INSERT INTO `t_money_investor_statistics` VALUES ('ff8080815d1a803d015d2f9c2b4741cb', 'ff8080815d1a803d015d2f9c2b4741ca', 
//'0.0000', '0.0000', '0.0000', '0.0000', '0.0000',
// '0.0000', '0.0000', '0.0000', '0.0000', '0.0000', 
// '0.0000', '0.0000', null, '0', '0', 
// '0', '0', '0', '0', '0', 
// '0', '0.0000', '0.0000', '0.0000', '0.0000',
//  '0', null, null, '2017-07-11 11:05:50', '2017-07-11 11:05:50');
        $this->db->addRecord('gh_jz_mimosa.t_money_investor_statistics', array(
  'oid'=>$this->ucOid,'investorOid'=>$this->ucOid,
  'totalDepositAmount'=>'0.0000','totalWithdrawAmount'=>'0.0000','totalInvestAmount'=>'0.0000','totalRedeemAmount'=>'0.0000','totalIncomeAmount'=>'0.0000',
  'totalRepayLoan'=>'0.0000','t0YesterdayIncome'=>'0.0000','tnTotalIncome'=>'0.0000','t0TotalIncome'=>'0.0000','t0CapitalAmount'=>'0.0000',
  'tnCapitalAmount'=>'0.0000','experienceCouponAmount'=>'0.0000','totalInvestProducts'=>0,'totalDepositCount'=>'0','totalWithdrawCount'=>'0',
  'totalInvestCount'=>0,'totalRedeemCount'=>0,'todayDepositCount'=>0,'todayWithdrawCount'=>0,'todayInvestCount'=>0,
  'todayRedeemCount'=>0,'todayDepositAmount'=>'0.0000','todayWithdrawAmount'=>'0.0000','todayInvestAmount'=>'0.0000','todayRedeemAmount'=>'0.0000',
  'monthWithdrawCount'=>0,'updateTime'=>date('Y-m-d H:i:s'),'createTime'=>date('Y-m-d H:i:s')
                    ));
//INSERT INTO `t_money_platform_statistics` VALUES ('platformsta', 'platformoid', '23250.0000', '5000.0000', '0.0000', '0.0000', '18250.0000', '0.0000', '0.0000', '0.0000', '--------------7', '0', '0', '0', '0', '0', '0', '0', '1', '2', '0', null, '2017-07-11 11:05:50', '2016-08-19 09:41:25');
        $this->db->updRecords('gh_jz_mimosa.t_money_platform_statistics', array('registerAmount=registerAmount+1'));

//INSERT INTO `t_money_tulip_log` VALUES ('ff8080815d1a803d015d2f9c2b8741ce', 'onRegister', '注册事件', '0', null, '1', '10', '2017-07-11 11:06:50', '{\"createTime\":1499742350175,\"friendId\":\"ff8080815d1a7e0f015d2f756a970005\",\"phone\":\"13032105140\",\"userId\":\"ff8080815d1a7e0f015d2f9c2ae90006\"}', '2017-07-11 11:05:50', '2017-07-11 11:05:50');
        $this->db->addRecord('gh_jz_mimosa.t_money_tulip_log', array(
            'oid'=>$this->ucOid,'interfaceCode'=>'onRegister','interfaceName'=>'注册事件','errorCode'=>0,'sendedTimes'=>1,'limitSendTimes'=>10,'nextNotifyTime'=>date('Y-m-d H:i:s',time()-5),
            'sendObj'=>'{\"createTime\":'.time().'000,\"friendId\":\"'.(empty($this->invitor)?'':$this->invitor['oid']).'\",\"phone\":\"'.$this->userAcc.'\",\"userId\":\"'.$this->ucOid.'\"}','createTime'=>date('Y-m-d H:i:s')
            ));
        //TODO 下面的两个应该没有用，先不处理
//INSERT INTO `t_ope_nocard` VALUES ('ff8080815d1a803d015d2f76c8774155', 'ff8080815d1a7e0f015d2f756a970005', null, '17717555734', '999920170228100000', '2017-07-11 10:23:30', null, 'no', 'no', null, null, '2017-07-11 10:25:00', '2017-07-11 10:25:00');
//INSERT INTO `t_ope_selecttime` VALUES ('ff8080815d1a803d015d1ab8b97700a5', 'nocardtime', '1499739810000', '2017-07-07 09:45:00', '2017-07-11 10:25:00');
        
        $fields = array(
            'onWayBalance'=>0,'userOid'=>$this->ucOid,'monthWithdrawCount'=>0,
            'isFreshman'=>'yes','balance'=>0,'investWayBalance'=>0
        );
        $this->redis->addRecord('m:investor:'.$this->ucOid, $fields);
    }
    
    protected function finalAutoLogin()
    {
        $curl = \Sooh2\Curl::factory(array());
        $vcode = \Prj\Redis\Vcode::createVCode(\Sooh2\Util::remoteIP(), $this->userAcc, 'login');
        $ini = \Sooh2\Misc\Ini::getInstance();
        $url = 'http://'.$ini->getIni('application.serverip.ghuc').'/wfduc/client/user/login/';

        $args = '{"userAcc": "'.$this->userAcc.'","userPwd":"","vericode": "'.$vcode.'","platform": "app" }';
        $ret = $curl->httpPost($url, $args);
        $chk = json_decode($ret,true);
        if(!is_array($chk)){
            throw new \ErrorException('login from uc failed 1:'.$ret.' got');
        }
        if(!empty($chk['errorMessage']) && strpos($chk['errorMessage'],'登录')){
            throw new \ErrorException('login from uc failed 2:'.$ret.' got');
        }
        $newSessid = $curl->cookies['SESSION'];
        setcookie('SESSION', $newSessid,time()+86400*3, '/');
        setcookie('GH-SESSION', $newSessid,time()+86400*3, '/');
    }
}
