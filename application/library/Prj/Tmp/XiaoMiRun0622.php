<?php
namespace Prj\Tmp;
use Lib\Misc\Result;
use Prj\Bll\Product;
use Prj\Loger;

/**
 * Description of XiaoMiRun0622
 *
 * @author simon.wang
 */
class XiaoMiRun0622 {
    /**
     * 
     * @param type $phone
     * @return string 手机号未注册 or 验证码已经发送 等信息
     */
    public function sendvc($phone)
    {
        $u = \Prj\Model\User::getCopyByPhone($phone);
        $db = $u->dbWithTablename();
        $tb = trim($db->kvobjTable()).'_tmp';
        
        $db->exec(array('create table if not exists '.$tb
                .' (phone varchar(64), vcode varchar(16), realname varchar(100), tel varchar(16), addr varchar(500), primary key (phone))'));
        $u->load();
        if($u->exists()){
            //用户来源的检查
            $checkUserRes = $this->checkU($u->dump());
            if(!Result::check($checkUserRes)){
                return $checkUserRes['message'];
            }

            $vcode = rand(100000,999999);
            if(\Prj\Tool\Debug::isTestEnv()){
                $vcode = 111111;
            }
            //检查验证码次数
            if(!$this->checkPhoneTotal($phone)){
                return '今日短信次数超限，请明天再试~';
            }

            try{
                $db->addRecord($tb, array('phone'=>$phone,'vcode'=>$vcode));
            } catch (\Sooh2\DB\DBErr $ex) {
                if($ex->keyDuplicated){
                    \Prj\Loger::out('这里的主键重复经过处理...');
                    $db->updRecords($tb, array('vcode'=>$vcode),array('phone'=>$phone));
                }else{
                    return "短信系统故障，请联系客服";
                }
            }

            \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('短信验证码',
                        '验证码：'. $vcode .'，请在2分钟内填写，注意保密哦！',
                        $u->getField('oid'), array('smsnotice'), 'xiaomirunchk');
            return true;
        }else{
            \Prj\Loger::out('该用户未注册!!!');
            return "仅限参与活动的用户参加~";
        }
    }

    protected function checkU($userInfo = []){
        return \Prj\Bll\MiActivy::getInstance()->checkUser($userInfo);
    }

    /**
     * 验证码一次性有效
     * @param type $phone
     * @param type $code
     * @return mix null 验证码错误， array(addr=>adf,'tel'=>asf,'realname'=>asf),验证码正确
     */
    public function checkvc($phone,$code){
        $u = \Prj\Model\User::getCopyByPhone($phone);
        $db = $u->dbWithTablename();
        $tb = trim($db->kvobjTable()).'_tmp';
        
        $db->exec(array('create table if not exists '.$tb
                .' (phone varchar(64), vcode varchar(16), realname varchar(100), tel varchar(16), addr varchar(500), primary key (phone))'));
        $u->load();
        if($u->exists()){
            Loger::out('登录用户 phone: '.$phone.' uid: '.$u->getField('oid'));
            $r = $db->getRecord($tb,'*',array('phone'=>$phone));
            if(!empty($r)){
                if( $r['vcode']==$code ){
                    return array('addr'=>$r['addr'],'tel'=>$r['tel'],'realname'=>$r['realname'],'userId' => $u->getField('oid'));
                }else{//验证失败，改掉验证码
                    $db->updRecords($tb,array('vcode'=>\Lib\Misc\StringH::randStr(6)),array('phone'=>$phone));
                    return Null;
                }
            }else{
                return null;
            }
        }else{
            return null;
        }
    }
    /**
     * 设置联系方式
     * @return boolean 是否成功
     */
    public function setinfo($phone,$code,$addr,$tel,$realname){
        $u = \Prj\Model\User::getCopyByPhone($phone);
        $db = $u->dbWithTablename();
        $tb = trim($db->kvobjTable()).'_tmp';
        
        $db->exec(array('create table if not exists '.$tb
                .' (phone varchar(64), vcode varchar(16), realname varchar(100), tel varchar(16), addr varchar(500), primary key (phone))'));
        $u->load();
        if($u->exists()){

            $r = $db->getRecord($tb,'*',array('phone'=>$phone));
            if(!empty($r)){
                if( $r['vcode']==$code ){//验证通过记录信息
                    $db->updRecords($tb,array('addr'=>$addr,'tel'=>$tel,'realname'=>$realname),array('phone'=>$phone));
                    return true;
                }else{//验证失败，改掉验证码
                    $db->updRecords($tb,array('vcode'=>100),array('phone'=>$phone));
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    protected function _getCodeIpTb($db){
        return trim($db->kvobjTable()).'_code_ip_tmp';
    }
    /**
     * 每个IP的每日验证码上限为9999条
     * @return bool
     */
    public function checkIp(){
        $ip = \Sooh2\Util::remoteIP();
        if(empty($ip))return true;
        $u = \Prj\Model\User::getCopyByPhone($ip);
        $db = $u->dbWithTablename();
        $tb = $this->_getCodeIpTb($db);
        $sql = <<<tmp
            CREATE TABLE if not exists $tb (
            `ip`  varchar(20) NOT NULL ,
            `ymd`  bigint NOT NULL ,
            `num`  int NOT NULL DEFAULT 0 ,
            PRIMARY KEY (`ip`, `ymd`)
)
;
tmp;
        $db->exec([$sql]);
        $ymd = date('Ymd');
        $data = $db->getRecord($tb ,'*', ['ip' => $ip , 'ymd' => $ymd]);
        if(empty($data)){
            $db->addRecord($tb , [
                'ip' => $ip,
                'ymd' => $ymd,
                'num' => 1,
            ]);
            return true;
        }
        if($data['num'] > 9999){
            return false;
        }

        $ret = $db->updRecords($tb , [
            'ip' => $ip,
            'ymd' => $ymd,
            'num' => ++ $data['num'],
        ] , ['ip' => $ip , 'ymd' => $ymd]);
        \Prj\Loger::out('ip: '.$ip.' num: '.$data['num']);

        return true;
    }

    public function checkPhoneTotal($phone){
        $u = \Prj\Model\User::getCopyByPhone($phone);
        $db = $u->dbWithTablename();
        $tb = $this->_getCodeIpTb($db);
        $sql = <<<tmp
            CREATE TABLE if not exists $tb (
            `ip`  varchar(20) NOT NULL ,
            `ymd`  bigint NOT NULL ,
            `num`  int NOT NULL DEFAULT 0 ,
            PRIMARY KEY (`ip`, `ymd`)
)
;
tmp;
        $db->exec([$sql]);
        $ymd = date('Ymd');
        $data = $db->getRecord($tb ,'*', ['ip' => $phone , 'ymd' => $ymd]);
        if(empty($data)){
            $db->addRecord($tb , [
                'ip' => $phone,
                'ymd' => $ymd,
                'num' => 1,
            ]);
            return true;
        }
        if($data['num'] > 100){
            return false;
        }

        $ret = $db->updRecords($tb , [
            'ip' => $phone,
            'ymd' => $ymd,
            'num' => ++ $data['num'],
        ] , ['ip' => $phone , 'ymd' => $ymd]);
        \Prj\Loger::out('ip: '.$phone.' num: '.$data['num']);

        return true;
    }
}
