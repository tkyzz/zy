<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/9/7
 * Time: 11:09
 * desc: 推送模板管理
 */
use Rpt\Manage\ManageIniCtrl;
use Prj\Model;
use Sooh2\Misc\Uri;

class PushTemplateController extends Rpt\Manage\ManageIniCtrl
{
    //是否有效
    protected $userd = [0=>'否',1=>'是'];
    //跳转类型
    protected  $jumpType = [
        null     =>    '无',
        'jumppage'=>    '跳转页面',
        'sign'    =>    '签到'
    ];
    //跳转页面
    protected $jumpPage = [
        null                =>    '无',
        'currentDetail'     =>      '活期详情页面',
        'fixedDetail'       =>      '定期详情页面',
        'myCoupon'          =>      '优惠券列表',
        'fixedList'         =>      '定期列表',
        'url'               =>      '网页',
    ];
    public function indexAction()
    {
        $uri = Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("标识","templateKey",100)
            ->addHeader("标题","title",200)
            ->addHeader("内容","msgModel",200)
            ->addHeader("内容替换字段","msgModelCode",160)
            ->addHeader("透传内容","transText",300)
            ->addHeader("是否有效","userd",50)
            ->addHeader("创建时间","createTime",200)
            ->addHeader("更新时间","updateTime",160)
            ->addHeader("操作人","operater",160)
            ->addHeader("操作","op",160)
            ->initJsonDataUrl($uri->uri("","listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->initDatagrid($table)->initStdBtn($uri->uri('','pageAdd'))->init("推送模板管理");
        $this->renderPage($page);
    }

    public function listDataAction(){
        //查询数据
        $data = Model\PushTemplate::getRecords(null);
        foreach($data as $k => &$v){
            $v['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $v['updateTime'] = date('Y-m-d H:i:s',$v['updateTime']);
            $v['userd'] = $this->userd[$v['userd']];
            $v['op'] = $this->btnEdtInDatagrid(['id'=>$v['id']]).'|'.$this->btnDelInDatagrid(['id'=>$v['id']]);
        }
        $this->renderArray($data);
    }

    /**
     * 新增
     * @param
     * @return string
     */
    public function pageAddAction(){
//        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'htmlUpload');
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        //增加表单
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("title","","标题")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("templateKey","","标识KEY")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("msgModel","","内容")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('jumpType'," ",'跳转类型')->initOptions($this->jumpType))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('pagename'," ",'跳转页面')->initOptions($this->jumpPage))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("url","","URL"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("productId","","产品编号"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('userd'," ",'是否有效')->initOptions($this->userd))
        ;
        //判断是否提交
        if($form->isUserRequest($this->_request)){
            $time = time();
            $obj = Model\PushTemplate::getCopy(null);
            $obj->setField("updateTime",$time);
            $obj->setField("createTime",$time);
            $obj->setField("operater",'system');
            //获取表单数据
            $inputs = $form->getInputs();
            // 标题
            $obj->setField("title",$inputs['title']);
            // key
            $obj->setField("templateKey",$inputs['templateKey']);
            //内容
            $obj->setField("msgModel",$inputs['msgModel']);
            //是否有效
            $obj->setField("userd",$inputs['userd']);
            // 提取内容里的替换字段
            preg_match_all("/\[\w*\]/",$inputs['msgModel'], $matches);
            // 内容替换code(不能重复)
            $obj->setField("msgModelCode",implode(',',array_unique($matches[0])));
            // 组装json 透传内容
            $transText['content']['jumpinfo']['pagename'] = $inputs['pagename'];
            if( $inputs['pagename'] == 'url' ){
                $transText['content']['jumpinfo']['url'] = $inputs['url'];
            }
            $transText['content']['jumpinfo']['productNo'] = $inputs['productId'];
            $transText['content']['type'] = $inputs['jumpType'];
            //透传内容
            $obj->setField("transText",json_encode($transText));
            try{
                //入库
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添推动模板',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加推动模板失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("新增推送模板");
            $page->initForm($form);
            $this->renderPage($page,true);
        }

    }
    /**
     * 修改
     * @param
     * @return string
     */
    public function pageupdAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $obj = Model\PushTemplate::getByBASE64($strpkey);
        $obj->load();
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $transText = $obj->getField('transText');
        //增加表单
        $form->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("title",$obj->getField('title'),"标题")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("templateKey",$obj->getField('templateKey'),"标识KEY")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("msgModel",$obj->getField('msgModel'),"内容")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('jumpType',$transText['content']['type'],'跳转类型')->initOptions($this->jumpType))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('pagename',$transText['content']['jumpinfo']['pagename'],'跳转页面')->initOptions($this->jumpPage))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("url",$transText['content']['jumpinfo']['url'],"URL"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("productId",$transText['content']['jumpinfo']['productNo'],"产品编号"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('userd',$obj->getField('userd'),'是否有效')->initOptions($this->userd))
        ;
        if($form->isUserRequest($this->_request)){
            $time = time();
            $obj->setField("updateTime",$time);
            $obj->setField("createTime",$time);
            $obj->setField("operater",'system');
            //获取表单数据
            $inputs = $form->getInputs();
            // 标题
            $obj->setField("title",$inputs['title']);
            // key
            $obj->setField("templateKey",$inputs['templateKey']);
            //内容
            $obj->setField("msgModel",$inputs['msgModel']);
            //是否有效
            $obj->setField("userd",$inputs['userd']);
            // 提取内容里的替换字段
            preg_match_all("/\[\w*\]/",$inputs['msgModel'], $matches);
            // 内容替换code(不能重复)
            $obj->setField("msgModelCode",implode(',',array_unique($matches[0])));
            // 组装json 透传内容
            $transText['content']['jumpinfo']['pagename'] = $inputs['pagename'];
            if( $inputs['pagename'] == 'url' ){
                $transText['content']['jumpinfo']['url'] = $inputs['url'];
            }
            $transText['content']['jumpinfo']['productNo'] = $inputs['productId'];
            $transText['content']['type'] = $inputs['jumpType'];
            //透传内容
            $obj->setField("transText",json_encode($transText));
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
     * 删除
     * @param
     * @return string
     */
    public function delAction(){
        $obj = Model\PushTemplate::getByBASE64($this->_request->get('__pkey__'));
        $obj->load();
        if($obj->exists()){
            //删除数据
            $obj->delete();
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功');
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '无此记录，操作失败');
        }
    }
}