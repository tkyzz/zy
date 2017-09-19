<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/26
 * Time: 10:59
 */

use Rpt\Manage\ManageCtrl;
use Rpt\Manage\ManageIniCtrl;
use Sooh2\Misc\Uri;

class ProtocolController extends \Rpt\Manage\ManageIniCtrl
{
//    const REGISTER = "register";
//    public $typeArr = [
//        self::REGISTER  =>  "注册协议"
//    ];

    /**
     * 协议类型
     * @param type $arrOrObj
     * @return string
     */
    protected $protocolOptions = [
        'register'                  =>   "注册协议",
        'recharge'                  =>   "充值协议",
        'buy'                       =>   "购买协议",
        'fast'                      =>   "快捷支付服务协议",
        'regular_dingxiang'         =>   "定向委托投资协议-定期",
        'current_dingxiang'         =>   "定向委托投资协议-活期",
        'service'                   =>   "服务协议",
        'fengxian'                  =>   "风险提示书",

    ];

    /**
     * 版本号集合
     * @param type $arrOrObj
     * @return string
     */
    protected $versionArr;
    /**
     * 协议类型主页
     * @param type $arrOrObj
     * @return string
     */
    public function indexAction(){
        $uri = Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("类型","type",200)
            ->addHeader("版本协议","version",160)
            ->addHeader("更新时间","updateTime",200)
            ->addHeader("添加时间","createTime",200)
            ->addHeader("操作","op",160)
            ->initJsonDataUrl($uri->uri("","listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->initDatagrid($table)->initStdBtn($uri->uri('','pageAdd'))->init("协议列表");
        $this->renderPage($page);
    }

    /**
     * 获取协议数据列表
     * @param type $arrOrObj
     * @return string
     */
    public function listDataAction(){
        //查询数据
        $data = \Prj\Model\Protocol::getRecords("id,type,version,updateTime,createTime");//print_r($data);
        if(!empty($data)){
            foreach ($data as $k => $v){
                $data[$k]['updateTime'] = date('Y-m-d H:i:s',$data[$k]['updateTime']);
                $data[$k]['createTime'] = date('Y-m-d H:i:s',$data[$k]['createTime']);
                $data[$k]['type'] = $v['type'].'('.$this->protocolOptions[$v['type']].')';
                $data[$k]['op'] = $this->btnLookAtDatagrid(['id'=>$v['id']]) .'  '. $this->btnDelInDatagrid(['id'=>$v['id']]);
            }
        }
        $this->renderArray($data);

    }
    /**
     * 新增协议
     * @param type $arrOrObj
     * @return string
     */
    public function pageAddAction(){
        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'htmlUpload');
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        //增加表单
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("version","","版本号")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('type'," ",'协议类型')->initOptions($this->protocolOptions))
            ->addFormItem(\Sooh2\BJUI\FormItem\File2::factory('imageUrl', '','上传HTML',$imgAction))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("content","","协议内容")->initChecker(new \Sooh2\Valid\Str(true)));
        //判断是否提交
        if($form->isUserRequest($this->_request)){
            $time = time();
            $obj = \Prj\Model\Protocol::getCopy(null);
            $obj->setField("updateTime",$time);
            $obj->setField("createTime",$time);
            //获取表单数据
            $inputs = $form->getInputs();
            if( !empty($inputs['version']) ){
                if( $this->hasVersion(['version'=>$inputs['version'],'type'=>$inputs['type']]) ) {
                    return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, "操作失败：当前类型【".$inputs['type']."】已存在的协议版本号【".$inputs['version']."】");
                }
            }

//            \Prj\Loger::outVal("inputs-",$inputs);
//            print_r($inputs);
            if( !empty($inputs['imageUrl']) ){
                $inputs['content'] = file_get_contents(APP_PATH.'/html'.$inputs['imageUrl']);
            }
            if(empty($inputs['content'])){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '请上传HTML或输入HTML');
            }
//            print_r($inputs);
            unset($inputs['imageUrl']);
            foreach($inputs as $k => $v){
                $obj->setField($k,$v);
            }
            try{
                //入库
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添加协议',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加协议失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("新增协议");
            $page->initForm($form);
            $this->renderPage($page,true);
        }

    }

    /**
     * 修改协议
     * @param type $arrOrObj
     * @return string
     */
    public function pageupdAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Protocol::getByBASE64($strpkey);
        $obj->load();
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'htmlUpload');

        //增加表单
        $form->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("version",$obj->getField('version'),"版本号")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('type',$obj->getField('type'),'协议类型')->initOptions($this->protocolOptions))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("content",$obj->getField('content'),"协议内容")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\File2::factory('htmlUrl', '','上传HTML',$imgAction))
        ;
        if($form->isUserRequest($this->_request)){
            $inputs = $form->getInputs();
            if( !empty($inputs['imageUrl']) ){
                $inputs['content'] = file_get_contents(APP_PATH.'/html'.$inputs['imageUrl']);
                unset($inputs['imageUrl']);
            }
            foreach($inputs as $k => $v){
                if($k == 'content'){
                    $obj->setField($k,stripslashes($v));
                    continue;
                }
                $obj->setField($k,$v);

            }
            $obj->setField("updateTime",time());
            try{
                $ret = $obj->saveToDB();

                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '修改成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("修改协议详情");
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    /**
     * 删除协议
     * @param type Void
     * @return string
     */
    public function delAction(){

        $obj = \Prj\Model\Protocol::getByBASE64($this->_request->get('__pkey__'));
        $obj->load();
        if($obj->exists()){
            //删除数据
            $obj->delete();
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功');
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '无此记录，操作失败');
        }
    }

    /**
     * 查看协议
     * @param type Void
     * @return string
     */
    public function infoAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Protocol::getByBASE64($strpkey);
        $obj->load();
        $page = \Prj\View\Bjui\Detail::getInstance();
        $page
//            ->setData("协议类型：",$obj->getField("type").'-'.$this->protocolOptions[$obj->getField("type")],2)
//            ->setData("版本号：",$obj->getField("version"),2)
            ->setData("内容：",$obj->getField("content"));
        $this->renderPage($page);
    }


    /**
     * 协议是否存在
     * @param type Void
     * @return string
     */
    public function hasVersion($arr=[]){
        if( empty($arr) ){
            return false;
        }
        $id = \Prj\Model\Protocol::getRecord('id' , $arr)['id'];

        if( $id ){
            return true;
        }else{
            return false;
        }
    }

    /**
     *HTML文件上传
     */
    public function htmlUploadAction(){
        $up=new \Sooh2\Upload;
        $fileField=array_keys($_FILES)[0];
        $uploadPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadPath');
        $up -> setOption("path", $uploadPath)
            -> setOption("maxSize", 20000000)
            -> setOption("allowType", array("html", "htm"));
        if($up->upload($fileField)){
            $uploadUrl=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadUrl');
            $fileName=$uploadUrl."/".$up->getFileName();
            $arr=array('statusCode'=>'200','filename'=>$fileName);
            $this->renderArray($arr);
        }else{
            $arr=array('statusCode'=>'100','message'=>$up->getErrorMsg());
            $this->renderArray($arr);
        }
    }
    protected function btnLookAtDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__'=> \Rpt\KVObjBase::base64EncodePkey($pkey)),'info');
        return  '<a href="'.$url.'" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'预览\', mask:true,width:800, height:500}">预览</a>&nbsp;';
    }
}