<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/17
 * Time: 11:56
 */
/*黑名单*/
class CouponWhiteListController extends \Rpt\Manage\ManageIniCtrl
{
    public $showStatus = [0=>"否",1=>"是"];
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = Sooh2\HTML\Table::factory()
            ->addHeader('手机号','phone','250','')
            ->addHeader('是否不发送红包过期短信','expiredCoupon','300','')
            ->addHeader("操作","op",'250')
            ->initJsonDataUrl($uri->uri('','listData'));

        $page = Sooh2\BJUI\Pages\ListStd::getInstance()->init('消息发送')->initStdBtn($uri->uri('','pageAdd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }


    public function listDataAction(){
        $obj = \Prj\Model\CouponWhiteList::getCopy(null);
        list($db,$tb) = $obj->dbAndTbName();
        $res = $db->getRecords($tb,'*');
        foreach($res as $k => $v){
            $config = json_decode($v['whitelistJson'],true);
            $res[$k]['expiredCoupon'] = $this->showStatus[$config['expiredCoupon']];
            $res[$k]['op'] = $this->btnEdtInDatagrid(['wid'=>$v['wid']]);
//            $res[$k]['statusCode'] = "<p class='bg-primary'>".$this->feedbackStatus[$v['statusCode']]."</p>";
        }

        $this->renderArray($res);
    }



    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone','','手机号'))
        ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("expiredCoupon",0,"是否不发送红包过期短信")->initOptions($this->showStatus));

        if($form->isUserRequest($this->_request)){
            $phone = $this->_request->get("phone");

            $config['expiredCoupon'] = $this->_request->get("expiredCoupon");
            $uid = $this->getUidByPhone($phone);
            if(empty($uid)) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'');

            try{
//                $flag = true;
//                foreach($contents as $k => $v){
//
//                    if(empty($v)) continue;
//                    $obj = \Rpt\Manage\HandMail::getCopy(null);
//                    $arr = explode("\t",$v);
//                    $arr = array_filter($arr);
//                    $obj->setField('phone',trim(array_shift($arr)));
//                    $obj->setField('title',trim(array_shift($arr)));
//                    $obj->setField('content',trim(array_shift($arr)));
//                    $obj->setField('approach',trim(array_shift($arr)));
//                    $obj->setField('statusCode',0);
//                    $ret = $obj->saveToDB();
//                    if(!$ret) $flag=false;
//                }
                $obj = \Prj\Model\CouponWhiteList::getCopy(null);
                $obj->setField("phone",$phone);
                $obj->setField("whitelistJson",json_encode($config));
                $obj->setField("uid",$uid);
                $obj->setField("createTime",date('Y-m-d H:i:s'));
                $obj->setField("updateTime",date("Y-m-d H:i:s"));
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'成功添加白名单成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'添加白名单失败！');
                }
            }catch (Exception $ex){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('消息发送')->initForm($form);
            $this->renderPage($page,true);
        }
    }



    public function pageupdAction(){
        $base64str = $this->_request->get("__pkey__");
        $pkey = json_decode(hex2bin($base64str),true);
        $obj = \Prj\Model\CouponWhiteList::getCopy($pkey);
        $obj->load();
        $con = $obj->getField("whitelistJson");
        \Prj\Loger::outVal("con1",$con['expiredCoupon']);
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->appendHiddenFirst("__pkey__",$base64str)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone',$obj->getField("phone"),'手机号'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("expiredCoupon",$con["expiredCoupon"],"是否不发送红包过期短信")->initOptions($this->showStatus));

        if($form->isUserRequest($this->_request)){
            $phone = $this->_request->get("phone");

            $config['expiredCoupon'] = $this->_request->get("expiredCoupon");
            $uid = $this->getUidByPhone($phone);
            if(empty($uid)) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'没此用户信息');

            try{

                $obj->setField("phone",$phone);
                $obj->setField("whitelistJson",json_encode($config));
                $obj->setField("uid",$uid);
                $obj->setField("createTime",date('Y-m-d H:i:s'));
                $obj->setField("updateTime",date("Y-m-d H:i:s"));
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'成功修改白名单',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'修改白名单失败！');
                }
            }catch (Exception $ex){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('白名单修改')->initForm($form);
            $this->renderPage($page);
        }
    }
}