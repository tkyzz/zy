<?php

/**
 * 测试的hello
 *
 * @author simon.wang
 */
class TghController extends \Prj\Framework\Ctrl {

    public function __construct(){
        \Prj\Tool\Debug::setTestEnv();
        \Prj\Loger::$prefix .= '[TEST]';
        if(in_array($_SERVER['SERVER_ADDR'] , ['106.14.236.8','106.14.25.126','106.14.236.168'])){

        }else if(isset($_SERVER['CVS_RSH']) && $_SERVER['CVS_RSH'] == 'ssh'){

        }else{
            die('error !');
        }
    }

    public function daliAction(){
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('www');
    }


    /**
     * 获取签到历史数据
     */
    public function historyAction()
    {
        $history = array(
            'signhistory'=>array(20170601,2070602),
            'params'=> $this->_request->get('productId'),
            '_cookie'=>$_COOKIE,
        );
        $this->_view->assign('DaySignHistory',$history);
        $this->assignCodeAndMessage('success');
        $this->assignPageInfo();
    }
    /**
     * 签到
     */
    public function dosignAction()
    {
        $this->_view->assign('DaySignResult',1);
        //do call histroy
        $this->historyAction();
    }

    protected static $msSleep = 1000000;

    public static function loopHourly($_preNameSpace='\\Prj\\Events\\',$evtDataClass='\\Sooh2\\EvtQue\\QueData')
    {
        $evt = $evtDataClass::getOne();
        if($evt){
            $evtData = $evt->getEvtData();
            $className = $_preNameSpace.($evtData->evtId);
            if(!class_exists($className)){
                error_log('[ERROR]' . $className.' does not exists !');
            }else{
                $ret = $className::getInstance()->init($evtData)->onEvt();
                $evt->endJob($ret);
            }
        }else{
            error_log('[Prj\Events]没有需要处理的队列!');
            die('over');
        }
    }

    public function runAllEventAction(){
        while (true){
            self::loopHourly();
        }
    }

    /**
     * 测试事件发券的功能
     */
    public function checkEventAction(){
        $db = \Prj\Model\ActivityCoupon::getCopy('')->dbWithTablename();
        //满标事件
        $insertSql = <<<sql
            INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('PrdtFull', '15fec57f671e48c9a222e8cb4d942a5e', '', '', '');
sql;
        $ret = $db->exec([$insertSql]);
        self::loopHourly();

        //注册事件
        $insertSql = <<<sql
            INSERT INTO `jz_db`.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`) 
            VALUES ('RegisterOk', '', '8a9bfa0e5caa4dca015caf54d1a60041', '');
sql;
        $ret = $db->exec([$insertSql]);
        self::loopHourly();

        //绑卡事件
        $insertSql = <<<sql
            INSERT INTO `jz_db`.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`) 
            VALUES ('BindOk', '中国测试银行', 'ff8080815ca5f6d1015caf9062ec0005', '');
sql;
        $ret = $db->exec([$insertSql]);
        self::loopHourly();

        //充值事件
        $insertSql = <<<sql
            INSERT INTO `jz_db`.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`) 
            VALUES ('ChargeOk', '', 'ff8080815ca5f6d1015caf9062ec0005', 'ff8080815ca54705015cafbb306620ca');
sql;
        $ret = $db->exec([$insertSql]);
        self::loopHourly();
        var_dump(\Prj\Bll\EventCoupon::getOids());
        //$this->clearEventCoupon();
    }

    protected function clearEventCoupon(){
        $arr = \Prj\Bll\EventCoupon::getOids();
        if($arr){
            foreach ($arr as $v){
                $db = \Prj\Model\ActivityCoupon::getCopy('')->dbWithTablename();
                var_dump($db->delRecords($db->kvobjTable() , ['oid' => $v]));
            }
        }
    }

    protected $userId =  'ff8080815ca53f41015ca54d017f0000';

    public function test_170629Action(){
        var_dump(\Prj\Bll\MiActivy::getInstance()->getChannelInfos());
        $userId = 'ff8080815ca53f41015ca54d017f0000';
        $channel = '115220170517100031';
        \Prj\Events\RegisterOk::getInstance()->send_register_sms_for_activity($userId , $channel);
    }

    /**
     * 宝宝树上报测试
     */
    public function test_170629_bbtAction(){
        $orderInfo = \Prj\Model\MimosaTradeOrder::getRecords(null , [] , 'rsort createTime' , 1 , 1)[0];
        \Prj\Bll\BaobaoTree::getInstance()->sendOrder($orderInfo['oid']);
    }

    public function testAction(){
        var_dump(\Prj\Bll\UserCoupon::getInstance()->getMyListByProId([
            'productId' => '2e57b2f3e14f43a19f1fea48acd4bfb5',
            'userId' => $this->userId,
        ]) );
    }

    /**
     * Hand 开发-手动发券接口
     */
    public function sendCouponAction(){
        $phone = $this->_request->get('phone');
        $type = $this->_request->get('type' , '1');
        if(empty($phone))return $this->assignCodeAndMessage('no phone' , 99999);
        $user = \Prj\Model\User::getCopyByPhone($phone);
        $user->load();
        $userId = $user->getField('oid');
        if($type == '1'){
            $coupon = \Prj\Model\Coupon::getOne(['typeCode' => 'COUPON' , 'status' => 'yes' , 'isFloat' => 1]);
            $couponId = $coupon['oid']; //代金券
            $amount = rand(1,50);
        }else if($type == '2'){
            $coupon = \Prj\Model\Coupon::getOne(['typeCode' => 'REDPACKETS' , 'status' => 'yes' , 'isFloat' => 0]);
            $couponId = $coupon['oid']; //代金券
            $amount = 0;
        }else if($type == '3'){
            $coupon = \Prj\Model\Coupon::getOne(['typeCode' => 'RATECOUPON' , 'status' => 'yes' , 'isFloat' => 0]);
            $couponId = $coupon['oid']; //代金券
            $amount = 0;
        }
        //$amount = 0;
        $sender = \Lib\Services\SendCouponLocal::getInstance();
        $sender->setCouponId($couponId);
        $sender->setAmount($amount);
        $ret = $sender->sendCoupon($userId);
        $this->assignRes($ret);
    }

    public function shareAction(){
        $appID = 'wxbed418d617b9e7c2';
        $appsecret = 'df235cae97eddfa1d4320313a58459f9';
        $data = (new \Lib\Wx\Jssdk($appID , $appsecret))->getSignPackage();
        $this->_view->assign('data' , $data);
        $this->assignCodeAndMessage();
    }

    public function downAction(){
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        $data = [
            [
                1,2,3
            ]
        ];
        echo \Lib\Misc\ArrayH::exportCsv('xxx' , ['xxx'] , $data);
    }
}
