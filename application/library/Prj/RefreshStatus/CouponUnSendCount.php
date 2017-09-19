<?php
/**
 * 用户未发券数量
 * User: amdin
 * Date: 2017/7/18
 * Time: 15:16
 */
namespace Prj\RefreshStatus;

class CouponUnSendCount extends Basic
{
    protected function getNodeData($userId){
        if(empty($userId)) return null;
        $params['userId'] = $userId;
        $params['couponStatus'] = 'NOTUSED';
        $params['>expireTime'] = date('Y-m-d H:i:s');
        return \Prj\Model\ZyBusiness\UserCoupon::getCount($params);
    }
}