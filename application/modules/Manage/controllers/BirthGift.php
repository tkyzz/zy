<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/8/23
 * Time: 16:18
 * 后台生日礼包活动配置
 */

class BirthGiftController extends \Rpt\Manage\ManageIniCtrl
{

    protected $status = ['close'=>'关闭','open'=>'开启'];
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("配置项", "ret", 300)
            ->addHeader("值", "value", 300)
            ->addHeader("操作", "op", 300)->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(null, "listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init("生日礼包活动配置")->initDatagrid($table);
        $this->renderPage($page);
    }

    public function listDataAction()
    {
//        print_r($this->couponsStr());exit();
        $arr = \Prj\Model\DataTmp::getRecords("*", ['type' => 'birth']);
        foreach ($arr as $k => $v) {
            if( $v['key'] == 'coupons' ){
                $arr[$k]['value']  = $this->couponsStr(json_decode($v['value'],true));
            }elseif( $v['key'] == 'enable' ){
                $arr[$k]['value'] = $arr[$k]['value'] ? '是':'否';
            }
            $arr[$k]['op'] = $this->btnEdtInDatagrid(['`key`' => $v['key']]);
        }
        $this->renderArray($arr);

    }

    public function couponsStr($where=[]){
        if( empty($where) ){
            return false;
        }
          $data = \Prj\Bll\Coupon::getInstance()->getRecords(['oid'=>$where],'rsort status rsort createTime')['data'];
        $str = [];
        foreach($data as $k=>$v){
            if( strtoupper($v['type']) == 'RATECOUPON' ){
                $str[] = $v['name'].'('.$v['upperAmount'].'%)';
            }else{
                $str[] = $v['name'].'('.$v['upperAmount'].'元)';
            }

        }
        return implode(',',$str);
    }
    //更改配置项
    public function pageupdAction()
    {
        $coupon = \Prj\Bll\Coupon::getInstance()->getRecords(['status'=>'yes'] , 'rsort status rsort createTime')['data'];
        foreach($coupon as $k => $v){
            //加息券
            if( strtoupper($v['type']) == 'RATECOUPON' ){
                $couponOption[$v['oid']] = $v['name'].'('.$v['upperAmount'].'%)';
            }else{
                $couponOption[$v['oid']] = $v['name'].'('.$v['upperAmount'].'元)';
            }

        }
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\DataTmp::getByBASE64($strpkey);
        $obj->load();
        if (!$obj->exists()) {
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('key', $obj->getField("key"), '配置项',readonly)->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("ret", $obj->getField("ret"), "备注",readonly)->initChecker(new \Sooh2\Valid\Str(true)));
        if( $obj->getField('key') == 'coupons' ){
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\MultiSelect::factory("value", $obj->getField("value"), 'value')->initOptions($couponOption)->initChecker(new \Sooh2\Valid\Str(true)));
        }elseif( $obj->getField('key') == 'enable' ){
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("value", $obj->getField("value")=="1"?'open':'close', 'value')->initOptions($this->status)->initChecker(new \Sooh2\Valid\Str(true)));
        } else{
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value", $obj->getField("value"), 'value')->initChecker(new \Sooh2\Valid\Str(true)));
        }
        if ($edtForm->isUserRequest($this->_request)) {
            $inputs = $edtForm->getInputs();
//            $err = $edtForm->getErrors();
            if (!isset($inputs['value'])) {
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, 'value值不能为空');
            }
            if($obj->getField('key') == 'coupons'){
                //生日礼物列表配置项
                $inputs['value'] = json_encode($inputs['value']);
            }elseif( $obj->getField('key') == 'enable' ){
                $inputs['value'] = $inputs['value'] == 'open' ? 1:0;
            }
            $obj->setField("`value`", $inputs['value']);
            $ret = $obj->saveToDB();
            if ($ret) {
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '修改成功', true);
            } else {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败');
            }
        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('生日礼包活动配置')->initForm($edtForm);
            $this->renderPage($page);
        }
    }
}