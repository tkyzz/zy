<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/3
 * Time: 14:46
 */
namespace Prj\Bll\ZY;

class TradeOrder extends \Prj\Bll\_BllBase
{
    /**
     * Hand 查询用户的首次定期订单
     * @param $userId
     * @return array|bool|\mysqli_result
     */
    public function getFirstDingOrderInfo($userId){
        $productType = \Prj\Model\ZyBusiness\ProductInfo::$type_ding;
        $orderStatus = \Prj\Model\ZyBusiness\TradOrder::$status_confirm;
        $dbName = \Prj\Model\ZyBusiness\ProductInfo::getDbname();
        $tbName = \Prj\Model\ZyBusiness\TradOrder::getTbname();
        $sql = <<<sql
SELECT o.* , p.productType from $tbName o
LEFT JOIN $dbName.tpf_product_info p
ON o.productId = p.productId
where userId = '$userId' and productType = '$productType' and orderStatus = '$orderStatus'
ORDER BY createTime , orderId LIMIT 1
sql;
        return \Prj\Model\ZyBusiness\ProductInfo::query($sql)['0'] ?: [];
    }



    /*查询用户的首页购买订单*/
    public function getFirstOrderInfo($userId){

        $orderStatus = \Prj\Model\ZyBusiness\TradOrder::$status_confirm;
        $dbName = \Prj\Model\ZyBusiness\ProductInfo::getDbname();
        $tbName = \Prj\Model\ZyBusiness\TradOrder::getTbname();
        $sql = <<<sql
SELECT o.* , p.productType from $tbName o
LEFT JOIN $dbName.tpf_product_info p
ON o.productId = p.productId
where userId = '$userId' and orderStatus = '$orderStatus'
ORDER BY createTime , orderId LIMIT 1
sql;
        return \Prj\Model\ZyBusiness\ProductInfo::query($sql)['0'] ?: [];
    }
}