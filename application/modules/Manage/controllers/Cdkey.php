<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/8/21
 * Time: 19:29
 */

class CdkeyController extends \Rpt\Manage\ManageIniCtrl
{

    protected $status = ['0'=>'停用','1'=>'启用'];
    protected $couponType = ['COUPON'=>'优惠券'];
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
//            ->addHeader('ID', 'oid', 200, '')
//            ->addHeader('cdkID', 'cdkID', 200, '')
//            ->addHeader('couponId', 'couponId', 200, '')
            ->addHeader('兑换码名称', 'name', 160, '')
            ->addHeader('券名称', 'title', 160, '')
            ->addHeader('奖励类型', 'typeCode', 160, '')
            ->addHeader('总量', 'count', 160, '')
            ->addHeader('领取数量', 'getCount', 160, '')
            ->addHeader('关键字', 'words', 160, '')
            ->addHeader('开始时间', 'start', 160, '')
            ->addHeader('截止时间', 'finish', 160, '')
            ->addHeader('状态', 'statusCode', 80, '')
            ->addHeader('创建时间', 'createTime', 160, '')
            ->addHeader('操作', 'op', 140, '')
            ->initJsonDataUrl($uri->uri(null, 'listdata'));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('兑换码管理')->initStdBtn($uri->uri(null, 'pageadd'))
            ->initDatagrid($table);
        $this->renderPage($page);
    }

    public function listdataAction(){
        $data = \Prj\Model\CdkeyAward::getAwardList();
//        print_r($data);
        if( !empty($data) ){
            foreach ($data as $k => $v) {
                $data[$k]['count'] = $data[$k]['count'] ? $data[$k]['count'] : '无限';
                $data[$k]['op'] = $this->btnEdtInDatagrid(['oid'=>$v['cdkId']]) .'  ';
                if( $data[$k]['statusCode'] ){
                    $data[$k]['op'] .= $this->btnAjax(['oid' => $v['cdkId']] , 'disable' , '停用' ,
                        '确定停用吗？');
                }else{
                    $data[$k]['op'] .= $this->btnAjax(['oid' => $v['cdkId']] , 'enable' , '启用' ,
                        '确定启用吗？');
                }
                $data[$k]['statusCode'] = $this->status[$data[$k]['statusCode']];

            }
        }
        $this->renderArray($data);
    }

    public function pageAddAction(){
        $couponList = \Prj\Model\Coupon::getRecords('oid,title',['typeCode'=>'coupon','status'=>'yes']);
        //券列表
        foreach($couponList as $k => $v){
            $coupon[$v['oid']] = $v['title'];
        }
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("name","","名称")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('coupon'," ",'券码')->initOptions($coupon)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('typeCode'," ",'兑换码类型')->initOptions($this->couponType)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("count","0","兑换码总量")->initChecker(new \Sooh2\Valid\Str(true,0)))
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("getCount","","领取数量")->initChecker(new \Sooh2\Valid\Str(true,1)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("words","","兑换口令"))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("start","","开始时间")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("finish","","截止时间")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("statusCode","","状态")->initOptions($this->status))
        ;
        if($form->isUserRequest($this->_request)){

            //获取表单数据
            $inputs = $form->getInputs();
            if( empty($inputs['start'])  ){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '开始时间必选');
            }
            if( empty($inputs['finish'])  ){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '截止时间必选');
            }
            if( strtotime($inputs['start']) > strtotime($inputs['finish']) ){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '开始时间不能大于截止时间');
            }
//            if( $inputs['count'] < $inputs['getCount'] && $inputs['count'] !=0){
//                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '领取数量不得大于总量');
//            }

            $inputs['statusCode'] = $inputs['statusCode'] ? 1:0;
            //日志
            \Prj\Loger::outVal("inputs-",$inputs);
            try{
                //开启事务
                \Prj\Model\Cdkey::startTransaction();
                //入库
                $cdkeyRet = \Prj\Model\Cdkey::insertOne($inputs);
                $inputs['cdkeyId'] = $cdkeyRet;
                $cdkeyAwardRet = \Prj\Model\CdkeyAward::insertOne($inputs);
                if($cdkeyRet && $cdkeyAwardRet){
                    //事务提交
                    \Prj\Model\Cdkey::commit();
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '添加成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败');
                }
            } catch (Exception $ex) {
                //事务回滚
                \Prj\Model\Cdkey::rollback();
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("新增兑换码");
            $page->initForm($form);
            $this->renderPage($page,true);
        }

