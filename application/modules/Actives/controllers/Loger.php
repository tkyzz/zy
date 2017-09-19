<?php
/**
 * 安装部署时，数据库会尝试找DB.log,找不到就用DB.mysql，要求：存在数据库db_log,并且用户有建表的权限
 *
 * @author simon.wang
 */
class LogerController extends \Prj\Framework\Ctrl {
    //GET ?deviceId=md5%3Ab6e5a3873756a82035dec52c1d847127
    //&userId=ff8080815a8451f4015a89d2f83e0008&isLogined=1&opcount=37
    //&clientType=3&clientVer=1.11.0&contractId=500020170328300000&evt=recharge
    //&dt=1500012195000&ip=116.231.217.247&sarg1=DeviceUid%3A45BEC9C0-CFFF-49F3-AEFB-E2F57A1627DB

    public function writeAction()
    {
        \Sooh2\Misc\Loger::getInstance()->traceLevel(0);
        $fields=array(
            'deviceId'=>$this->_request->get('deviceId',''),
            'userId'=>$this->_request->get('userId',''),
            'isLogined'=>$this->_request->get('isLogined')-0,
            'opcount'=>$this->_request->get('opcount')-0,
            'clientType'=>$this->_request->get('clientType')-0,
            'clientVer'=>$this->_request->get('clientVer',''),
            'contractId'=>$this->_request->get('contractId',''),
            
            'mainType'=>$this->_request->get('mainType',''),
            'subType'=>$this->_request->get('subType',''),
            'target'=>$this->_request->get('target',''),
            'num'=>$this->_request->get('num')-0,
            'evt'=>$this->_request->get('evt',''),
            'ext'=>$this->_request->get('ext',''),
            'ret'=>$this->_request->get('ret',''),
            'dt'=> time(),
            'ip'=> \Sooh2\Util::remoteIP(),
            '_scr_'=>$this->_request->get('_scr_',''),
            '_ua_'=>$this->_request->get('_ua_',''),
            '_url_'=>$this->_request->get('_url_',''),
            '_refer_'=>$this->_request->get('_refer_',''),
            'reqdur'=>$this->_request->get('reqdur')-0,
            'sarg1'=>$this->_request->get('sarg1',''),
            'sarg2'=>$this->_request->get('deviceInfo',''),
            'sarg3'=>$this->_request->get('sarg3',''),
            'narg1'=>$this->_request->get('narg1')-0,
            'narg2'=>$this->_request->get('narg2')-0,
            'narg3'=>$this->_request->get('narg3')-0,
        );
        if(strlen($fields['ret'])>490){
            $fields['ret'] = substr($fields['ret'],0,490);
        }
        if(strlen($fields['ext'])>490){
            $fields['ext'] = substr($fields['ext'],0,490);
        }
        $conf = \Sooh2\Misc\Ini::getInstance()->getIni('DB');
        if(isset($conf['log'])){
            $db = \Sooh2\DB::getConnection($conf['log']);
        }elseif(isset($conf['dblog'])){
            $db = \Sooh2\DB::getConnection($conf['dblog']);
        }else{
            $db = \Sooh2\DB::getConnection($conf['mysql']);
        }
        $tb = 'db_log.tb_log'.date('Ym');
        try{
            $db->addRecord($tb, $fields);
        }catch(\Sooh2\DB\DBErr $ex){
            if($ex->getCode()== \Sooh2\DB\DBErr::tableNotExists){
                try{
                    $db->exec(array('create table '.$tb.' '.$this->sqlCreate_part));
                    $db->addRecord($tb, $fields);
                } catch (\ErrorException $e){
                    $this->onError($e, $fields);
                }
            }else{
                $this->onError($ex, $fields);
            }
        }catch(\ErrorException $e){
            $this->onError($e, $fields);
        }

        
        $db = \Sooh2\DB::getConnection($conf['mysql']);
        $tb = 'jz_db.tb_device_contractid_0';
        \Sooh2\Misc\Loger::getInstance()->app_trace('use kvobj for tb_device_contractid_0 instead');
//        list($type,$id) = explode(':', str_replace('%3A', ':', $fields['sarg1']));
        $type = array_keys($fields['sarg1'])[0];
        $id = array_values($fields['sarg1'])[0];
        //\Prj\Loger::outVal("dsds",$type);
        try{
            if(method_exists($db, 'skipErrorLog')){
                $db->skipErrorLog(\Sooh2\DB\DBErr::duplicateKey);
            }
            switch ($type){
                case 'idfa':
                case 'IDFA':
                case 'DeviceUid'://DeviceUid:D84F34F6-546A-4AEC-9579-36DB09903C0B
                    $db->addRecord($tb, array('deviceType'=>'idfa','deviceId'=>$id,'contractId'=>$fields['contractId']));
                    break;
                case 'imei':
                case 'IMEI':
                    $db->addRecord($tb, array('deviceType'=>'imei','deviceId'=>$id,'contractId'=>$fields['contractId']));
                    break;
            }
        }catch(\Sooh2\DB\DBErr $ex){
            if(empty($ex->keyDuplicated)){
                \Sooh2\Misc\Loger::getInstance()->app_warning($ex->getMessage()."#".$ex->getTraceAsString());
            }
        } catch (\ErrorException $ex){
            \Sooh2\Misc\Loger::getInstance()->app_warning($ex->getMessage()."#".$ex->getTraceAsString());
        }
        $this->assignCodeAndMessage('done');
    }
    protected function onError($e,$fields)
    {
        error_log($e->getMessage()."\n".json_encode($fields)."\n".$e->getTraceAsString());
    }
    protected $sqlCreate_part = '(
  logid bigint(20) NOT NULL AUTO_INCREMENT,
  deviceId varchar(64) NOT NULL DEFAULT \'\',
  userId varchar(64) NOT NULL DEFAULT \'\',
  isLogined int(11) NOT NULL DEFAULT \'0\',
  opcount int(11) NOT NULL DEFAULT \'0\',
  clientType int(11) NOT NULL DEFAULT \'0\',
  clientVer varchar(64) NOT NULL DEFAULT \'\',
  contractId varchar(64) NOT NULL DEFAULT \'\',
  evt varchar(64) NOT NULL DEFAULT \'\',
  mainType varchar(64) NOT NULL DEFAULT \'\',
  subType varchar(64) NOT NULL DEFAULT \'\',
  target varchar(64) NOT NULL DEFAULT \'\',
  num int(11) NOT NULL DEFAULT \'0\',
  ext varchar(500) NOT NULL DEFAULT \'\',
  ret varchar(500) NOT NULL DEFAULT \'\',
  ip varchar(64) NOT NULL DEFAULT \'\',
  dt bigint(20) NOT NULL DEFAULT \'0\',
  _scr_ varchar(64) NOT NULL DEFAULT \'\',
  _ua_ varchar(500) NOT NULL DEFAULT \'\',
  _url_ varchar(500) NOT NULL DEFAULT \'\',
  _refer_ varchar(500) NOT NULL DEFAULT \'\',
  reqdur bigint(20) NOT NULL DEFAULT \'0\',
  sarg1 varchar(2000) NOT NULL DEFAULT \'\',
  sarg2 varchar(2000) NOT NULL DEFAULT \'\',
  sarg3 varchar(2000) NOT NULL DEFAULT \'\',
  narg1 int(11) NOT NULL DEFAULT \'0\',
  narg2 int(11) NOT NULL DEFAULT \'0\',
  narg3 int(11) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (logid)
) ENGINE=MyISAM AUTO_INCREMENT=2266189 DEFAULT CHARSET=utf8';



    /**
     * @SWG\Post(path="/actives/Loger/sendWarningLog", tags={"Loger"},
     *   summary="发送警报信息",
     *
     *      @SWG\Parameter(name="content", type="string", in="formData",
     *     description="警报内容"   ),
     *     @SWG\Parameter(name="type", type="string", in="formData",
     *     description="发生警报来源，字符串，多个来源平台用逗号隔开： (app和server)平台"   ),
     *     @SWG\Response(response=200, description="successful operation"),
     *     @SWG\Schema(type="object",
     *              @SWG\Property(property="code", description="状态码" , type="string"),
     *              @SWG\Property(property="message", description="返回信息", type="string")
     *             ),
     *
     *
     * )
     */
    public function sendWarningLogAction(){
//        $key = "d511ddss414545413";
        $deviceInfo = $this->_request->get("deviceInfo");
//        $sign = $this->_request->get("sign");
//        \Prj\Loger::outVal("sign",$sign);
//        $reqTime = $this->_request->get("reqTime");
//        if(md5($key.$reqTime)!=$sign) return $this->assignCodeAndMessage("sign不匹配",99999);
//        $phpTime = substr($reqTime,0,10);
//        if(abs($phpTime-time())>10) return $this->assignCodeAndMessage("时间已过期！",99999);
        $deviceInfo = json_encode($deviceInfo);
        $content = $this->_request->get("content");
//        if(empty($deviceInfo)) return $this->assignCodeAndMessage("设备信息不能为空！",99999);
        if(empty($content)) return $this->assignCodeAndMessage("警报内容不能为空！",99999);
        $type = $this->_request->get("type");
        if(empty($type)) return $this->assignCodeAndMessage("来源类型不能为空",99999);
        $obj  = \Prj\Model\WarningLog::getCopy(null);
        $obj->setField("deviceInfo",$deviceInfo?$deviceInfo:'无');
        $obj->setField("warningContent",$content);
        $obj->setField("source",$type);
        $obj->setField("status",0);
        $obj->setField("createTime",date("Y-m-d H:i:s"));
        try{
            $ret = $obj->saveToDB();
            if($ret) return $this->assignRes();
            return $this->assignCodeAndMessage("上传警报信息失败！");
        }catch (Exception $ex){
            return $this->assignCodeAndMessage("上传警报信息失败！".$ex->getMessage());
        }

    }
}
