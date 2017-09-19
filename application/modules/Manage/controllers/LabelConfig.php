<?php

/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/21
 * Time: 18:55
 */
class LabelConfigController extends \Rpt\Manage\ManageIniCtrl
{
    public function indexAction()
    {
        $table = \Sooh2\HTML\Table::factory()->addHeader("key", "key", 250)
            ->addHeader("value", "value", 300)
            ->addHeader("ret", "ret", 300)
            ->addHeader("操作", "op", 300)->initJsonDataUrl(\Sooh2\Misc\Uri::getInstance()->uri(null, "listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->init("App文字替换")->initDatagrid($table)->initStdBtn(\Sooh2\Misc\Uri::getInstance()->uri(null, "pageAdd"));
        $this->renderPage($page);
    }


    public function listDataAction()
    {
        $arr = \Prj\Model\DataTmp::getRecords("*", ['type' => 'app']);
        foreach ($arr as $k => $v) {
            $arr[$k]['op'] = $this->btnEdtInDatagrid(['`key`' => $v['key']]) . "|" . $this->btnDelInDatagrid(['`key`' => $v['key']]);
        }
        $this->renderArray($arr);

    }

    public function pageAddAction()
    {
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('key', '', 'key')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value", '', 'value')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("ret", "", "备注")->initChecker(new \Sooh2\Valid\Str(true)));
        if ($form->isUserRequest($this->_request)) {
            $err = $form->getErrors();
            if (!empty($err)) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：' . implode(',', $err));
                return;
            }
            $inputs = $form->getInputs();
            $obj = \Prj\Model\DataTmp::getCopy(null);

            foreach ($inputs as $k => $v) {
                $obj->setField("`" . $k . "`", $v);
            }
            $obj->setField("type", "app");
            $ret = $obj->saveToDB();
            if ($ret) {
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '添加成功', true);
            } else {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败');
            }
        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('App文案替换')->initForm($form);
            $this->renderPage($page, true);
        }

    }


    public function pageupdAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\DataTmp::getByBASE64($strpkey);
        $obj->load();
        if (!$obj->exists()) {
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '记录没找到');
            return;
        }
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('key', $obj->getField("key"), 'key')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("value", $obj->getField("value"), 'value')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("ret", $obj->getField("ret"), "备注")->initChecker(new \Sooh2\Valid\Str(true)));
        if ($edtForm->isUserRequest($this->_request)) {
            $err = $edtForm->getErrors();
            if (!empty($err)) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：' . implode(',', $err));
                return;
            }
            $inputs = $edtForm->getInputs();
            foreach ($inputs as $k => $v) {
                $obj->setField("`" . $k . "`", $v);
            }
            $obj->setField("type", "app");
            $ret = $obj->saveToDB();
            if ($ret) {
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '修改成功', true);
            } else {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败失败');
            }
        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('App文案替换')->initForm($edtForm);
            $this->renderPage($page);
        }
    }

    public function delAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $pkey = json_decode(hex2bin($strpkey), true);
        \Prj\Loger::outVal("peu", $pkey);

        $ret = \Prj\Model\DataTmp::deleteOne($pkey);
        if ($ret !== true) {
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功');
        } else {
            \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '操作失败');
        }

    }

}
