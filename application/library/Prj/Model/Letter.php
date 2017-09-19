<?php
namespace Prj\Model;

/**
 * 站内信（主从模式）
 *
 * @author simon.wang
 */
use \Sooh2\Accounts\UserDsk;

class Letter extends \Sooh2\DB\KVObj\KVObjRW{
    public static $types=array(
        'cash'      =>array('name'=>'回款','where'=>array('mesTitle'=>array('回款提醒','提前还款提醒'))),
        'deposit'   =>array('name'=>'充值','where'=>array('mesTitle'=>array('充值成功提醒'))),
        'invest'    =>array('name'=>'投资','where'=>array('mesTitle'=>array('投资成功提醒','流标提醒','计息提醒'))),
        'notice'    =>array('name'=>'通知','where'=>array('mesTitle'=>array('通知'))),
        'redpacket' =>array('name'=>'红包','where'=>array('*mesTitle'=>'%红包%')),
        'withdraw'  =>array('name'=>'提现','where'=>array('mesTitle'=>array('提现申请提醒','提现到账提醒'))),
    );
    /**
     * 
     * @param type $userid
     * @return \Prj\Model\Letter
     */
    public static function getCopy($userid)
    {
        if(empty($userid)){
            return \Sooh2\Accounts\UserDsk::getCopy(null);
        }else{
            return parent::getCopy(array('userId'=>$userid));
        }
    }

    protected function onInit()
    {
        $this->needTransData = false;//主从模式
        $this->_reader = \Prj\Model\RW\LetterReader::getCopy(current($this->_pkey));
        $this->_writer = \Prj\Model\RW\LetterWriter::getCopy(current($this->_pkey));
    }

    /**
     * 获取指定用户未读信件的数量
     * @param type $userOid
     * @return \Sooh2\DB\Interfaces\DB
     */
    public static function getNumOfUnread($userOid)
    {
        $db = \Prj\Model\RW\LetterReader::getCopy(null)->dbWithTablename(0,true);

        return $db->getRecordCount($db->kvobjTable(), array('userOid'=>$userOid,'!isRead'=>'is'));
    }
    /**
     * 全部标记已读
     * @param type $userOid
     */
    public static function markAllRead($userOid)
    {
        $db = \Prj\Model\RW\LetterWriter::getCopy([$userOid])->dbWithTablename(0,false);
        return $db->updRecords($db->kvobjTable(),array('isRead'=>'is'), array('userOid'=>$userOid,'!isRead'=>'is'));
    }
    
    public static function addNew($users,$title,$content,$typeCode)
    {
        if(!is_array($users)){
            $users= array($users);
        }
        $retok = 0;
        foreach($users as $user){
            
            $userobj = \Prj\Model\User::getCopy($user);
            $userobj->load();
            if(!$userobj->exists()){
                throw new \ErrorException('userid error');
            }

            $fields = array(
                //'oid'=>'',
                'userOid'=>$user,
                'phone'=>$userobj->getField('userAcc'),
                'mailType'=>'person',
                'mesType'=>'sysyem',
                'mesTitle'=>$title,
                'mesContent'=>$content,
                'isRead'=>'no',
                'status'=>'pass',
                'requester'=>'system',
                'approver'=>'system',
                'approveRemark'=>'',
                'readUserNote'=>'',
                'remark'=>'',
                'createTime'=>date('Y-m-d H:i:s'),
                'updateTime'=>date('Y-m-d H:i:s'),
                'rowVersion' => 0,
                'typeCode'  =>  $typeCode
            );
            $retry = 3;
            while($retry){
                $retry --;
                
                $newid = md5(microtime(true).rand(10000,99999));
                $tmp = self::getCopy(array('oid'=>$newid));
                $tmp->load();
                if($tmp->exists()){
                    continue;
                }
                try{
                    foreach($fields as $k=>$v){
                        $tmp->setField($k,$v);
                    }
                    
                    $ret = $tmp->saveToDB();
                    if($ret){
                        $retok++;
                        $retry=0;
                    }
                } catch (\Sooh2\DB\DBErr $ex) {
                    
                }
                self::freeCopy($tmp);
            }
        }
        if($retok== sizeof($users)){
            return 'total '.$retok.' all ok';
        }else{
            throw new \ErrorException('total:'.sizeof($users).', '.$retok.' sent ok');
        }
        
    }
}
