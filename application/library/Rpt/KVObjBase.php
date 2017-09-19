<?php
namespace Rpt;
/**
 * 管理后台这里的配置类，都使用此类（内置记录日志）
 *
 * @author simon.wang
 */
class KVObjBase extends \Sooh2\DB\KVObj{
    //put your code here
    public function saveToDB($func_update = null, $maxRetry = 3) {
        $traceChg = array();
        foreach($this->chged as $k){
            $traceChg[$k]=$this->r[$k];
        }
        $ret = parent::saveToDB($func_update, $maxRetry);
        $this->saveLog($this->className, '设置: '.\Sooh2\Util::toJsonSimple($traceChg));
        //error_log('更新配置：'. get_called_class().' with:'.\Sooh2\Util::toJsonSimple($traceChg));
        return $ret;
    }
    public function delete()
    {
        list($db,$tb) = $this->dbAndTbName();
        $db->delRecords($tb,$this->pkey());
        $this->saveLog($this->className,'删除: '.json_encode($this->pkey()));
        //error_log('删除配置：'. get_called_class().' with:'.\Sooh2\Util::toJsonSimple($this->r));
    }
    /**
     * 
     * @param string $kpey
     * @return \Rpt\KVObjBase
     */
    public static function base64EncodePkey($pkey)
    {
        return bin2hex(json_encode($pkey));
    }
    
    public static function getByBASE64($base64str)
    {
        $pkey = json_decode(hex2bin($base64str),true);
        return parent::getCopy($pkey);
    }
    protected $_tb_manage_log='tb_manage_log';
    protected function saveLog($obj,$content)
    {
        $dt = time();
        $fields = array(
            'ymd'=>date('Ymd',$dt),'his'=>date('His',$dt),'managerid'=> \Rpt\Session\Broker::getManagerId(),
            'objtable'=>$obj,'chgcontent'=>$content,'rowVersion'=>1
        );
        $db = $this->dbWithTablename();
        try{
            $db->addRecord($this->_tb_manage_log, $fields);
        } catch (\Sooh2\DB\DBErr $ex) {
            if($ex->getCode()==\Sooh2\DB\DBErr::tableNotExists){
                $db->exec(array('CREATE TABLE `tb_manage_log` (
  `ymd` int(11) DEFAULT NULL COMMENT \'年月日\',
  `his` int(11) DEFAULT NULL COMMENT \'小时分钟秒\',
  `managerid` varchar(36) DEFAULT NULL COMMENT \'管理员\',
  `objtable` varchar(36) DEFAULT NULL COMMENT \'改的哪张表\',
  `chgcontent` varchar(2000) DEFAULT NULL COMMENT \'更改内容\',
  `rowVersion` int(11) DEFAULT NULL
) '));
                try{
                    $db->addRecord($this->_tb_manage_log, $fields);
                } catch (\ErrorException $ex){
                    \Sooh2\Misc\Loger::getInstance()->app_warning('记录管理员操作记录失败：'.\Sooh2\Util::toJsonSimple($fields));
                }
            }else{
                \Sooh2\Misc\Loger::getInstance()->app_warning('记录管理员操作记录失败：'.\Sooh2\Util::toJsonSimple($fields));
            }
        } catch (\ErrorException $ex){
            \Sooh2\Misc\Loger::getInstance()->app_warning('记录管理员操作记录失败：'.\Sooh2\Util::toJsonSimple($fields));
        }
    }
}
