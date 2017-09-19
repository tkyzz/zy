<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 14:18
 */

namespace Prj\Bll\ZY;

class Removal extends \Prj\Bll\_BllBase
{
    protected $newDb = 'zy_db';

    protected $oldDb = 'transition';

    protected $table;

    protected $noTable = [];

    protected $conf = [
        'start' => '20000101000000',
        'finish'=> '20171015235959',
    ];

    protected $needSyncTb = [
        'jz_platform_mail_type',
        't_platform_mail', //万级 动态
        'tb_accountlog_0',
        'tb_activity_0',
        'tb_activity_coupon_0', //万级 动态
        'tb_coupon_0',
        'tb_crondlog_0',
        'tb_data_tmp_0',
        //'tb_device_contractid_0',
        //'tb_device_contractid_baklog',
        'tb_manage_activity_scheme',
        'tb_manage_activity_scheme_config',
        'tb_manage_banner',
        'tb_manage_log',
        'tb_manage_menu',
        'tb_manage_notice',
        'tb_managers_0',
        'tb_msgsentlog_0', //万级 动态
        'tb_msgtpl_0',
        //'tb_user_final_0',
        'tb_contract_info', //万级
        'jz_channel_info',

        'jz_app_version',
        'jz_system_getui_config',
        'jz_system_module_config'
    ];

    public function clear(){
        foreach ($this->needSyncTb as $tb){
            \Prj\Model\Flexible::reset('ZyDb' , $tb);
            $this->output('begin clear ' . $tb . ' ...');
            \Prj\Model\Flexible::query('DELETE FROM ' . $tb);
            $this->output('clear ' . $tb . ' success');
        }
    }

    public function run(){
//        return $this->clear();
        $single = '';
        foreach ($this->needSyncTb as $table){
            if($single){
                if($single != $table)continue;
            }
            $this->table = $table;
            try{
                $method = 'sync_' . $table;
                $this->title($table);
                $this->$method();
            }catch (\Exception $e){}
        }

    }

    protected function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        return $this->normalSync();
    }

    protected function normalSync(){
        $res = $this->copy($this->table);
        if($this->checkRes($res))$this->output('同步成功...');
    }

//    protected function sync_tb_accountlog_0(){
//        return $this->normalSync();
//    }

//    protected function sync_jz_platform_mail_type(){
//        return $this->normalSync();
//    }

//    protected function sync_tb_crondlog_0(){
//        return $this->normalSync();
//    }

//    protected function sync_tb_device_contractid_baklog(){
//        return $this->normalSync();
//    }

//    protected function sync_jz_channel_info(){
//        return $this->normalSync();
//    }

    protected function sync_tb_device_contractid_0(){
        $res = $this->checkNoKey($this->table);
        if(!$this->checkRes($res))return $this->output($res['message']);
        return $this->sync_tb_accountlog_0();
    }
    protected function sync_tb_manage_activity_scheme(){
        $map = [
            '签到' => 'Signin',
            '活动图标' => 'ActivityIcon',
            '新手引导' => 'NewbieReward',
            '邀请配置' => 'Invite',
            '系统配置' => 'System',
            '其它配置' => 'Other',
        ];
        \Prj\Model\Flexible::reset('User' , $this->table);
        $list = \Prj\Model\Flexible::getRecords(null , []);
        \Prj\Model\Flexible::reset('ZyDb' , $this->table);
        try{
            foreach ($list as $v){
                $insert = $v;
                $insert['type_name'] = $map[$v['activity_name']];
                $ret = \Prj\Model\Flexible::saveOne($insert);
            }
        }catch (\Exception $e){
            if($e->getCode() == \Sooh2\DB\DBErr::duplicateKey){
                $this->output('已经同步过...');
                return $this->resultError('已经同步过...');
            }else{
                var_dump($e->getMessage());
                $this->output('同步异常!!!');
                return $this->resultError('同步异常!!!');
            }
        }
        if($ret){
            $this->output('同步成功...');
        }else{
            $this->output('同步失败!!!');
        }
    }

