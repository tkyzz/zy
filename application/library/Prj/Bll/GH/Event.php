<?php
/**
 * 国槐的事件表更新
 * 同步更新国槐的数据库
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/28
 * Time: 14:59
 */

namespace Prj\Bll\GH;

class Event extends \Prj\Bll\_BllBase
{
    /**
     * Hand 国槐的活动新增
     * @param $params
     * @return array
     */
    public function add($params){
        if(!\Lib\Misc\Result::paramsCheck($params , ['eventId','title','start','finish'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $time = date('Y-m-d H:i:s');

        $insertEvent = [
            'oid' => $params['eventId'],
            'title' => $params['title'], //*
            'description' => $params['description'],
            'start' => $params['start'], //*
            'finish' => $params['finish'], //*
            'createUser' => 'php_system',
            'createTime' => $time,
            'updateTime' => date('Y-m-d H:i:s'),
            'updateUser' => 'php_system',
            'status' => 'pending',
            'active' => 'wait',
            //'remark' => '',
            'type' => 'custom',
            'isdel' => 'yes',
        ];

        try{
            //开始更新tulip数据库
            \Prj\Model\TulipEvent::query('START TRANSACTION');
            //活动入库
            $ret = \Prj\Model\TulipEvent::saveOne($insertEvent);
            if(!$ret)throw new \Exception('活动入库失败![tulip]');
            \Prj\Loger::out('tulip活动入库成功...');
            $this->resetCoupon($params['eventId'] , $params['coupons']);
            \Prj\Model\TulipEvent::query('COMMIT');
            \Prj\Loger::out('更新完毕!');
            return $this->resultOK();
        }catch (\Exception $e){
            \Prj\Model\TulipEvent::query('ROLLBACK');
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
        if(!\Lib\Misc\Result::paramsCheck($params , ['oid','actInfo'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        //允许编辑
        $actInfo = $params['actInfo'];
        $time = date('Y-m-d H:i:s');
        $updateData = [
            'updateUser' => 'php_system',
            'updateTime' => $time,
        ];
        $changeCoupon = false; //是否需要换券
        if(isset($params['start']))$updateData['start'] = $params['start'];
        if(isset($params['finish']))$updateData['finish'] = $params['finish'];
        if(isset($params['title']))$updateData['title'] = $params['title'];
        if(isset($params['description']))$updateData['description'] = $params['description'];
        if(isset($params['status']))$updateData['status'] = $params['status'];
        if(isset($params['isDel']))$updateData['isDel'] = $params['isDel'];
        if(isset($params['active']))$updateData['active'] = $params['active'];
        if(isset($params['coupons'])){
            //改券的逻辑相对复杂
            $oldCouponInfo = \Prj\Bll\Event::getCouponInfoByEventId($params['oid']);
            if(empty($oldCouponInfo))throw new \Exception('券配置异常!');
            if($oldCouponInfo['oid'] != $params['coupons']){
                //换新券更新
                $changeCoupon = true;
            }
        }
        //开始更新数据库
        \Prj\Model\TulipEvent::query('START TRANSACTION');
        try{
            //活动表更新
            \Prj\Loger::out('本次更新的数据: ' . json_encode($updateData));
            $ret = \Prj\Model\TulipEvent::updateOne($updateData , ['oid' => $params['oid']]);
            if(!$ret)throw new \Exception('tulip活动表更新失败!');
            if($changeCoupon){
                //需要更新券的时候重新创建规则
                $this->resetCoupon($params['oid'] , $params['coupons']);
            }
            //提交事务
            \Prj\Model\TulipEvent::query('COMMIT');
            \Prj\Loger::out('更新完毕!');
            return $this->resultOK();
        }catch (\Exception $e){
            //国槐的回滚
            \Prj\Model\TulipEvent::query('ROLLBACK');
            \Prj\Loger::out('国槐更新失败,回滚...');
            return $this->resultError($e->getMessage());
        }
    }

    /**
     * Hand 不允许编辑情况下的更新操作
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function editLittle($params = []){
        //不允许编辑的时候,仅供删除
        //活动表更新
        if(isset($params['isDel'])){
            $updateData = ['isDel' => $params['isDel']];
            \Prj\Loger::out('本次更新的数据: ' . json_encode($updateData));
            $ret = \Prj\Model\TulipEvent::updateOne( $updateData, ['oid' => $params['oid']]);
            if(!$ret)throw new \Exception('tulip活动表更新失败!');
            return $this->resultOK();
        }
        return $this->resultError('没有需要更新的操作');
    }

    /**
     * 修改活动绑定的券ID
     * @param $eventId
     * @param $couponId
     * @return bool
     * @throws \Exception
     */
    protected function resetCoupon($eventId , $couponId){
        $time = date('Y-m-d H:i:s');
        $ruleOid = \Lib\Misc\StringH::createOid();
        $insertRule = [
            'oid' => $ruleOid,
            'type' => 'get',
            'weight' => 'and',
            'createUser' => 'php_system',
            'updateUser' => 'php_system',
            'updateTime' => $time,
            'createTime' => $time,
            'status' => 'yes',
        ];

        $couponRuleOid = \Lib\Misc\StringH::createOid();
        $insertCouponRule = [
            'oid' => $couponRuleOid,
            'ruleId' => $ruleOid,
            'couponId' => $couponId, //*
            'createUser' => 'php_system',
            'updateUser' => 'php_system',
            'updateTime' => $time,
            'createTime' => $time,
        ];

        $eventRuleOid =  \Lib\Misc\StringH::createOid();
        $insertEventRule = [
            'oid' => $eventRuleOid,
            'eventId' => $eventId,
            'ruleId' => $ruleOid,
            'status' => 'yes',
            'createTime' => $time,
            'updateTime' => $time,
            'createUser' => 'php_system',
            'updateUser' => 'php_system',
        ];

        $ret = \Prj\Model\TulipRule::saveOne($insertRule);
        if(!$ret)throw new \Exception('规则入库失败![tulip]');
        \Prj\Loger::out('tulip规则入库成功...');
        //活动规则入库
        $ret = \Prj\Model\TulipEventRule::saveOne($insertEventRule);
        if(!$ret)throw new \Exception('活动规则入库失败![tulip]');
        \Prj\Loger::out('tulip活动规则入库成功...');
        //券规则入库
        if($couponId){
            $ret = \Prj\Model\TulipCouponRule::saveOne($insertCouponRule);
            if(!$ret)throw new \Exception('券规则入库失败![tulip]');
        }
        \Prj\Loger::out('tulip券规则入库成功...');
        return true;
    }

    /**
     * 查询所有可用的活动列表
     * @param null $actCode
     * @return array|bool|\mysqli_result
     */
    public function getValidRecords($actCode = null){
        $tbAct = \Prj\Model\Activity::db()->kvobjTable();
        $tbEvent = ' jz_db.t_event ';
        $date = date('Y-m-d');
        $time = date('YmdHis');
        $actCodeWhere = $actCode ? " a.actCode = '$actCode' and " : '';
        $sql = <<<sql
          SELECT a.* FROM $tbAct a LEFT JOIN $tbEvent b ON a.oid = b.oid WHERE 
            $actCodeWhere a.isDel = 'yes' and 
            b.start <= '$date' and 
            b.finish >= '$date' and 
            a.startTime <= '$time' AND 
            a.finishTime > '$time' AND 
            b.status = 'pass' and 
            b.active = 'on' LIMIT 9999
sql;
        return \Prj\Model\Activity::query($sql);
    }

}