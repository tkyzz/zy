<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-04 14:10
 */

namespace Prj\Model;

class InviteRebateInfo extends _ModelBase
{
    protected static $pkeyName = 'id';

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_invite_rebate_info_0';
    }

    /**
     * 发放一条返利（待返状态）
     * @param string $uid 接受返利的用户ID
     * @param string $formUid 来源用户的ID
     * @param string $amount 返利金额
     * @param string $orderNo 订单ID
     * @param string $productNo 产品ID
     * @param int $isFirstBuy 是否首投：0不是，1是
     * @param array $arrCoupon 优惠券限制
     * @param int $status 状态位：0待返，1已返, 4发放失败
     * @return bool|static
     * @author lingtima@gmail.com
     */
    public static function giveRebate($uid, $formUid = '', $amount = '', $orderNo = '', $productNo = '', $isFirstBuy = 0, $arrCoupon = [], $status = 0)
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('给' . $uid . '发放返利');
        $retry = 1;
        while ($retry < 6) {
            $id = static::buildId($uid);
            $Model = static::getCopy($id);
            $Model->load();
            if ($Model->exists()) {
                $retry++;
                continue;
            } else {
                break;
            }
        }
        if ($retry >= 6) {
            return false;
        }

        $Model->setField('uid', $uid);
        $Model->setField('formUid', $formUid);
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($formUid);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            $Model->setField('formUserPhone', $ModelUserFinal->getField('phone'));
            $Model->setField('formUserName', \Prj\Tool\Common::getInstance()->getNameByRealnameAndGender($ModelUserFinal->getField('nickname'), $ModelUserFinal->getField('gender')));
        }
        $Model->setField('amount', $amount);
        $Model->setField('orderNo', $orderNo);
        $Model->setField('productNo', $productNo);
        $Model->setField('status', 0);
        $isFirstBuy && $Model->setField('isFirstBuy', 1);
        $Model->setField('createTime', date('Y-m-d H:i:s'));
        $Model->setField('updateTime', date('Y-m-d H:i:s'));
        if (!empty($arrCoupon)) {
            foreach ($arrCoupon as $k => $v) {
                if (in_array($k, ['couponType', 'couponInvestAmount', 'couponName', 'couponProductList'])) {
                    $Model->setField($k, $v);
                }
            }
        } else {
            $Model->setField('couponType', \Prj\Model\Coupon::type_redPackets);
        }
        $ret = $Model->saveToDB();
        if ($ret) {
            return $Model;
        } else {
            return false;
        }
    }

    /**
     * 生成返利ID
     * @param string $uid 用户ID
     * @return string
     * @author lingtima@gmail.com
     */
    public static function buildId($uid = '')
    {
        $head = !empty($uid) ? substr($uid, -6) : mt_rand(100000, 999999);
        $body = microtime(true) * 10000;
//        \Sooh2\Misc\Loger::getInstance()->app_trace($body);
        $tail = mt_rand(10000, 99999);
        return "{$head}{$body}{$tail}";
    }
}