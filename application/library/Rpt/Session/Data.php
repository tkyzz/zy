<?php
namespace Rpt\Session;
/**
 * 管理后台的session data, 先不考虑需要锁的情景
 *
 * @author simon.wang
 */
class Data extends \Sooh2\DB\KVObj{

    const TIME_OUT = 3600;

    public static function sqlCreate($db,$tbname = 'tb_manager_session_{i}')
    {
        $sqls =array();
        $sqls[] = str_replace('{tbname}', $tbname, 
"CREATE TABLE `tb_manager_session_0` (
  `sessionId` varchar(36) NOT NULL DEFAULT '0',
  `userId` varchar(36) NOT NULL DEFAULT '',
  `sessionData` varchar(500) NOT NULL DEFAULT '0',
  `dtCreate` bigint(20) NOT NULL DEFAULT '0',
  `dtUpdate` bigint(20) NOT NULL DEFAULT '0',
  `durExpire` int(11) NOT NULL DEFAULT '0',
  `dtExpire` bigint(20) NOT NULL DEFAULT '0',
  `rowVersion` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sessionId`),
  KEY `dtExpire` (`dtExpire`)
)"
            );

            //todo: alter table

        
        return str_replace('{tbname}',$tbname, $sqls[0]);
    }
    protected function onInit()
    {
        $this->className = 'ManagerSession';
        
        parent::onInit();
        error_log('todo-todo-todo: session expire');
        $this->field_locker=null;//  悲观锁用的字段名，默认使用'rowLock'，设置为null表明不需要悲观锁
        $this->_tbName = 'tb_manager_session_{i}';//表名的默认模板
    }
    //     /**
    //      * 针对主键是一个数字串的情况使用取余的计算方式，默认取尾数，这里可以改成使用开头部分
    //      * 设置比较长度，改100000，userid用100亿，取前几位而不是末几位，流水用后面的数字递增
    //      * @param string $n
    //      */
    //     protected static function calcPkeyValOfNumber($n)
    //     {
    //         return substr(sprintf('%010d',$n),0,-4)-0;
    //     }
    /**
     * 获取 ManagerSessData
     * @param int $sessionId
     * @return \Rpt\Session\Data
     */
    public static function getCopy($sessionId)
    {
        if(empty($sessionId)){
            return null;
        }else{
            return parent::getCopy(array('sessionId'=>$sessionId));
        }
    }
    /**
     * 创建一个新的session（如果Id冲突，连续尝试10次）
     * @param string $userId 用户id
     * @param int $durExpired 有效期（单位秒）,多少时间内不操作就超时
     * @param array $defaultVals 其它的数据
     * @return \Rpt\ManagerSessData
     */
    public static function createNew($userId,$durExpired=self::TIME_OUT,$defaultVals=null)
    {
        $retry = 10;
        while($retry>0){
            $retry--;
            $sessionId = md5(microtime(true).rand(100000,999999));
            $tmp = static::getCopy($sessionId);
            $tmp->setField('userId',$userId);
            $tmp->setField('sessionData',$defaultVals?:'');
            $tmp->setField('durExpire',$durExpired);
            $tmp->setField('dtCreate', time());

            try{
                $ret = $tmp->saveToDB();
                if($ret){
                    return $tmp;
                }
            }catch(\Sooh2\DB\DBErr $e){
                if($e->keyDuplicated){
                    static::freeCopy($tmp);
                }else{
                    throw $e;
                }
            }
        }
        return null;
    }

    public function saveToDB($func_update = null, $maxRetry = 3) {
        $this->setField('dtUpdate',time());
        $this->setField('dtExpire',time()+$this->getField('durExpire'));
        return parent::saveToDB($func_update, $maxRetry);
    }
    
    public function load($forceReload = false) {
        $ret = parent::load($forceReload);
        if($this->exists()){
            $dur = time() - $this->getField('dtUpdate');
            if($dur>$this->getField('durExpire')){//已经超时
                $this->setField('userId', '0');
                $this->setField('durExpire', self::TIME_OUT);
                $this->saveToDB();
            }elseif($dur>$this->getField('durExpire')/2){//没超时，但过了一半的时间，更新一下时间
                $this->saveToDB();
            }
        }
        return $ret;
    }
}

