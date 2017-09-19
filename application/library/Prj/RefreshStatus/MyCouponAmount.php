<?php


namespace Prj\RefreshStatus;

/**
 * 获取用户信息
 *
 * @author simon.wang
 */
class MyCouponAmount extends Basic
{


    protected function getNodeData($uid)
    {
        if (!$uid) {
            return null;
        }

        return \Prj\Bll\UserCoupon::getUserCouponAmount($uid);
    }
}
