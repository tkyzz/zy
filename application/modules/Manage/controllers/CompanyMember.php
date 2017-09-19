<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/8/18
 * Time: 18:04
 */

use Rpt\Manage\ManageCtrl;
use Rpt\Manage\ManageIniCtrl;
use Sooh2\Misc\Uri;
use Prj\Model\Tmp\CompanyMember;

class CompanyMemberController extends \Rpt\Manage\ManageIniCtrl
{
    //组
    protected $groupArr = [
            'APP'       =>  '客户端',
            'SERVER'    =>  '服务端',
            'PC'        =>  "PC",
            'M'         =>  "M站",
            'OTHER1'    =>  '其他1',
            'OTHER2'    =>  '其他2',
            'OTHER3'    =>  '其他3',
        ];

    public function indexAction(){
        $uri = Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("姓名","name",160)
            ->addHeader("手机号","phone",160)
            ->addHeader("邮箱","email",200)
            ->addHeader("组","groups",220)
            ->addHeader("加入时间","createTime",200)
            ->addHeader("操作","op",160)
            ->initJsonDataUrl($uri->uri("","listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->initDatagrid($table)->initStdBtn($uri->uri('','pageAdd'))->init("通知人员");
        $this->renderPage($page);
    }

    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        //增加表单
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("name","","姓名")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("phone","","手机号")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("email","","邮箱")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\MultiSelect::factory("groups","","组")->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->groupArr));
        //判断是否提交
        if($form->isUserRequest($this->_request)){
            $obj = CompanyMember::getCopy(true);
            $obj->setField("createTime",date("Y-m-d H:i:s"));
            //获取表单数据
            $inputs = $form->getInputs();
            foreach($inputs as $k => $v){
                if( $k == 'groups' ){
                    $v = implode(',',$v);
                }
                $obj->setField($k,$v);
            }
            try{
                //入库
                $ret = $obj->saveToDB();
                if($ret){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '添加成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败');
                }
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("新增通知人员");
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    /**
     * 修改通知人员信息
     * @param type $arrOrObj
     * @return string
     */
    public function pageupdAction()
    {
//        print_r($this->getPkey());exit();
//        $strpkey = $this->_request->get('__pkey__');
//        //获取当前oid
//        $obj = \Rpt\KVObjBase::getByBASE64($strpkey);
        $strpkey = $this->_request->get('__pkey__');
        $oid = $this->getPkey()['id'];
        //读取一条记录
        $res = CompanyMember::getRecord('*',['oid'=>$oid]);
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        //增加表单
        $form->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("name",$res['name'],"姓名")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("phone",$res['phone'],"手机号")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("email",$res['email'],"邮箱")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\MultiSelect::factory("groups",$res['groups'],"组")->initChecker(new \Sooh2\Valid\Str(true))->initOptions($this->groupArr));
        ;
        if($form->isUserRequest($this->_request)){
            $inputs = $form->getInputs();
            foreach($inputs as $k => &$v){
                //将组数据转成逗号连接的字符串
                if( $k == 'groups' ){
                    $v = implode(',',$v);
                }
            }
            try{
                //更新一条记录
                $ret = CompanyMember::updateOne($inputs,['oid'=>$oid]);
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
            $page->init("修改通知人员详情");
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    /**
     * 删除通知人员列表
     * @param type Void
     * @return string
     */
    public function delAction(){

        //获取当前oid
//        $obj = \Rpt\KVObjBase::getByBASE64($this->_request->get('__pkey__'));
        $oid = $this->getPkey()['id'];
        //读取一条记录
        $res = CompanyMember::getRecord('oid',['oid'=>$oid]);
        if($res['oid']){
            //删除数据
            CompanyMember::deleteOne(['oid'=>$oid]);
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功');
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '无此记录，操作失败');
        }
    }

    /**
     * 获取通知人员列表
     * @param type $arrOrObj
     * @return string
     */
    public function listDataAction(){
//      oid,name,phone,email,group,createTime
        $data = CompanyMember::getRecords("*");
        if(!empty($data)){
            foreach ($data as $k => $v){
                $data[$k]['groups'] = explode(',',$v['groups']);
//                foreach($data[$k]['groups'] as $gk => $gv){
//                    $data[$k]['groups'][$gk] = $this->groupArr[$gv];
//                }
                $data[$k]['op'] = $this->btnEdtInDatagrid(['id'=>$v['oid']]) .'  '. $this->btnDelInDatagrid(['id'=>$v['oid']]);
            }
        }
        $this->renderArray($data);
    }


}