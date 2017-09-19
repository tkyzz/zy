<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/9
 * Time: 22:55
 */

namespace Rpt\Misc\ConfTpl;

class Signin extends \Rpt\Misc\ConfTpl\Main
{
    /** @var  \Sooh2\BJUI\Forms\Edit */
    protected $edtForm;

    protected $pkey;

    protected $data;

    /**
     * 根据这里写死的配置名称来输出表单项
     * @var array
     */
    protected $configFlag = [
        'signin_coupon_type_name' => '代金券类型名称',
        'signin_coupon_type_code' => '代金券类型标签',
        'signin_coupon_name' => '代金券名称',

        'signin_amount_invest' => '代金券投资限额(元)',
        'signin_amount_final' => '签到最后一天的固定奖励(元)',
        'signin_switch_version_time' => array('版本切换时间戳','大于此时间戳则切换到新版'),
        'signin_amount_rand' => '签到其他时间随机金额配置JSON（单键值对=固定值）',




    ];

    public function initForm($data)
    {
        $this->data = $data;
        $this->pkey = $data['__pkey__'];
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $this->edtForm = $edtForm;
        $edtForm->appendHiddenFirst('__pkey__', $data['__pkey__'])
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('name', $data['name'], '配置名称')->initChecker(new \Sooh2\Valid\Str(true, 2, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('remark', $data['remark'], '配置说明')->initChecker(new \Sooh2\Valid\Str(false, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('start_time', $data['start_time'], '生效时间')->initChecker(new \Sooh2\Valid\Str(true, 10, 19)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('end_time', $data['end_time'], '结束时间')->initChecker(new \Sooh2\Valid\Str(true, 10, 19)));
        $this->log($data);
        $configs = \Rpt\Manage\ManageActivitySchemeConfig::getRecords('', ['sid' => $data['id']]);
        foreach ($configs as $v) {
            $tmp[$v['flag']] = $v;
        }
        $configs = $tmp;
        $this->log($configs);
        foreach ($this->configFlag as $k => $vv) {
            $v = $configs[$k];
            $name = 'config#' . $k;
            if($k == 'signin_switch_version_time'){
                $opStr = 'placeholder='.array_pop($this->configFlag[$k]);
                $vv = array_shift($this->configFlag[$k]);
            }
            if ($k == 'signin_amount_rand') {
                $edtForm->addFormItem(\Prj\View\Bjui\TableForm::factory($name, isset($v['value']) ? $v['value'] : '', $vv));
                continue;
            }
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory($name, htmlspecialchars(isset($v['value']) ? $v['value'] : ''), $vv,isset($opStr)?$opStr:'')->initChecker(new \Sooh2\Valid\Str(true, 1, 80)));
        }
    }

    public function saveForm()
    {
        if (!empty($this->data['id'])) {
            $obj = \Rpt\Manage\ManageActivityScheme::getCopy(\Lib\Misc\StringH::base64DecodePkey($this->pkey));
            $id = \Lib\Misc\StringH::base64DecodePkey($this->pkey);

        } else {
            $id = rand(100, 100000);
            $obj = \Rpt\Manage\ManageActivityScheme::getCopy($id);
        }

        $obj->load();

        $edtForm = $this->edtForm;
        $err = $edtForm->getErrors();
        if (!empty($err)) {
            return $this->resultError('输入数据错误：' . implode(',', $err));
        }
        $inputs = $edtForm->getInputs();


        foreach ($inputs as $k => $v) {
            if (strpos($k, 'config#') === 0) {
                $flag = substr($k, 7);
                $pkey = ['sid' => $id, 'flag' => $flag];
                $tmp = \Rpt\Manage\ManageActivitySchemeConfig::getCopy($pkey);
                $tmp->load();
                $this->log($tmp->dump());
                $tmp->setField('value', $v);
                try {
                    $tmp->saveToDB();
                } catch (\Exception $e) {
                    return $this->resultError($e->getMessage());
                }
            }
        }

//        foreach ($inputs as $k => $v) {
//            $obj->setField($k, $v);
//        }


        $obj->setField('name', $inputs['name']);
        $obj->setField('remark', $inputs['remark']);
        if (empty($this->data['id'])) {
            $type_name = \Lib\Misc\StringH::base64DecodePkey($this->pkey);
            $activity_name = \Rpt\Manage\ManageActivityScheme::getCopy()->getMap()[$type_name];
            $obj->setField('type_name', $type_name);
            $obj->setField('activity_name', $activity_name);

        }
        $obj->setField('create_time', date('Y-m-d H:i:s', time()));
        $obj->setField('start_time', $inputs['start_time']);
        $obj->setField('end_time', $inputs['end_time']);

        // return $this->resultError('xxxxxxxxxxxxxx');
        try {
            $ret = $obj->saveToDB();

            return $ret ? $this->resultOK('添加成功') : $this->resultError('添加失败');
        } catch (Exception $ex) {
            return $this->resultError('添加失败:' . $ex->getMessage());
        }
    }

    public function getForm()
    {
        return $this->edtForm;
    }
}