//    protected function sync_tb_manage_activity_scheme_config(){
//        return $this->normalSync();
//    }

//    protected function sync_tb_manage_banner(){
//        return $this->normalSync();
//    }

    protected function sync_tb_manage_log(){
        $res = $this->checkNoKey($this->table);
        if(!$this->checkRes($res))return $this->output($res['message']);
        return $this->normalSync();
    }

//    protected function sync_tb_manage_menu(){
//        return $this->normalSync();
//    }

    protected function sync_tb_manage_notice(){
        $res = $this->checkNoKey($this->table);
        if(!$this->checkRes($res))return $this->output($res['message']);
        return $this->normalSync();
    }

//    protected function sync_tb_managers_0(){
//        return $this->normalSync();
//    }

    protected function sync_tb_msgsentlog_0(){
        $this->setStartFinish('ymdhis' , $this->conf['start'] , $this->conf['finish']);
        return $this->normalSync();
    }

    protected function sync_tb_msgtpl_0(){
        $res = $this->checkNoKey($this->table);
        if(!$this->checkRes($res))return $this->output($res['message']);
        return $this->normalSync();
    }

    protected function sync_tb_contract_info(){
        $res = $this->copy('jz_contract_info' , $this->table);
        if($this->checkRes($res))$this->output('同步成功...');
    }

    protected function sync_tb_activity_coupon_0(){
        $fieldsMap = ['oid' , 'reqOid' , 'type' , 'productId' , 'orderId' , 'ucUserId' , 'eventId' ,
            'statusCode' , 'rowVersion' , 'rowLock' , 'ret' , 'amount' , 'createTime'];
        $fieldsMapStr = implode(',' , $fieldsMap);
        $table = $this->table;
        $newDb = $this->newDb;
        $oldDb = $this->oldDb;
        $start = $this->conf['start'];
        $finish = $this->conf['finish'];
        $sql = <<<sql
INSERT INTO $newDb.$table($fieldsMapStr , userCouponId) SELECT $fieldsMapStr , '' as userCouponId FROM $oldDb.$table WHERE createTime >= '$start' AND createTime < '$finish'
sql;
        $res = $this->insertSql($sql);
        if($this->checkRes($res))$this->output('同步成功...');
    }

    protected function sync_tb_data_tmp_0(){
        $newDb = $this->newDb;
        $oldDb = $this->oldDb;
        $table = $this->table;
        $fieldsMap = ['`key`','type','value','ret','expire','rowVersion','rowLock'];
        $fieldsMapStr = implode(',' , $fieldsMap);
        $sql = <<<sql
INSERT INTO $newDb.$table($fieldsMapStr) SELECT $fieldsMapStr FROM $oldDb.$table
sql;
        $res = $this->insertSql($sql);
        if($this->checkRes($res))$this->output('同步成功...');
    }

    protected $start;
    protected $finish;
    protected $timeField;
    protected function setStartFinish($timeField , $start , $finish){
        $this->timeField = $timeField;
        $this->start = $start;
        $this->finish = $finish;
        return $this;
    }

    protected function sync_t_platform_mail(){
        $newDb = $this->newDb;
        $oldDb = $this->oldDb;
        $table = $this->table;
        $fields = ['oid' , 'userOid' , 'phone' , 'mailType' , 'mesType' , 'mesTitle' , 'mesContent'
            , 'isRead' , 'status' , 'requester' , 'approver' , 'approveRemark' , 'readUserNote', 'remark' ,
            'createTime' , 'updateTime'];
        $fieldsStr = implode(',' , $fields);
        $start = date('Y-m-d H:i:s' , strtotime($this->conf['start']));
        $finish = date('Y-m-d H:i:s' , strtotime($this->conf['finish']));
        $sql = <<<sql
INSERT INTO $newDb.$table($fieldsStr , rowVersion) SELECT $fieldsStr , 1 FROM $oldDb.$table WHERE createTime >= '$start' AND createTime < '$finish';
sql;
        $res = $this->insertSql($sql);
        if(!$this->checkRes($res))return;
        $this->output('同步成功...');

    }

    protected function sync_tb_coupon_0(){
        $newDb = $this->newDb;
        $oldDb = $this->oldDb;
        $table = $this->table;
        \Prj\Model\Flexible::reset('ZyDb' , $table);

        $fields = ['oid' , 'title' , 'description' , 'typeCode' , 'amount' , 'status' , 'rowVersion'
            ,'createTime' , 'count' , 'remainCount' , 'updateTime' , 'expire' , 'investAmount' , 'totalAmount'
            ,'remainAmount' , 'useCount' , 'labels'];
        $fieldsStr = implode(',' , $fields);
        \Prj\Model\Flexible::startTransaction();
        try{
            $sql = <<<sql
INSERT INTO $newDb.$table($fieldsStr) SELECT oid , `name` , description , upper(`type`) , upperAmount * 100 , `status` , 
1 , DATE_FORMAT(createTime,"%Y%m%d%H%i%s") , `count` , IFNULL(`count`,remainCount) , DATE_FORMAT(updateTime,"%Y%m%d%H%i%s") , 
disableDate , IFNULL(investAmount * 100 , 10000 ) , 9999999 , 9999999 , useCount , labels FROM (SELECT c1.* , c2.labels FROM $oldDb.t_coupon c1
LEFT JOIN $oldDb.tb_coupon_0 c2
ON c1.oid = c2.oid
where c2.oid is not NULL) t_coupon;
sql;
            $res = $this->insertSql($sql);
            if(!$this->checkRes($res))$this->fatalErr($res['message']);

            $list = \Prj\Model\Coupon::getRecords(null , []);

            foreach ((array) $list as $v){
                $coupon = \Prj\Model\Flexible::getCopy($v['oid']);
                $coupon->load();
                if(!$coupon->exists())$this->fatalErr($v['oid'] . ' 不存在的卡券oid');
                $coupon->setField('labels' , $this->transLabels($coupon->getField('labels')));
                $coupon->saveToDB();
                echo '.';
            }
            \Prj\Model\Flexible::commit();
        }catch (\Exception $e){
            \Prj\Model\Flexible::rollback();
            throw $e;
        }
        $this->output('同步成功...');
    }

    protected function sync_tb_activity_0(){

        $newDb = $this->newDb;
        $oldDb = $this->oldDb;
        $table = $this->table;
        \Prj\Model\Flexible::reset('ZyDb' , $table);
        try{
            \Prj\Model\Flexible::startTransaction();
            $sql = <<<sql
INSERT INTO $newDb.$table select jza.oid , gha.title , gha.title as description , gha.`status` , gha.active , jza.actCode , jza.coupons , 
jza.createTime , jza.startTime , jza.finishTime , jza.rules , jza.labels , jza.isDel , jza.rowVersion , jza.rowLock
FROM $oldDb.tb_activity_0 jza
LEFT JOIN $oldDb.t_event gha
ON jza.oid = gha.oid
sql;
            $res = $this->insertSql($sql);
            \Prj\Loger::out($sql);
            if(!$this->checkRes($res))$this->fatalErr($res['message']);
            $list = \Prj\Model\Flexible::getRecords(null , ['!labels' => '']);
            foreach ((array) $list as $v){
                $model = \Prj\Model\Flexible::getCopy($v['oid']);
                $model->load();
                if(!$model->exists())$this->fatalErr('活动不存在!!!');
                $model->setField('labels' , $this->transLabels($model->getField('labels')));
                $model->saveToDB();
                echo '.';
            }
            \Prj\Model\Flexible::commit();
        }catch (\Exception $e){
            \Prj\Model\Flexible::rollback();

            throw $e;
        }
        $this->output('同步成功...');
    }


    protected function checkNoKey($tableFrom , $tableTo = null){
        $tableTo = $tableTo ?: $tableFrom;
        \Prj\Model\Flexible::reset('User' , $tableFrom);
        $count1 = \Prj\Model\Flexible::getCount([ 1 => 1]);
        if(empty($count1))return $this->resultError('数据为空,不需要同步...');
        \Prj\Model\Flexible::reset('ZyDb' , $tableTo);
        $count2 = \Prj\Model\Flexible::getCount([ 1 => 1]);
        if($count1 > 0 && $count1 <= $count2)return $this->resultError('已经同步过...');
        return $this->resultOK();
    }

    protected function getLabelMap(){
        $list = \Prj\Model\MimosaLabel::getRecords(null , []);
        $data = [];
        foreach ((array)$list as $v){
            $data['oid'] = $v['labelCode'];
        }
        return $data;
    }

    protected function transLabels($labelsStr){
        if(empty($labelsStr))return '';
        $labels = explode(',' , $labelsStr);
        $oldDb = $this->oldDb;
        //$list = \Prj\Model\MimosaLabel::getRecords(null , ['oid' => $labels]);
        $labelsWhereStr = "(" . $labelsStr . ")";
        $labelsWhereStr = str_replace("(" , "('" , $labelsWhereStr);
        $labelsWhereStr = str_replace(")" , "')" , $labelsWhereStr);
        $labelsWhereStr = str_replace("," , "','" , $labelsWhereStr);
        $list = \Prj\Model\User::query("select * from $oldDb.t_money_platform_label where oid in $labelsWhereStr");
        if(count($labels) != count($list))$this->fatalErr('标签查询失败[0]');

        $labelCodeArr = [];
        foreach ($list as $v){
            $labelCodeArr[] = $v['labelCode'];
        }

        $newList = \Prj\Model\ZyBusiness\SystemLabel::getRecords(null , ['labelNo' => $labelCodeArr]);
        if(count($labels) != count($newList))$this->fatalErr('标签查询失败[1]');

        $labelIds = [];
        foreach ($newList as $v){
            $labelIds[] = $v['labelId'];
        }

        return implode(',' , $labelIds);
    }

    protected function insertSql($sql){
        try{
            $ret = \Prj\Model\User::query($sql);
        }catch (\Exception $e){
            if($e->getCode() == \Sooh2\DB\DBErr::duplicateKey){
                $this->output('已经同步过...');
                return $this->resultError('已经同步过...');
            }else{
                var_dump($e->getMessage());
                $this->output('同步异常!!!');
                return $this->resultError('同步异常!!!');
            }
        }
        if(!$ret)$this->resultError('同步失败!!!');
        return $this->resultOK();
    }

    protected function copy($tableFrom , $tableTo = null){
        $tableTo = $tableTo ?: $tableFrom;
        $newDb = $this->newDb;
        $oldDb = $this->oldDb;
        $sql = <<<sql
INSERT INTO $newDb.$tableTo SELECT * FROM $oldDb.$tableFrom
sql;
        if(!empty($this->timeField) && !empty($this->start) && !empty($this->finish)){
            $sql .= " WHERE $this->timeField >= '$this->start' AND $this->timeField < '$this->finish'";
            $this->timeField = $this->start = $this->finish = null;
        }
        return $this->insertSql($sql);
    }

    protected function output($str1 , $str2 = '' , $str3 = ''){
        \Prj\Loger::out($str1);
        if(is_array($str1))$str1 = json_encode($str1 , 256);
        if(is_array($str2))$str2 = json_encode($str2 , 256);
        if(is_array($str3))$str3 = json_encode($str3 , 256);
        echo $str1 . $str2 . $str3 . "\n";
    }

    protected function title($str){
        $this->output('--------------- '.$str.' 同步 ------------------');
    }

}