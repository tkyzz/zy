<?php
namespace Libs\GuoHuai;
/**
 * 国槐tulip系统中uc.user读写封装类
 */
class GHAccount extends \Sooh2\DB\KVObj
{
    protected function onInit()
    {
        //$this->className = 'UserDsk';
        
        parent::onInit();
        $this->field_locker='rowLock';//  悲观锁用的字段名，默认使用'rowLock'，设置为null表明不需要悲观锁
        $this->_tbName = 't_wft_user';//表名的默认模板
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
     * 获取user
     * @param string $userId
     * @return \Libs\GuoHuai\GHAccount
     */
    public static function getCopyByGHUcUid($userId)
    {
        if(empty($userId)){
            return parent::getCopy(null);
        }else{
            return parent::getCopy(array('oid'=>$userId));
        }
    }
    /**
     * 获取user
     * @param string $phone
     * @return \Libs\GuoHuai\GHAccount
     */
    public static function getCopyByPhone($phone)
    {
        if(empty($phone)){
            return parent::getCopy(null);
        }else{
            return parent::getCopy(array('userAcc'=>$phone));
        }
    }
    /**
     * 检查密码是否正确
     * @param string $pwdInput
     * @return boolean
     */
    public function checkpwd($pwdInput)
    {
        return $this->getField('userPwd')==bin2hex(sha1(hex2bin($this->getField('salt')).$pwdInput, true));   
    }
    /**
     * 创建一个新的用户，返回user类（如果userId冲突，连续尝试10次）
     * @param array $defaultVals 默认字段值列表
     * @param int $speIndex 是否指定splitIndex
     * @return \Sooh2\Accounts\UserDsk
     */
    public static function createNew($defaultVals=array())
    {

        while($retry>0){
            $retry--;
            if($speIndex<=99999){
                $userId = rand(10000,99999).$speIndex;
            }else{
                $userId = $speIndex;
            }
            $tmp = static::getCopy($userId);
            foreach($defaultVals as $k=>$v){
                $tmp->setField($k,$v);
            }
            try{
                $ret = $tmp->saveToDB();
                if($ret){
                    $tmp->_lock = \Sooh2\DB\LockInfo::factory('');
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
    /**
     * 获取用户Id(全部，或指定的ucoid,memberid,uid)
     * 
     */
    public function getAllKindsID($type=null)
    {

        switch ($type){
            case 'ucoid': return $this->getField('oid');
            case 'memberid':return $this->getField('memberOid');
            case 'uid':return $this->getField('oid');
            default:
                return array('ucoid'=>$this->getField('oid'),'memberid'=>$this->getField('memberOid'),'uid'=>$this->getField('oid'));
        }

    }
}

