<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/12
 * Time: 17:41
 */

namespace Prj\Bll\Finals;

use Lib\Misc\Result;
use Prj\Bll\_BllBase;
use Prj\Bll\User;
use Prj\Loger;
use Prj\Model\BankOrder;
use Prj\Model\MimosaBankOrder;
use Prj\Model\MoneyInvestorBankOrder;
use Prj\Model\MoneyInvestorTradeOrder;
use Prj\Model\TradeOrder;
use Sooh2\DB\Myisam\Cmd;

class CouponFinal extends _BllBase
{
    public function crond($ymd){
        \Prj\Loger::$prefix = '[crond]';
        $hour = date('H');
        if($hour!=3) return;
        \Prj\Loger::out('crond start...');
        $this->CouponFinal($ymd);
    }




    public function CouponFinal($ymd)
    {

        $yesterday = $ymd?date('Y-m-d',strtotime("-1 days",strtotime($ymd))):date('Y-m-d', strtotime('-1 days'));

        $list = \Prj\Model\Coupon::getRecords("oid,title,purposeCode,typeCode,amount", ['status' => "yes", "!typeCode" => "TASTECOUPON"]);
        Loger::outVal("dddd", $list);
        try {

            foreach ($list as $k => $v) {
                $obj = \Prj\Model\CouponFinal::getCopy(['couponId'=>$v['oid'],'ymd'=>date('Ymd',strtotime($yesterday))]);
                $obj->load();
                switch ($v['typeCode']) {
                    case "RATECOUPON":
                        $sql = "select count(a.ucId) as useCount, SUM(a.couponAmount*b.orderAmount*(IF(c.durationPeriodType='MONTH',c.durationPeriodDays*30,c.durationPeriodDays))/365) as useCost,SUM(b.orderAmount) as investAmount from " . \Prj\Model\ZyBusiness\UserCoupon::getTbname() . " a
                     LEFT JOIN " . \Prj\Model\ZyBusiness\InverstorTradeOrder::getTbname() . " b on a.ucId=b.userCouponId LEFT JOIN " . \Prj\Model\ZyBusiness\ProductInfo::getTbname() . " c 
                     on c.productId=b.productId and b.orderType='INVEST'  and b.orderStatus='CONFIRMED' where   a.couponType='RATECOUPON' and a.couponId='" . $v['oid'] . "' and  str_to_date(a.useTime,'%Y-%m-%d')='" . $yesterday . "' limit 1";
//                    $staticInfo = \Prj\Model\ZyBusiness\UserCoupon::getRecord("SUM(couponAmount) couponAmount,COUNT(a.ucId) useCount",['couponId'=>$v['oid'],"str_to_date(useTime,'%Y%m%d')"=>$yesterday]);
                        $lenderCount = \Prj\Model\ZyBusiness\UserCoupon::getRecord("count(ucId) as lenderCount", ['couponType' => "RATECOUPON", "str_to_date(lenderTime,'%Y-%m-%d')" => $yesterday, 'couponId' => $v['oid']]);

                        $sqlLog = "select count(distinct userId) as checkUsersNum from db_log.tb_log".date('Ym',strtotime($yesterday))." a where evt='inMyCouponPage' and  FROM_UNIXTIME(`dt`,'%Y-%m-%d')='".$yesterday."' and userId in  (select b.userId from ".\Prj\Model\ZyBusiness\UserCoupon::getTbname()." b  where  couponType='RATECOUPON'
                        and str_to_date(b.lenderTime,'%Y-%m-%d')='".$yesterday."' and b.couponId='".$v['oid']."')";
                        $checkUsersNum = \Prj\Model\ZyBusiness\UserCoupon::query($sqlLog)[0];
                        $data = \Prj\Model\ZyBusiness\UserCoupon::query($sql)[0];

                        break;
                    default :
                        $sql = "select count(a.ucId) as useCount, SUM(a.couponAmount) as useCost,SUM(b.orderAmount) as investAmount from " . \Prj\Model\ZyBusiness\UserCoupon::getTbname() . " a
                     LEFT JOIN " . \Prj\Model\ZyBusiness\InverstorTradeOrder::getTbname() . " b on b.userCouponId=a.ucId and b.orderType='INVEST'  and b.orderStatus='CONFIRMED'  where a.couponId='" . $v['oid'] . "' and  str_to_date(a.useTime,'%Y-%m-%d')='" . $yesterday . "' limit 1";
                        $data = \Prj\Model\ZyBusiness\UserCoupon::query($sql)[0];
                        $lenderCount = \Prj\Model\ZyBusiness\UserCoupon::getRecord("count(ucId) as lenderCount", ["str_to_date(lenderTime,'%Y-%m-%d')" => $yesterday, 'couponId' => $v['oid']]);
                        $sqlLog = "select count(distinct userId) as checkUsersNum  from db_log.tb_log".date('Ym',strtotime($yesterday))." a where FROM_UNIXTIME(`dt`,'%Y-%m-%d')='".$yesterday."' and evt='inMyCouponPage' and userId in  (select distinct(userId) from ".\Prj\Model\ZyBusiness\UserCoupon::getTbname()." b  where 
                         str_to_date(lenderTime,'%Y-%m-%d')='".$yesterday."' and couponId='".$v['oid']."')";
                        $checkUsersNum = \Prj\Model\ZyBusiness\UserCoupon::query($sqlLog)[0];


                        break;
                }
                $obj->setField("leadCount",$lenderCount['lenderCount']?$lenderCount['lenderCount']:0);
                $obj->setField("checkUsersNum",$checkUsersNum['checkUsersNum']);
                $obj->setField("title",$v['title']);
                $obj->setField("purposeCode",$v['purposeCode']);
                $obj->setField("useCount",$data['useCount']?$data['useCount']:0);
                $obj->setField("useCost",$data['useCost']?$data['useCost']:0);
                $obj->setField("investAmount",$data['investAmount']?$data['investAmount']:0);
                $obj->saveToDB();


            }
        }catch (\Exception $ex){
            Loger::out("[CouponFinal]优惠券统计出错！".$ex->getMessage());
        }
    }



}
