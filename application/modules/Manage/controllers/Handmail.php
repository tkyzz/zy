<?php
/**
 * 发送站内信
 * User: amdin
 * Date: 2017/7/18
 * Time: 16:19
 */

class HandmailController extends \Rpt\Manage\ManageIniCtrl
{
    protected $status = [
        '0' =>  '未发送',
        '1' =>  '已发送'
    ];
    protected $approachType = [
        'msg'   =>  "站内信",
        'smsnotice' =>  "短信",
        'push'  =>  "推送"
    ];
    protected $apporach = ['push','smsnotice','smsmarket','msg'];
    public function indexAction(){

        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = Sooh2\HTML\Table::factory()
            ->addHeader('手机号','phone','250','')
            ->addHeader('标题','title','300','')
            ->addHeader('内容','content','350','')
            ->addHeader('状态','statusCode','250','')
            ->addHeader('发送通道','approach','250')
            ->initJsonDataUrl($uri->uri('','listData'));

        $page = Sooh2\BJUI\Pages\ListStd::getInstance()->init('消息发送')->initStdBtn($uri->uri('','pageAdd'),$uri->uri("","imdSend"))
            ->initDatagrid($table);

        $this->renderPage($page);


    }

    public function listDataAction(){
        $where = ['statusCode'=>0];
        $obj = \Rpt\Manage\HandMail::getCopy(null);
//        $obj = \Prj\Model\HandMail::getCopy(null);
        list($db,$tb) = $obj->dbAndTbName();
        $res = $db->getRecords($tb,'*',$where);
        foreach($res as $k => $v){
            $res[$k]['statusCode'] = $this->status[$v['statusCode']];
        }

        $this->renderArray($res);
    }


    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory('contents','','手动发站内信'));

        if($form->isUserRequest($this->_request)){
            $contents = $this->_request->get('contents');

            $contents = explode("\r\n",$contents);
            $ret = $this->checkDataInfo($contents);
            if(!$ret['status']){
                return $this->returnError($ret['msg']);

            }
            try{
                $flag = true;
                foreach($contents as $k => $v){

                    if(empty($v)) continue;
                    $obj = \Rpt\Manage\HandMail::getCopy(null);
                    $arr = explode("\t",$v);
                    $arr = array_filter($arr);
                    $obj->setField('phone',trim(array_shift($arr)));
                    $obj->setField('title',trim(array_shift($arr)));
                    $obj->setField('content',trim(array_shift($arr)));
                    $obj->setField('approach',trim(array_shift($arr)));
                    $obj->setField('statusCode',0);
                    $ret = $obj->saveToDB();
                    if(!$ret) $flag=false;
                }
                if($flag){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'成功导入消息成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'手动导入消息，请重新导入');
                }
            }catch (Exception $ex){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }




        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('消息发送')->initForm($form);
            $this->renderPage($page,true);
        }
    }


    public function checkDataInfo($info){

        foreach($info as $k => $v){
            if(empty($v)) continue;
            $v = explode("\t",$v);
            $v = array_filter($v);

            if(count($v) != 4) return array('status'=>0,"msg"=>"每条记录必须输入4个字段的非空值，且必须要以tab键分隔");
            $phone = array_shift($v);
            $title = array_shift($v);
            $content = array_shift($v);
            $approach = array_shift($v);
            if(!preg_match("/^1[34578]{1}\d{9}$/",$phone)) return array('status'=>0,'msg'=>"手机号".$phone."格式输入错误");

            $userid = \Prj\Model\User::getRecord('memberOid', array('UserAcc' => $phone))['memberOid'];
            if(empty($userid)) {
                \Prj\Loger::out("未找到手机号为：".$phone." 此用户信息");
                return array('status'=>0,"msg"=>"未找到手机号为".$phone."的用户信息");
            }
            if(empty($approach)) {
                \Prj\Loger::out("手机号为".$phone."的用户发送通道为空");
                return array('status'=>0,"msg"=>"手机号为".$v[0]."的用户发送通道为空");

            }


            if(empty($title)) {
                \Prj\Loger::out("手机号为".$phone."的标题不能为空");
                return array('status'=>0,"msg"=>"手机号为".$phone."的标题不能为空");


            }
            if(empty($content)) {
                \Prj\Loger::out("手机号为".$phone."的内容不能为空");
                return array('status'=>0,"msg"=>"手机号为".$phone."的内容不能为空");
            }

            $approach = explode("|",$approach);

            foreach($approach as $k1 => $v1){
                if(!in_array($v1,$this->apporach)) {
                    \Prj\Loger::out("手机号为".$v['phone']."的用户发送通道名为".$this->apporach."为错误格式");
                    return array('status'=>0,"msg"=>"手机号为".$phone."的用户发送通道名为".$this->apporach."为错误格式");

                }
            }

        }

        return array("status"=>1,'msg'=>"验证成功");
    }


    public function imdSendAction(){

        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone','','手机号')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('title','','标题'))
        ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("content","","内容"))
        ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory("approach","","渠道")->initOptions($this->approachType));
        if($form->isUserRequest($this->_request)){
            $inputs = $form->getInputs();
            $uid = $this->getUidByPhone($inputs['phone']);
            \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($inputs['title'],$inputs['content'],array($uid),$inputs['approach']);
            $obj = \Rpt\Manage\HandMail::getCopy(null);
            foreach ($inputs as $k =>$v){
                if($v == "approach") $v = implode(",",$v);
                $obj->setField($k,$v);
            }
            $obj->setField("statusCode",1);
            $ret = $obj->saveToDB();
            if($ret){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'成功发送成功',true);
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'发送失败');
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('立即发送')->initForm($form);
            $this->renderPage($page,true);
        }
    }





}
