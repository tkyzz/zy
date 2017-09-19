<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;
use \Lib\Misc\Result;
use \Prj\Loger;

/**
 * Description of User
 *
 * @author simon.wang
 */
class Activity extends _BllBase {

    /**
     * 新增一个活动
     * productsMap : [ '003' => '悦月盈' ]
     * @param array $params
     * @return array
     */
   public function addActivity($params = []){
       \Prj\Loger::$prefix = '[addActivity]';
       if(in_array($params['actCode'] , \Prj\Model\Activity::$canSetRuleActCode)){
           $hasRule = true;
           $params['coupons'] = '';
       }else{
           $hasRule = false;
           $params['labels'] = $params['rules'] = '';
       }
       if($hasRule){
           if(!Result::paramsCheck($params , ['title','start','finish','rules','actCode'])){
               return Result::get(RET_ERR , '参数错误['. Result::$errorParam .']!');
           }
           $params['labels'] = is_array($params['labels']) ? implode(',',$params['labels']) : $params['labels'];
       }else{
           if(!Result::paramsCheck($params , ['title','start','finish','coupons','actCode'])){
               return Result::get(RET_ERR , '参数错误['. Result::$errorParam .']!');
           }
       }

       $eventOid = isset($params['oid']) ? $params['oid'] : \Lib\Misc\StringH::createOid();
       $params['eventId'] = $eventOid;
       //开始数据库更新
       $activity = \Prj\Model\Activity::getCopy($eventOid);
       $activity->load();
       if($activity->exists())return Result::get(RET_ERR , '数据已经存在!');
       $activity->setField('actCode' , $params['actCode']); //*
       $activity->setField('isDel' ,'yes');
       $activity->setField('coupons' , $params['coupons']);//*
       $activity->setField('createTime' , date('YmdHis'));
       $activity->setField('startTime' , date('YmdHis' , strtotime($params['start'])));
       $activity->setField('finishTime' , date('YmdHis' , strtotime($params['finish'])));

       //依赖国槐的字段
       $activity->setField('title' , $params['title']);
       $activity->setField('description' , $params['description']);
       $activity->setField('statusCode' , 'pending');
       $activity->setField('active' , 'wait');

       if($hasRule){
           $activity->setField('rules' , $params['rules']);
           $activity->setField('labels' , $params['labels']);
       }
       try{
           $ret = $activity->saveToDB();
           if(!$ret)return Result::get(RET_ERR , '更新失败!');
           \Prj\Loger::out('本地Activiy表更新成功...');
       }catch (\Exception $e){
           return Result::get(RET_ERR , $e->getMessage());
       }
       //开始更新tulip数据库
//       $res = \Prj\Bll\GH\Event::getInstance()->add($params); //同步更新国槐的数据
//       if(!$this->checkRes($res)){
//           $retDel = \Prj\Model\Activity::deleteOne(['oid' => $eventOid]);
//           if(!$retDel)$this->fatalErr('回滚错误,删除本地数据失败!');
//           return $res;
//       }
       return $this->resultOK();
   }

