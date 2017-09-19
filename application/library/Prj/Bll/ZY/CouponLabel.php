<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 14:18
 */

namespace Prj\Bll\ZY;

class CouponLabel extends \Prj\Bll\_BllBase
{
    public function add($params = []){
        $this->log($params , ' params ');
        if($params['labels']){
            $insertArr = [];
            foreach ($params['labels'] as $v){
                $insertArr[] = vsprintf("('%s', '%s', '%s')" , [
                    $params['oid'] , $v , date('Y-m-d H:i:s')
                ]);
            }
            $insertStr = implode(',' , $insertArr);
            $tbName = \Prj\Model\ZyBusiness\CouponLabel::getTbname();
            $sql = <<<sql
INSERT INTO $tbName (`couponId`, `labelId`, `createTime`) 
VALUES 
$insertStr
sql;
            $ret = \Prj\Model\ZyBusiness\CouponLabel::query($sql);
            if(!$ret)return $this->resultError('zy标签入库失败!');
        }
        return $this->resultOK();
    }
}