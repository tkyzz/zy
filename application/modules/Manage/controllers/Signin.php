<?php

/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-15 15:55
 */
class SigninController extends \Rpt\Manage\ManageIniCtrl
{
    public function pageaddAction()
    {
        
    }

    public function pageupdAction()
    {
        $pkey = $this->getPkey();
        if (empty($pkey)) {
            return $this->returnError('参数不正确！');
        }
        $obj = \Rpt\Manage\ManageActivityScheme::getCopy($pkey);
        $obj->load();
        if ($obj->exists()) {
            $data = [
                'name' => $obj->getField('name'),
                'remark' => $obj->getField('remark'),
                'start_time' => date('Y-m-d', strtotime($obj->getField('start_time'))),
                'end_time' => date('Y-m-d', strtotime($obj->getField('end_time'))),
            ];
        } else {
            return $this->returnError('记录已经存在！');
        }

        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $edtForm->appendHiddenFirst('__pkey__', $this->_request->get('__pkey__'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('name', $data['name'], '配置名称')->initChecker(new \Sooh2\Valid\Str(true, 2, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('remark', $data['remark'], '配置说明')->initChecker(new \Sooh2\Valid\Str(false, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('start_time', $data['start_time'], '生效时间')->initChecker(new \Sooh2\Valid\Str(true, 10, 19)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('end_time', $data['end_time'], '结束时间')->initChecker(new \Sooh2\Valid\Str(true, 10, 19)));

        if ($edtForm->isUserRequest($this->_request)) {//用户提交的请求
            $err = $edtForm->getErrors();
            if (!empty($err)) {
                return $this->returnError('输入数据错误：' . implode(',', $err));
            }
            $inputs = $edtForm->getInputs();

            foreach ($inputs as $k => $v) {
                $obj->setField($k, $v);
            }

            try {
                $ret = $obj->saveToDB();
                return $ret ? $this->returnOk('添加成功') : $this->returnError('添加失败');
            } catch (Exception $ex) {
                return $this->returnError('添加失败:' . $ex->getMessage());
            }
        } else {//展示页面
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init($this->fmtIntro($obj));
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }

    public function pageupd1Action()
    {
        $pkey = $this->getPkey();
        \Sooh2\Misc\Loger::getInstance()->app_trace('PKEY::::');
        \Sooh2\Misc\Loger::getInstance()->app_trace($pkey);

        if (empty($pkey)) {
            return $this->returnError('参数不正确！');
        }
        if(!is_array($pkey)) {
            $pkeyArr['type_name'] = $pkey;
        }else{
            $pkeyArr = $pkey;
        }

        $pkeyArr['__pkey__'] = $this->_request->get('__pkey__');
        $res = \Rpt\Misc\ConfTpl\Main::getInstance()->getTpl($pkeyArr);
        if(!\Lib\Misc\Result::check($res))return $this->returnError($res['message']);
        /** @var \Rpt\Misc\ConfTpl\Signin $tpl */
        $tpl = $res['data']['obj'];
        $edtForm = $tpl->getForm();

        if ($edtForm->isUserRequest($this->_request)) {//用户提交的请求
            \Sooh2\Misc\Loger::getInstance()->app_trace(11111111111);
            $res = $tpl->saveForm();
            if(!\Lib\Misc\Result::check($res)){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,$res['message']);
            }else{
               return  \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,$res['message'],true);
            }
        } else {//展示页面
            \Sooh2\Misc\Loger::getInstance()->app_trace(222222222);
            if(isset($pkey['id'])){
                $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            }else{
                $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            }

            //$page->init($this->fmtIntro($obj));
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }

    public function indexAction()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('方案名称', 'name', 220, '')
            ->addHeader('方案类型', 'activity_name', 220, '')
            //->addHeader('说明', 'remark', 600, '')
            ->addHeader('状态', 'statusName', 90, '')
            ->addHeader('生效时间', 'ymdhis', 220, '')
            ->addHeader('结束时间', 'ymdhisEnd', 220, '')
            ->addHeader('操作', 'op', 300, '')
            ->initJsonDataUrl($uri->uri(null, 'listdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('签到配置')->initStdBtn($uri->uri(null, 'pageadd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }

    public function listdataAction()
    {
        $db = \Rpt\Manage\ManageActivityScheme::getCopy(null)->dbWithTablename();
        //$arr = $db->getRecords($db->kvobjTable(), '*', ['activity_name' => '签到']);
        //todo 显示所有配置 tgh
        $arr = $db->getRecords($db->kvobjTable(), '*' , null , 'sort status rsort activity_name ');

        foreach ($arr as $k => &$v) {
//            $pkey = $v['id'];
            $pkey = ['id' => $v['id']];
            $v['ymdhis'] = $v['start_time'];
            $v['ymdhisEnd'] = $v['end_time'];
            switch ($v['status']) {
                case 'on':
                    $v['statusName'] = '<p class="bg-primary">已开启</p>';
                    break;
                default:
                    $v['statusName'] = '未启用';
                    break;
            }

            $arr[$k]['activity_name'] = $v['activity_name'];
            $arr[$k]['op'] = $this->btnChangeStatus($pkey, 'pageChangeStatus', $v['status']) . '&nbsp;|&nbsp;' .$this->btnEdtInDatagrid($pkey , 'pageupd1')
                .'&nbsp;|&nbsp;'.$this->btnDelInDatagrid($pkey, 'pageDel') ;
//            $arr[$k]['op'] .= '&nbsp;'  ;
        }
        $this->renderArray($arr);
    }

    public function pageDelAction()
    {
        $pkey = $this->getPkey();
        $id = $pkey['id'];
        $Model = \Rpt\Manage\ManageActivityScheme::getCopy($id);
        $Model->load();
        if ($Model->exists()) {
            try {
                $Model->delete();
                return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'删除成功！');
            } catch (Exception $ex) {
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'删除失败：'.$ex->getMessage());

            }
        } else {
            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'删除失败，记录不存在！');
        }
    }

    public function pageChangeStatusAction()
    {
        $pkey = $this->getPkey();
        $statusUpd = $this->_request->get('statusUpd');
        if (!in_array($statusUpd, ['on', 'off'])) {
            return $this->returnError('数据不合法！');
        }

        $Model = \Rpt\Manage\ManageActivityScheme::getCopy($pkey);
        $Model->load();
        if ($Model->exists()) {
            $Model->setField('status', $statusUpd);
            $ret = $Model->saveToDB();
            return $ret ? \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'操作成功！') : \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'操作失败！');
        } else {
            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'记录不存在！');
        }
    }

    protected function btnEdtInDatagrid($pkey, $actionName = 'pageupd')
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey)), $actionName);
        return '<a href="' . $url . '" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'修改\', mask:true,width:800, height:500}">修改</a>';
    }

    protected function btnDelInDatagrid($pkey, $actionName = 'del')
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey)), $actionName);
        return '<a href="' . '" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'你确定要删除吗？\', okCall:function(){mydelcmd(\'' . $url . '\');}}">删除</a>';
    }

    protected function btnLookInDatagrid($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey)), 'configIndex');
        return '<a href="' . $url . '" data-toggle="navtab" data-options="{id:\'delUpd\', title:\'查看\', mask:true,width:800, height:500}">查看</a>';
    }

    protected function btnChangeStatus($pkey, $action, $status)
    {
        if ($status == 'on') {
            $btnName = '关闭';
            $statusUdp = 'off';
        } else {
            $btnName = '开启';
            $statusUdp = 'on';
        }
        $warn = "确定要进行【{$btnName}】操作么？！";

        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey), 'statusUpd' => $statusUdp), $action);
        return '<a href="' . $url . '" data-toggle="alertmsg" data-options="{type:\'confirm\', msg:\'' . $warn . '\', okCall:function(){mydelcmd(\'' . $url . '\');}}">' . $btnName . '</a>&nbsp;';
    }

    public function configIndexAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('配置标识', 'flag', 280, '')
            ->addHeader('配置名称', 'name', 360, '')
            ->addHeader('配置项值', 'valueDate', 850, '')
            ->addHeader('操作', 'op', 150, '')
            ->initJsonDataUrl($uri->uri(['sid' => $strpkey], 'configListdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('方案配置')->initStdBtn($uri->uri(['sid' => $strpkey], 'configAdd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }


    /**
     *图片上传
     */
    public function imgUploadAction(){
        $up=new \Sooh2\Upload;
        $fileField=array_keys($_FILES)[0];
        $uploadPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadPath');
        $up -> setOption("path", $uploadPath)
            -> setOption("maxSize", 20000000)
            -> setOption("allowType", array("png", "jpg","jpeg"));
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


    public function configListDataAction()
    {
        $sid = $this->_request->get('sid');
        $arr = \Rpt\Manage\ManageActivitySchemeConfig::getListByBASE64($sid);

        if (!empty($arr)) {
            foreach ($arr as $k => &$v) {
//                $pkey = $v['id'];
                $pkey = ['id' => $v['id']];
                if (is_numeric($v['value']) && strlen($v['value']) == 10) {
                    $v['valueDate'] = $v['value'] . '&nbsp;(对应日期：' . date('Y-m-d H:i:s', $v['value']) . ')';
                } else {
                    $v['valueDate'] = $v['value'];
                }

//                $v['ymdhis'] = $v['create_time'];
                $v['op'] = $this->btnEdtInDatagrid($pkey, 'configUpd') . '&nbsp;|&nbsp;' . $this->btnDelInDatagrid($pkey, 'configDel');
            }
        }
        $this->renderArray($arr);
    }

    public function configAddAction()
    {
        $edtForm = new \Prj\View\Bjui\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $sidKey = $this->_request->get('sid');
        $edtForm->appendHiddenFirst('sid', $sidKey);
        $edtForm
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('flag', '', '标识')->initChecker(new \Sooh2\Valid\Str(true, 0, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('name', '', '名称')->initChecker(new \Sooh2\Valid\Str(true, 0, 80)))
            ->addFormItem(\Prj\View\Bjui\TableForm::factory('value','','值设置')->initOptions([
                'rowNum' => 9,
                'row'=> [
                    ['Text' , '最小金额' , []],
                    ['Text' , '最大金额' , []],
                    ['Text' , '权重' , [] ],
                ]
            ]));

        if ($edtForm->isUserRequest($this->_request)) {
            $err = $edtForm->getErrors();
            if (!empty($err)) {
                return $this->returnError('输入数据错误：' . implode(',', $err));
            }
            $inputs = $edtForm->getInputs();

            if (\Rpt\Manage\ManageActivitySchemeConfig::getBySidAndFlag(json_decode(hex2bin($sidKey), true), $inputs['flag'])) {
                return $this->returnError('配置项已经存在:' . $inputs['flag']);
            }
            \Prj\Loger::outVal('1111' , $inputs);
            \Prj\Loger::out( $inputs);
            $Model = \Rpt\Manage\ManageActivitySchemeConfig::getCopy(null);
            \Prj\Loger::out( $Model);
            foreach ($inputs as $k => $v) {
                if ($k == 'sid') {
                    $tmpKey = json_decode(hex2bin($sidKey), true);

                    $Model->setField('sid', intval($tmpKey['id']));

                } else {
                    $Model->setField("`$k`", $v);
                }
            }

            try {
                $Model->setField('create_time', date('Y-m-d H:i:s', time()));
                $ret = $Model->saveToDB();
//                \Prj\Loger::out($ret);
                return $ret ? $this->returnOk('添加成功') : $this->returnOk('添加失败');
            } catch (Exception $ex) {
                return $this->returnError('添加失败:' . $ex->getMessage());
            }
        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('新增配置');
            $page->initForm($edtForm);
//            $this->_view->assign('edtForm' , $edtForm);
//            $this->_view->assign('ruleForm' , $ruleForm);
            $this->renderPage($page);
        }
    }

    public function configUpdAction()
    {
        $pkey = $this->getPkey();
        $edtForm = new \Prj\View\Bjui\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        if ($pkey) {
            $ModelConfig = \Rpt\Manage\ManageActivitySchemeConfig::getCopy($pkey);
            $ModelConfig->load();
            if (!$ModelConfig->exists()) {
                return $this->returnError('配置不存在！');
            }
            $data = [
                'sid' => $ModelConfig->getField('sid'),
                'flag' => $ModelConfig->getField('flag'),
                'name' => $ModelConfig->getField('name'),
                'value' => $ModelConfig->getField('value'),
            ];
        } else {
            return $this->returnError('表单不正确');
        }
        //判定value的显示形式
        $flag = $ModelConfig->getField('flag');
        $flag_rand = substr($flag,-4);
        //$pid = $ModelConfig->getField('id');
        if($flag_rand == 'rand'){
            $edtForm->appendHiddenFirst('__pkey__', $this->_request->get('__pkey__'));
            $edtForm
                ->addFormItem(\Sooh2\BJUI\FormItem\Show::factory('flag', $data['flag'], '标识')->initChecker(new \Sooh2\Valid\Str(true, 0, 80)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('name', $data['name'], '名称')->initChecker(new \Sooh2\Valid\Str(true, 0, 80)))
                ->addFormItem(\Prj\View\Bjui\TableForm::factory('value',$data['value'],'值设置'));
        }else{
            $edtForm->appendHiddenFirst('__pkey__', $this->_request->get('__pkey__'));
            $edtForm
                ->addFormItem(\Sooh2\BJUI\FormItem\Show::factory('flag', $data['flag'], '标识')->initChecker(new \Sooh2\Valid\Str(true, 0, 80)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('name', $data['name'], '名称')->initChecker(new \Sooh2\Valid\Str(true, 0, 80)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('value', $data['value'], '值设置')->initChecker(new \Sooh2\Valid\Str(true, 0, 80)));

        }

        if ($edtForm->isUserRequest($this->_request)) {
            $err = $edtForm->getErrors();
            if (!empty($err)) {
                return $this->returnError('输入数据错误：' . implode(',', $err));
            }
            $inputs = $edtForm->getInputs();
            //\Prj\Loger::out($inputs);

            $Model = \Rpt\Manage\ManageActivitySchemeConfig::getCopy($pkey);
            $Model->load();
            if (!$Model->exists()) {
                return $this->returnError('记录不存在！');
            }

            foreach ($inputs as $k => $v) {
                $Model->setField("`$k`", $v);
            }

            try {
                $ret = $Model->saveToDB();
                return $ret ? $this->returnOk('添加成功') : $this->returnOk('添加失败');
            } catch (Exception $ex) {
                return $this->returnError('添加失败:' . $ex->getMessage());
            }
        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('修改配置');
            $page->initForm($edtForm);
           /* $this->_view->assign('edtForm' , $edtForm);
            $this->_view->assign('ruleForm' , $ruleForm);*/
            $this->renderPage($page);

        }
    }

    public function configDelAction()
    {
        $pkey = $this->getPkey();
        $Model = \Rpt\Manage\ManageActivitySchemeConfig::getCopy($pkey);
        $Model->load();
        if ($Model->exists()) {
            try {
                $Model->delete();
                return $this->returnOk('删除成功');
            } catch (Exception $ex) {
                return $this->returnError('删除失败：' . $ex->getMessage());
            }
        } else {
            return $this->returnError('删除失败！记录不存在');
        }
    }
}