<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/8/29
 * Time: 17:18
 */

class alertController extends \Rpt\Manage\ManageIniCtrl
{
    // 状态
    protected $status = ['关闭','开启'];
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("图片", "imgShow", 300)
            ->addHeader("图片地址", "img", 300)
            ->addHeader("跳转地址", "url", 300)
            ->addHeader("是否开启", "status", 300)
            ->addHeader("操作", "op", 300)
            ->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(null, "listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init("帮助配置")->initStdBtn($uri->uri('','pageAdd'))->initDatagrid($table);
        $this->renderPage($page);
    }

    public function listDataAction(){
        $data = $this->getData();
        foreach( $data as $k => &$v ){
            $status = $v['status'] ? '关闭':'开启';
            $v['status'] = $this->status[$v['status']];
            $v['op'] = $this->btnDelInDatagrid(['id'=>$k]).'|'.$this->btnAjax(['id'=>$k] , 'setStatus' , $status , '确定'.$status.'吗？').'|'.$this->btnEdtInDatagrid(['id'=>$k]);
            $v['imgShow'] = "<img src='". $v['img']."'  height='70'/>";
        }
        $this->renderArray($data);
    }

    /**
     * 添加
     * @param type $arrOrObj
     * @return string
     */
    public function pageAddAction()
    {
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('img', '','上传图片','/manage/inistartup/imgUpload/')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("url", "", "跳转地址")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('status','','是否开启')->initOptions(['关闭','开启']));
        //判断是否提交
        if($form->isUserRequest($this->_request)) {
            $data = $this->getData();
            //获取表单数据
            $inputs = $form->getInputs();
            if( $inputs['img'] && $inputs['url'] ){
                //新增到最后
                array_push($data,['img'=>$inputs['img'],'url'=>$inputs['url'],'status'=>$inputs['status']]);
            }else{
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '参数错误');
            }
//            print_r($data);exit();
            //更新操作
            $ret = \Prj\Model\DataTmp::updateOne(['`value`'=>json_encode($data)],['`key`'=>'popup']);
            if( $ret ){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功',true);
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '操作操作');
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("新增APP弹窗配置");
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    /**
     * 修改
     * @param type $arrOrObj
     * @return string
     */
    public function pageupdAction()
    {
        $strkey = $this->_request->get('__pkey__');
        $pkey = \Prj\Model\DataTmp::getPkey($this->_request->get('__pkey__'));
        $data = $this->getData();
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form
            ->appendHiddenFirst("__pkey__",$strkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('img', $data[$pkey['id']]['img'],'上传图片','/manage/inistartup/imgUpload/')->initChecker(new \Sooh2\Valid\Str(false)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("url", $data[$pkey['id']]['url'], "跳转地址")->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('status',$data[$pkey['id']]['status'],'是否开启')->initOptions(['关闭','开启']));
        //判断是否提交
        if($form->isUserRequest($this->_request)) {
            //获取表单数据
            $inputs = $form->getInputs();
            if(!empty($data[$pkey['id']])){
                if( !empty($inputs['img']) ){
                    $data[$pkey['id']]['img'] = $inputs['img'];
                }
                if( !empty($inputs['url']) ){
                    $data[$pkey['id']]['url'] = $inputs['url'];
                }
                if( isset($inputs['status']) ){
                    $data[$pkey['id']]['status'] = $inputs['status'];
                }
            }
            //更新操作
            $ret = \Prj\Model\DataTmp::updateOne(['`value`'=>json_encode($data)],['`key`'=>'popup']);
            if( $ret ){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功',true);
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '操作操作');
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("新增APP弹窗配置");
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    /**
     * 删除
     * @param type Void
     * @return string
     */
    public function delAction(){
        //获取数据
        $data = $this->getData();
        $pkey = \Prj\Model\DataTmp::getPkey($this->_request->get('__pkey__'));
        if(!empty($data[$pkey['id']])){
            //删除数据
            unset($data[$pkey['id']]);
            //重组数组
            $data = array_merge($data);
            //更新操作
            $ret = \Prj\Model\DataTmp::updateOne(['`value`'=>json_encode($data)],['`key`'=>'popup']);
            if( $ret ){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功');
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '操作成功');
            }
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '无此记录，操作失败');
        }
    }

    /**
     * 设置状态
     * @param type Void
     * @return string
     */
    public function setStatusAction(){
        //获取数据
        $data = $this->getData();
        $pkey = \Prj\Model\DataTmp::getPkey($this->_request->get('__pkey__'));
        if(!empty($data[$pkey['id']])){
            $data[$pkey['id']]['status'] = $data[$pkey['id']]['status'] ? 0:1;
            //更新操作
            $ret = \Prj\Model\DataTmp::updateOne(['`value`'=>json_encode($data)],['`key`'=>'popup']);
            if( $ret ){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '设置状态成功');
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '操作失败');
            }
        }else{
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '无此记录，操作失败');
        }
    }
    /**
     * 获取数据
     * @param type Void
     * @return string
     */
    public function getData(){
        $arr = \Prj\Model\DataTmp::getRecord("*", ['type' => 'alert']);
        return  $arr['value'] ? json_decode($arr['value'],true) : [];
    }

}