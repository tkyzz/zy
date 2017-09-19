<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/23
 * Time: 11:24
 */
namespace Prj\Bll\ActRuleZy;

class ActBirth extends \Prj\Bll\_BllBase
{
    protected $conf = [
        'enable' => 1, //是否开启
        'mail_title' => 'xxxxxxxxxxxx', //站内信标题
        'mail_content' => 'xxxxxxxxxxxxxxxxxxxxxx', //站内信内容
        'conpon_sms' => 'xxxxxxxxxxxxxxxxx', //礼包短信
        'birth_sms' => 'xxxxxxxxxxxxxxxxxxxx', //生日短信
        'coupons' => [ //奖励列表
            '150148645587236806',
            '150148255078990180',
            '1500866005224yzPrP7oYej7zgnEUFAK'
        ],
    ];

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        \Prj\Loger::setKv('ActBirth' , null);
    }

    /**
     * 监听生日,发放生日礼包
     * @param array $params
     * @return array|void
     */
    public function listenBirth($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['userId']))return $this->resultError('参数错误');
        \Prj\Loger::setKv('a' , 'listenBirth');

        if($this->getConf('enable') != 1)return $this->resultError('活动未开启!!!');

        $userId = $params['userId'];
        //todo 取身份证号改成读 user_final_0
        $bank = \Prj\Model\UserFinal::getCopy($userId);
//        $bank = \Prj\Model\UserBank::getCopy(['userOid' => $userId]);
        $bank->load();
        \Prj\Loger::out(\Prj\Model\UserFinal::db()->lastCmd());
        if(!$bank->exists()) return $this->resultError("此账号不存在");
        if(empty($bank->getField("bindCardId"))) return $this->resultError('银行卡信息不存在!!!');
        try{
            $idNum = $bank->getField('certNo');
        }catch (\Exception $e){
            $idNum = '';
        }
        if(empty($idNum))return $this->resultError('身份证信息不存在!!!');
        //获取生日
        if(strlen($idNum)==18)
        {
            $tmonth=intval(substr($idNum,10,2));
        }
        elseif(strlen($idNum)==15)
        {
            $tmonth=intval(substr($idNum,8,2));
        }
        if($tmonth != date('m')){
            \Prj\Loger::out('本月是 ' . date('m').' 用户出生月份为: ' . $tmonth);
            return $this->resultError('用户非本月出生!!!');
        }
        return $this->sendCouponToUser([
            ['wfdUserId' => $userId]
        ]);
        return $this->resultOK();
    }

    /**
     * Hand 扫取本月过生日的用户,发送祝福短信
     * @return array
     */
    public function crondBirSMS(){
        \Prj\Loger::setKv('a' , 'crondBirSMS');
        $pageNo = 0;
        $max = 10;
        if(!$this->getConf('enable'))return $this->resultError('生日活动未开启!!!');
        while (true){
            if(($max -= 1) < 0)break;

            $list = $this->getBirthUserListByDate([
                'date' => date('Ymd'),
                'pageNo' => $pageNo,
            ]);
            if(empty($list))break;

            //开始发放祝福
            foreach ($list as $v){
                $userId = $v['wfdUserId'];
                $birthSms = $this->getConf('birth_sms');
                \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('birth_day_sms' , $birthSms , $userId , ['smsnotice']);
            }

            $pageNo++;
        }
        return $this->resultOK();
    }

    /**
     * Hand 扫取本月过生日的用户,发送生日礼包
     * @return array
     */
    public function crondBirth(){
        \Prj\Loger::setKv('a' , 'crondBirth');
        if($this->getConf('enable') != 1)return $this->resultError('活动未开启!!!');
        $pageNo = 0;
        $max = 10;
        while (true){
            if(($max -= 1) < 0)break;

            $list = $this->getBirthUserListByMonth([
                'date' => date('Ymd'),
                'pageNo' => $pageNo,
            ]);
            if(empty($list))break;

            //开始发放奖励
            $this->sendCouponToUser($list);

            $pageNo++;
        }
        return $this->resultOK();
    }

    /**
     * Hand 获取指定月份过生日的用户
     * @param array $params
     * @return array
     */
    protected function getBirthUserListByMonth($params = []){
        $date = $params['date'];
        $month = date('m' , strtotime($date));
        $pageSize = $params['pageSize'] ?: 2000;
        $pageNo = $params['pageNo'];
        $where = [
            "DATE_FORMAT(ymdBirthday,'%m')" => $month,
            "<ymdReg" => date('Ym' , strtotime($date)) . '01'
        ];
        //todo 改成读tb_user_final
        $records = \Prj\Model\UserFinal::getRecords(null , $where , 'sort ymdReg sort hisReg' , $pageSize , $pageNo * $pageSize);
        \Prj\Loger::outVal('sql' , \Prj\Model\UserFinal::db()->lastCmd());
        \Prj\Loger::outVal('count' , count($records));
        return $records;
    }

    /**
     * Hand 获取指定日期过生日的用户
     * @param array $params
     * @return array
     */
    protected function getBirthUserListByDate($params = []){
        $date = $params['date'];
        $day = date('md' , strtotime($date));
        $pageSize = $params['pageSize'] ?: 2000;
        $pageNo = $params['pageNo'];
        $where = [
            "DATE_FORMAT(ymdBirthday,'%m%d')" => $day,
        ];
        //todo 改成读tb_user_final
        $records = \Prj\Model\UserFinal::getRecords(null , $where , 'sort ymdReg sort hisReg' , $pageSize , $pageNo * $pageSize);
        \Prj\Loger::outVal('sql' , \Prj\Model\UserFinal::db()->lastCmd());
        \Prj\Loger::outVal('count' , count($records));
        return $records;
    }

    protected function sendCouponToUser($list){
        $coupons = $this->getConf('coupons');
        foreach ($list as $v){
            $userId = $v['wfdUserId'];
            foreach ($coupons as $couponId){
                $res = \Prj\Bll\EventCoupon::getInstance()->sendBirthCoupon($userId , date('Ymd') , $couponId);
                if(!$this->checkRes($res)){
                    \Prj\Loger::out('【发券失败】CouponId: ' . $couponId.' Reson:' . $res['message']);
                }
            }

            //发送站内信
            $mailTitle = $this->getConf('mail_title');
            $mailContent = $this->getConf('mail_content');
            \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($mailTitle , $mailContent , $userId , ['msg']);
            //发送短信
            $conponSms = $this->getConf('conpon_sms');
            \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('birth_conpon_sms' , $conponSms , $userId , ['smsnotice']);
        }
    }

    protected function getConf($key){
        $this->conf = \Prj\Model\DataTmp::getConfig('birth');
        $value = $this->conf[$key];
        if($value === null)$this->fatalErr($key. ' 配置缺失!!!');
        return $value;
    }

    public function test(){
        $this->crondBirth();

        $this->listenBirth(['userId' => 'ff8080815ca5f6d1015cd481c9fc000f']);

        $this->crondBirSMS();
    }
}