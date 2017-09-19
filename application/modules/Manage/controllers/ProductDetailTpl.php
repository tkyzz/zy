<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/7
 * Time: 11:25
 */
class ProductDetailTplController extends \Rpt\Manage\ManageIniCtrl
{
    public $productOptions = [
        'currentIncome'     =>  "活期收益说明",
        'currentIntro'      =>  "活期项目介绍",
        'regularIncome'     =>  "定期收益说明",
        'regularIntro'      =>  "定期项目说明"
    ];

    protected $statusOptions = ['1'=>'开启','0'=>'关闭'];
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('类型','tplcode',200,'')
            ->addHeader('标题','title',500,'')
            ->addHeader('状态','status',150,'')
            ->addHeader('操作','op',150,'')

            ->initJsonDataUrl($uri->uri("","listData"));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('产品详情模板')->initStdBtn($uri->uri('','pageAdd'))->initDatagrid($table);

        $this->renderPage($page);
    }


    public function listDataAction(){
        $obj = \Prj\Model\ProductDetailTpl::getCopy(null);
        list($db,$tb) = $obj->dbAndTbName();
        $list = $db->getRecords($tb,'*');

        foreach($list as $k => $v){
            if(!$v['status']){
                $list[$k]['status'] = "未开启";
            } else{
                $list[$k]['status'] = "已开启";
            }
            $list[$k]['tplcode'] = $this->productOptions[$v['tplcode']];
            $list[$k]['op'] = $this->btnEdtInDatagrid(['id'=>$v['id']])."|".$this->btnDelInDatagrid(['id'=>$v['id']]);
        }

        $this->renderArray($list);
    }



    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('tplcode',"",'模板类型')->initOptions($this->productOptions))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("status",1,"是否开启")->initOptions($this->statusOptions))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("title","","标题")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("content","","模板内容"));
        if($form->isUserRequest($this->_request)){
            $obj = \Prj\Model\ProductDetailTpl::getCopy(null);

            $inputs = $form->getInputs();
            \Prj\Loger::outVal("inputs-",$inputs);
//            $obj->load();
//            if($obj->exists()){
//                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '此记录已经存在');
//                return;
//            }
            foreach($inputs as $k => $v){
                if($k == 'content'){
                    $obj->setField($k,$v);
                    continue;
                }
                    $obj->setField($k,$v);

            }
            $obj->setField("createTime",date("Y-m-d H:i:s"));
            $obj->setField("updateTime",date("Y-m-d H:i:s"));
            try{
                $ret = $obj->saveToDB();

                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添加产品详情模板：',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败）');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('添加产品详情模板');
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    public function pageupdAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\ProductDetailTpl::getByBASE64($strpkey);
        $obj->load();
        if(!$obj->exists()){
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }

        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('tplcode',$obj->getField("tplcode"),'模板类型')->initOptions($this->productOptions))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("status",$obj->getField("status"),"是否开启")->initOptions($this->statusOptions))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("title",$obj->getField("title"),"标题")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("content",$obj->getField("content"),"模板内容"));
        if($form->isUserRequest($this->_request)){

            $inputs = $form->getInputs();

            foreach($inputs as $k => $v){
                if($k == 'content'){
                    $obj->setField($k,stripslashes($v));
                    continue;
                }
                $obj->setField($k,$v);

            }
            $obj->setField("updateTime",date("Y-m-d H:i:s"));
            try{
                $ret = $obj->saveToDB();

                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功修改模板数据：',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败:'.$ex->getMessage());
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('添加产品详情模板');
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }


    public function delAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\ProductDetailTpl::getByBASE64($strpkey);
        $obj->load();
        if($obj->exists()){
            $obj->delete();
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功删除');
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到，删除失败');
        }
    }


}