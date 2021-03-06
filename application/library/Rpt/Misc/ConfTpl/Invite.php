<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-15 09:56
 */

namespace Rpt\Misc\ConfTpl;

class Invite extends \Rpt\Misc\ConfTpl\Main
{
    /** @var  \Sooh2\BJUI\Forms\Edit */
    protected $edtForm;

    protected $pkey;

    protected $data;

    protected $configFlag = [
        'invite_banner_img_url' => '邀请Banner页图片地址',
        'invite_banner_jump_url' => '邀请Banner页跳转地址',
        'invite_title' => '邀请一句话标题',
        'invite_descript' => '邀请一句话描述',
        'invite_jump_url' => '跳转目标基础URL',
        'invite_introduction_url' => '了解更多URL',
        'invite_share_title' => '分享标题',
        'invite_share_image_url' => '分享图标地址',
        'invite_share_content' => '分享内容描述',
        'invite_share_coupon_amount' => '邀请红包金额',
    ];

    public function initForm($data){
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
        if(isset($data['id'])){
            $configs = \Rpt\Manage\ManageActivitySchemeConfig::getRecords('',['sid' => $data['id']]);

            foreach($this->configFlag as $k=>$v){
                $name = 'config#'.$k;
                $value = '';
                foreach ((array)$configs as $vv){
                    if($vv['flag'] == $k){
                        $value = htmlspecialchars($vv['value']);
                    }
                }
                $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory($name,$value,$v)->initChecker(new \Sooh2\Valid\Str(true,0,1000)));
            }
        }else {
            foreach($this->configFlag as $k=>$v){
                $name = 'config#'.$k;

                $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory($name,'',$v)->initChecker(new \Sooh2\Valid\Str(true,0,1000)));
            }
        }

    }

    public function saveForm(){
        if(!empty($this->data['id'])){
            $obj = \Rpt\Manage\ManageActivityScheme::getCopy(\Lib\Misc\StringH::base64DecodePkey($this->pkey));
            $id = \Lib\Misc\StringH::base64DecodePkey($this->pkey);
            $id = $id['id'];
        }else{
            $id = rand(100,100000);
            $obj = \Rpt\Manage\ManageActivityScheme::getCopy($id);
        }
        $obj->load();
        $edtForm = $this->edtForm;
        $err = $edtForm->getErrors();
        if (!empty($err)) {
            return $this->resultError('输入数据错误：' . implode(',', $err));
        }
        $inputs = $edtForm->getInputs();

        foreach($inputs as $k => $v){
            if(strpos($k , 'config#') === 0){
                $flag = substr($k , 7);
                $pkey = ['sid' => $id , 'flag' => $flag];
                $tmp = \Rpt\Manage\ManageActivitySchemeConfig::getCopy($pkey);
                $tmp->load();
                $this->log($tmp->dump());
                $tmp->setField('value' , $v);
                $tmp->setField('name' , $this->configFlag[$flag]);
                try{
                    $tmp->saveToDB();
                }catch (\Exception $e){
                    return $this->resultError($e->getMessage());
                }
            }
        }

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

        try {
            $ret = $obj->saveToDB();
            return $ret ? $this->resultOK('添加成功') : $this->resultError('添加失败');
        } catch (\Exception $ex) {
            return $this->resultError('添加失败:' . $ex->getMessage());
        }
    }

    public function getForm(){
        return $this->edtForm;
    }
}