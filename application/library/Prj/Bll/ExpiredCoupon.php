<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/17
 * Time: 15:20
 */

namespace Prj\Bll;
use Prj\EvtMsg\Sender;
use Prj\Loger;

class ExpiredCoupon extends _BllBase
{
    public function crond()
    {
        \Prj\Loger::$prefix = '[crond]';
        $where = [
            'couponType' => ['COUPON', 'RATECOUPON'],
            'couponStatus' => 'NOTUSED',
            '!name'     =>  "签到红包",
            '>expireTime' => "DATE_ADD(str_to_date(CURDATE(),'%Y-%m-%d %H:%i:%s'),INTERVAL 1 DAY)",
            '<expireTime' => "DATE_ADD(str_to_date(CURDATE(),'%Y-%m-%d %H:%i:%s'),INTERVAL 2 DAY)"
        ];
//        if(!\Prj\Tool\Debug::isTestEnv()){
//        if (!\Prj\Model\ZyBusiness\UserCoupon::getCount($where)) {
//            Loger::out("没有过期红包");
//            return;
//        }
//        }
        \Prj\Loger::out('crond start...');
        $this->SendMsg();
    }

    private $pageSize = 5000;


    private $field = "distinct(userId),couponType";
    private $title = "红包过期提醒";

    private function SendMsg()
    {
        $where = [
            'couponType' => ['COUPON', 'RATECOUPON'],
            'couponStatus' => 'NOTUSED',
            '!name'     =>  "签到红包",
            "DATE_FORMAT(expireTime,'%Y-%m-%d')" => date('Y-m-d',strtotime("+1 day"))
        ];
        $total = \Prj\Model\ZyBusiness\UserCoupon::getCount($where);
        Loger::out($total);
        $count = ceil($total / $this->pageSize);
        $FixList = $this->getList("count(*) as num,sum(couponAmount) as sumAmount,userId",$where,'groupby userId');
        $blackList = \Prj\Model\CouponWhiteList::getRecords("*");

        for ($i = 0; $i < $count; $i++) {
            $list = \Prj\Model\ZyBusiness\UserCoupon::getRecords("distinct(userId),couponType,sum(couponAmount) as sum,count(userId) as num", $where, 'sort expireTime group userId', $this->pageSize, $i * $this->pageSize);

            foreach ($list as $k => $v) {
                $searchPos = array_search(['uid'=>$v['userId']],$blackList);
                if(!in_array(['uid'=>$v['userId']],$blackList)||(in_array(['uid'=>$v['userId']],$blackList)&&json_decode($blackList[$searchPos]['whitelistJson'],true)['expiredCoupon'])){
                    switch ($v['couponType']) {
                        case "COUPON":
                            $content = "您有" . $v['num'] . "个价值" . floatval($v['sum']) . "元的红包明日到期，快去用掉吧";

                            break;
                        case "RATECOUPON":
                            $content = "您有" . $v['num'] . "个价值" . floatval($v['sum']) . "元的加息券明日到期，快去用掉吧";
                            break;

                    }
                    \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($this->title,$content,$v['userId'],['msg','smsnotice']);
                }

            }
        }


    }



    private function getList($field,$where,$sort='',$row='',$page=''){
        $rsForm = $page?($row*($page-1)):'';
        $list = \Prj\Model\ZyBusiness\UserCoupon::getRecords($field,$where,$sort,$row,$rsForm);
        return $list;
    }

    private function getFilterList($arr,$key){
        $data = [];
        foreach($arr as $k=>$v){
         $data[$v[$key]] = $v;
        }
        return $data;
    }






}