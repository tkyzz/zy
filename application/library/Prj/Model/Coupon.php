<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class Coupon extends _ModelBase
{
    const type_redPackets = 'REDPACKETS';
    const type_coupon = 'COUPON';
    const type_jiaxi = 'RATECOUPON'; //RATECOUPON

    public static $type_map = [
        self::type_coupon => '代金券',
        self::type_redPackets => '现金红包',
        self::type_jiaxi => '加息券',
    ];

    public static function getAdminOption(){
        return [
            self::type_coupon => '代金券',
            self::type_redPackets => '现金红包',
            self::type_jiaxi => '加息券',
        ];
    }

    public static $support_send_types = ['REDPACKETS' , 'COUPON' , 'RATECOUPON']; //目前支持发放的优惠券

    protected function onInit(){
        parent::onInit();
        $this->_tbName = 'tb_coupon_0';
    }

    public static function getOneByOid($oid){
        $db = static::getCopy('')->dbWithTablename();
        $where = ['oid' => $oid];
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    public static function updateNum($oid , $field , $change){
        if($change < 0){
            $whereOt = " AND $field >= ". ($change * -1) ." ";
        }else{
            $whereOt = '';
        }
        $table = self::getTbname();
        $sql = <<<sql
            UPDATE $table SET $field = $field + $change WHERE oid = '$oid' $whereOt LIMIT 1 ;
sql;
        return self::query($sql);
    }



    public static function getCouponMaps($params){
        $couponsRes = \Prj\Bll\Coupon::getInstance()->getRecords($params);
        $coupons = $couponsRes['data'];
        $couponsMap = [];
        foreach ($coupons as $v) {
            switch ($v['typeCode']){

                case self::type_jiaxi:
                    if($v['isFloat']) {
                        $couponsMap[$v['oid']] = $v['title'] . '  浮动';
                    }else{
                        $couponsMap[$v['oid']] = $v['title'] . ' ' . ($v['amount'] ? $v['amount'] . '%' : '');
                    }
                    break;
                default:
                    if($v['isFloat']) {
                        $couponsMap[$v['oid']] = $v['title'] . ' ' . ' 浮动';
                    }else{
                        $couponsMap[$v['oid']] = $v['title'] . ' ' . ($v['amount'] ? $v['amount'] . '元' : '');
                    }break;

            }

        }
        return $couponsMap;
    }
}