    /**
     * 更新活动
     * @param $params
     * @return array
     */
   public function updateActivity($params){
       if(isset($params['actCode'])){
           if(in_array($params['actCode'] , \Prj\Model\Activity::$canSetRuleActCode)){
               $params['coupons'] = '';
           }else{
               $params['labels'] = $params['rules'] = '';
           }
       }
       if(!Result::paramsCheck($params , ['oid']))
           return Result::get(RET_ERR , '参数不能为空['. Result::$errorParam .']');

       if($params['labels']){
           $params['labels'] = is_array($params['labels']) ? implode(',',$params['labels']) : $params['labels'];
       }

        $actOid = $params['oid'];
        $activity = \Prj\Model\Activity::getCopy($actOid);
        $activity->load();
        if(!$activity->exists())return Result::get(RET_ERR , '活动信息不存在!');
        $actInfo = $this->getRecords(['oid' => $actOid])['data'][0];
        if(empty($actInfo))return Result::get(RET_ERR , '活动记录不存在!');
        //Loger::out($actInfo);
        if($actInfo['status'] == 'pending'){
            Loger::out('状态为: pending 未审核的活动可以编辑...');
            //允许编辑
            $allow = true;
        }else{
            Loger::out('状态为: '.$actInfo['status'].' 该状态下不允许编辑...');
            $allow = false;
        }

        if($allow){
            $actAollow = ['actCode','isDel','coupons','isDel','labels','rules','title','description','startTime','finishTime','statusCode','active'];
        }else{
            $actAollow = ['isDel', 'active'];
        }
        $initData = $activity->dump(); //保存数据 回滚用

        if(isset($params['start']))$params['startTime'] = date('YmdHis' , strtotime($params['start']));
        if(isset($params['finish']))$params['finishTime'] = date('YmdHis' , strtotime($params['finish']));
        if(isset($params['status']))$params['statusCode'] = $params['status'];
        if($params['status'] == 'pass'){
           //判断是否上架
           $time = time();
           $startTime = strtotime($actInfo['startTime']);
           $finishTime = strtotime($actInfo['finishTime']);
           Loger::out('活动是否上架 '.$actInfo['startTime'].'~'.$actInfo['finishTime'].' '.$time);
           if($time >= $startTime && $time <= $finishTime){
               $params['active'] = 'on';
           }else if ($time > $finishTime){
               $params['active'] = 'off';
           }
        }
        foreach ($params as $k => $v){
            if(in_array($k , $actAollow)){
                $activity->setField($k , $v);
            }
        }
        $this->log($params , 'params');
        // return $this->resultError('xxxxxxxxxxxxxxxxxx');
        $ret =  $activity->saveToDB();
       //本地库的更新
       if(!$ret)return Result::get(RET_ERR , '更新失败!');

       if(\Prj\Tool\System::isGh()){
           if($allow){
               $params['actInfo'] = $actInfo;
               $res = \Prj\Bll\GH\Event::getInstance()->editMore($params);
           }else{
               $res = \Prj\Bll\GH\Event::getInstance()->editLittle($params);
           }

           if(!$this->checkRes($res)){
               //回滚操作
               foreach ($initData as $k => $v){
                   if(in_array($k , $actAollow)){
                       $activity->setField($k , $v);
                   }
               }
               $ret =  $activity->saveToDB();
               if(!$ret){
                   $msg = '回滚错误,本地数据回滚失败!';
                   \Sooh2\Misc\Loger::getInstance()->sys_warning($msg);
                   $this->fatalErr($msg);
               }
               return $res;
           }
       }

       return $this->resultOK();
   }

    /**
     * 获取信息列表
     * @param array $where
     * @param null $sortgrpby
     * @param null $pageSize
     * @param int $rsFrom
     * @return array
     */
   public function getRecords($where = [], $sortgrpby = null,$pageSize = null,$rsFrom = 0){
        $data = \Prj\Model\Activity::getRecords('' , $where , $sortgrpby , $pageSize , $rsFrom);
        if($data){
            foreach ($data as &$v){
                $v['statusCH'] = \Prj\Model\TulipEvent::$statusMap[$v['statusCode']];
                $v['status'] = $v['statusCode'];
                $v['activeCH'] = \Prj\Model\TulipEvent::$activeMap[$v['active']];
                $v['actCodeCH'] = \Prj\Model\Activity::$actCodeMap[$v['actCode']];
                $v['labels'] = $v['labels'] ? explode(',' , $v['labels']) : [];
                $v['rules'] = $v['rules'] ? json_decode($v['rules'] , true) : [];
            }
        }
        $this->log($data , 'data');
        return Result::get(RET_SUCC ,'' , $data);
   }

    public function getRecord($where = []){
        $res = $this->getRecords($where , null , 1);
        if(!$this->checkRes($res))return $res;
        return $this->resultOK([
            'info' => $res['data'][0],
        ]);
    }

   public function getCouponInfoByEventId($eventId){
        $res = $this->getRecord(['oid' => $eventId]);
        if(!$this->checkRes($res))return $res;
        $actInfo = $res['data']['info'];
        $couponId = $actInfo['coupons'];
        if(empty($couponId))return $this->resultOK(['info'=>[]]);
        $res = \Prj\Bll\Coupon::getInstance()->getRecord(['oid' => $couponId]);
        return $res;
   }

    /**
     * 查询所有可用的活动列表
     * @param null $actCode
     * @return array|bool|\mysqli_result
     */
    public function getValidRecords($actCode = null){
        $time = date('YmdHis');
        $where = [
            '[startTime' => $time,
            ']finishTime' => $time,
            'statusCode' => 'pass',
            'active' => 'on',
            'isdel' => 'yes',
        ];
        if($actCode)$where['actCode'] = $actCode;
        return $this->getRecords($where)['data'];
    }
}