//        print_r($couponList);
    }
    //修改
    public function pageupdAction(){
        $strpkey = $this->_request->get('__pkey__');
        $pkey = $this->getPkey();
//        print_r($pkey);
        $couponList = \Prj\Model\Coupon::getRecords('oid,title',['typeCode'=>'coupon','status'=>'yes']);
        //券列表
        foreach($couponList as $k => $v){
            $coupon[$v['oid']] = $v['title'];
        }
        $data = \Prj\Model\CdkeyAward::getAwardDetail($pkey['oid'])[0];
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("name",$data['name'],"名称")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('coupon',$data['couponId'],'券码')->initOptions($coupon)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('typeCode',$data['typeCode'],'兑换码类型')->initOptions($this->couponType)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("count",$data['count'],"兑换码总量")->initChecker(new \Sooh2\Valid\Str(true)))
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("getCount",$data['gerCount'],"领取数量",'readonly'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("words",$data['words'],"兑换口令"))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("start",$data['start'],"开始时间")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory("finish",$data['finish'],"截止时间")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("statusCode",$data['statusCode'],"状态")->initOptions($this->status))
        ;
        if($form->isUserRequest($this->_request)){
            //获取表单数据
            $inputs = $form->getInputs();
            if( strtotime($inputs['start']) > strtotime($inputs['finish']) ){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '开始时间不能大于截止时间');
            }
            if( $inputs['count'] < $data['getCount'] && $inputs['count'] !=0){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '领取数量不得大于总量');
            }
            $inputs['statusCode'] = $inputs['statusCode'] ? 1:0;
            //日志
            \Prj\Loger::outVal("inputs-",$inputs);
            try{
                //开启事务
                \Prj\Model\Cdkey::startTransaction();
                //入库
                $cdkeyRet = \Prj\Model\Cdkey::updateOne($inputs,['oid'=>$pkey['oid']]);
                $inputs['cdkeyId'] = $cdkeyRet;
                $cdkeyAwardRet = \Prj\Model\CdkeyAward::updateOne($inputs,['cdkeyId'=>$pkey['oid']]);
                if($cdkeyRet && $cdkeyAwardRet){
                    //事务提交
                    \Prj\Model\Cdkey::commit();
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '修改成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败');
                }
            } catch (Exception $ex) {
                //事务回滚
                \Prj\Model\Cdkey::rollback();
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("修改兑换码");
            $page->initForm($form);
            $this->renderPage($page,true);
        }

//        print_r($couponList);
    }

    //启用兑换码
    public function enableAction(){
        $pkey = $this->getPkey();
        if( empty($pkey['oid']) ){
            return $this->returnError('参数错误');
        }
        //开启事务
        \Prj\Model\Cdkey::startTransaction();
        $cdkRet = \Prj\Model\Cdkey::updateOne(['statusCode'=>1],['oid'=>$pkey['oid']]);
        $cdkARet = \Prj\Model\CdkeyAward::updateOne(['statusCode'=>1],['cdkeyId'=>$pkey['oid']]);
        if($cdkRet && $cdkARet){
            //事务提交
            \Prj\Model\Cdkey::commit();
            return $this->returnOk('',false);
        }else{
            //事务回滚
            \Prj\Model\Cdkey::rollback();
            return $this->returnError('操作失败');
        }
    }

    //停用兑换码
    public function disableAction(){
        $pkey = $this->getPkey();
        if( empty($pkey['oid']) ){
            return $this->returnError('参数错误');
        }
        //开启事务
        \Prj\Model\Cdkey::startTransaction();
        $cdkRet = \Prj\Model\Cdkey::updateOne(['statusCode'=>'0'],['oid'=>$pkey['oid']]);
        $cdkARet = \Prj\Model\CdkeyAward::updateOne(['statusCode'=>'0'],['cdkeyId'=>$pkey['oid']]);
        if($cdkRet && $cdkARet){
            //事务提交
            \Prj\Model\Cdkey::commit();
            return $this->returnOk('',false);
        }else{
            //事务回滚
            \Prj\Model\Cdkey::rollback();
            return $this->returnError('操作失败');
        }
    }
}