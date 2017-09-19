<?php
/**
 * 国槐的券更新
 * 同步更新国槐的数据库
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/28
 * Time: 14:59
 */

namespace Prj\Bll\GH;

class Coupon extends \Prj\Bll\_BllBase
{
    /**
     * Hand 国槐的活动新增
     * @param $params
     * @return array
     */
    public function add($params){
        if(!\Lib\Misc\Result::paramsCheck($params , ['oid','name','type','upperAmount','amountType',
            'disableDate','investAmount'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $tulipInsertData = [
            'oid' => $params['oid'],
            'name' => $params['name'],
            'description' => $params['description'],
            'type' => $params['type'],
            'upperAmount' => $params['upperAmount'],
            'amountType' => $params['amountType'],
            'count' => $params['count'],
            'remainCount' => $params['count'],
            'totalAmount' => $params['totalAmount'],
            'remainAmount' => $params['remainAmount'],
            'status' => 'yes',
            'createUser' => 'php_system',
            'updateUser' => 'php_system',
            'updateTime' => date('Y-m-d H:i:s'),
            'payflag' => 'redeming',
            'disableDate' => $params['disableDate'],
            'disableType' => 'DAY',
            'investAmount' => $params['investAmount'],
            'products' => $params['products'],
            'rules' => '全场适用',
            'useCount' => 0,
            'overlap' => 'yes',
            'createTime' => date('Y-m-d H:i:s'),
        ];

        //tulip库插入 开启事务
        $ret = \Prj\Model\TulipCoupon::query('START TRANSACTION');
        if(!$ret)return \Lib\Misc\Result::get(RET_ERR , '事务开启失败!');

        try{
            if( isset($params['productsMap']) && !empty($params['productsMap']) ){
                //插入券使用规则
                foreach ($params['productsMap'] as $k => $v){
                    $rangeOid = \Lib\Misc\StringH::createOid();
                    $ret = \Prj\Model\TulipCouponRange::saveOne([
                        'oid' => $rangeOid,
                        'couponBatch' => $params['oid'],
                        'labelCode' => $k,
                        'labelName' => $v,
                    ]);
                    if(!$ret)throw new \Exception('券使用范围插入失败!');
                    \Prj\Loger::out('券范围 '.$rangeOid.' 入库成功...');
                }
            }
            //插入券
            $ret = \Prj\Model\TulipCoupon::saveOne($tulipInsertData);
            if(!$ret)throw new \Exception('券插入失败!');
            \Prj\Loger::out('tulip券入库成功...');

            \Prj\Model\TulipCoupon::query('COMMIT');
            \Prj\Loger::out('更新完毕!');
            return \Lib\Misc\Result::get(RET_SUCC);
        }catch (\Exception $e){
            \Prj\Model\TulipCoupon::query('ROLLBACK');
            return $this->resultError($e->getMessage());
        }
    }

    /**
     * Hand 允许编辑情况下的更新操作
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function editMore($params = []){
        $oid = $params['oid'];
        unset($params['oid']);
        $ret = \Prj\Model\TulipCoupon::updateOne($params , ['oid' => $oid]);
        if(!$ret)return $this->resultError('TulipCoupon 更新失败');
        return $this->resultOK();
    }

    /**
     * Hand 不允许编辑情况下的更新操作
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function editLittle($params = []){

    }

}