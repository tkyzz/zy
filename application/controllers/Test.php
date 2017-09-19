<?php
class TestController extends \Yaf_Controller_Abstract
{
    protected $userId = '41aab1ac6f4c179db3ab957e92dc'; //13262798028

    public function __construct(){
        \Prj\Loger::setKv('TEST');
        \Prj\Loger::out('test...');
        ini_set('error_log' , '/var/log/tgh_php_errors.log');
    }

    public function hiAction()
    {
        $this->_view->assign('kk',$this->_request->get('k','unfound'));
    }

    public function tmpTestAction(){
//        $this->test_tgh_sendCoupon();
//        $this->test_tgh_event();
//        $this->test_tgh_td();
//        \Prj\Bll\ZY\Removal::getInstance()->run();
        $userId = '0090afee348a3b4e080921eedd0e';
        $final = \Prj\Model\UserFinal::getCopy($userId);
        $final->load();
        var_dump(\Prj\Bll\UserFinal::getInstance()->getIsBindCard($final));
    }

    public function tgh_removalAction(){
        \Prj\Bll\ZY\Removal::getInstance()->run();
    }

    /**
     * Hand 同步券的标签到zy表
     */
    public function addCouponLabelAction(){
        $couponList = \Prj\Model\Coupon::getRecords();
        foreach ($couponList as $v){
            if($v['labels']){
                $labelsArr = explode(',' , $v['labels']);
                foreach ($labelsArr as $labelId){
                    echo $labelId . "\n";
                    $label = \Prj\Model\MimosaLabel::getCopy($labelId);
                    $label->load();
                    $labelCode = $label->getField('labelCode');
                    $labelCode = str_replace(0 , '' , $labelCode);
                    echo $labelCode . "\n";

                    $newLabel = \Prj\Model\ZyBusiness\SystemLabel::getRecord(null , ['labelNo' => $labelCode]);
                    if($newLabel){

                        $record  = \Prj\Model\ZyBusiness\CouponLabel::getRecord(null , [
                            'couponId' => $v['oid'],
                            'labelId' => $newLabel['labelId'],
                        ]);

                        if(empty($record)){
                            $insertData = [
                                'couponId' => $v['oid'],
                                'labelId' => $newLabel['labelId'],
                                'createTime' => date('Y-m-d H:i:s'),
                            ];
                            \Prj\Model\ZyBusiness\CouponLabel::saveOne($insertData);
                        }else{
                            echo '已存在' . "\n";
                        }

                    }else{
                        echo 'newLabel 不存在' . "\n";
                    }
                }
            }
        }
    }

    /**
     * Hand 发券测试
     * @return bool
     */
    public function test_tgh_sendCoupon(){
        $userId = $this->userId;
        $where = ['status' => 'yes'];
        $types = [
            'c' => \Prj\Model\Coupon::type_coupon,
            'r' => \Prj\Model\Coupon::type_redPackets,
            'j' => \Prj\Model\Coupon::type_jiaxi,
        ];
        $floats = [0 , 1];

        foreach ($floats as $vv){
            $sender = \Lib\Services\SendCouponLocal::getInstance();
            if($vv){
                $sender->setAmount(123)
                    ->setInvestAmount(12300)
                    ->setExpire(0);
            }
            $where['isFloat'] = $vv;
            foreach ($types as $v){
                $this->output('【发券测试】' , $where);
                $where['typeCode'] = $v;
                $coupon = \Prj\Model\Coupon::getOne($where);
                if(empty($coupon)){
                    $this->output('没有该类型的券' , $where);
                    continue;
                }
                $ret = $sender->setCouponId($coupon['oid'])->sendCoupon($userId);
                if(\Lib\Misc\Result::check($ret))$ret = '发券成功...';
                $this->output($ret);
                $this->output('------------------------------');
            }
        }
        return true;
    }

    /**
     * Hand 事件测试
     * @return bool
     */
    public function test_tgh_event(){
        //注册事件
        $userId = $this->userId;
        $prefix = 'JavaEvt\\';
        $data[] = [
            'evt' => 'RegisterOk',
            'objId' => $userId,
            'uid' => $userId,
            'args' => [],
        ];
        //绑卡事件
        $card = \Prj\Model\Payment\BankBind::getOne(['userId' => $userId , 'type' => 'BIND']);
        if(!empty($card)){
            $data[] = [
                'evt' => 'BindOk',
                'objId' => $card['orderNo'],
                'uid' => $userId,
                'args' => [
                    'idCardNo' => '340823199311281234',
                    'bankCardNo' => '1234567890',
                ],
            ];
        }
        //充值事件



        foreach ($data as $v){
            \Sooh2\EvtQue\QueData::addOne($prefix . $v['evt'] , $v['objId'] , $v['uid'] , json_encode($v['args'] , 256));
        }

        return true;
    }

    public function test_tgh_td(){
        \Prj\Bll\Td::getInstance()->test();
        return true;
    }



    protected function output($str1 , $str2 = '' , $str3 = ''){
        if(is_array($str1))$str1 = json_encode($str1 , 256);
        if(is_array($str2))$str2 = json_encode($str2 , 256);
        if(is_array($str3))$str3 = json_encode($str3 , 256);
        echo $str1 . $str2 . $str3 . "\n";
    }
}