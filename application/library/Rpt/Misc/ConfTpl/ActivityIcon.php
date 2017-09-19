<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/9
 * Time: 22:55
 */
namespace Rpt\Misc\ConfTpl;

use Rpt\Manage\ManageActivitySchemeConfig;

class ActivityIcon extends \Rpt\Misc\ConfTpl\Main
{
    /** @var  \Sooh2\BJUI\Forms\Edit */
    protected $edtForm;

    protected $pkey;

    protected $data;

    protected $configFlag = [
        'signin_logo_change'    =>  '活动图标开关',
        'signin_logo_icon'  =>  '活动图标',
        'signin_logo_url'   =>  '活动跳转地址'
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
        $imgAction=\Sooh2\Misc\Uri::getInstance()->uriTpl(array(),'imgUpload');

        if(!empty($data['id'])){
            $configs = \Rpt\Manage\ManageActivitySchemeConfig::getRecords('',['sid' => $data['id']]);
            $this->log($configs);
            foreach($configs as $v){
                $name = 'config#'.$v['flag'];
                switch ($v['flag']){
                    case 'signin_logo_change':
                        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Select::factory($name,$v['value'],'是否开启图标')->initChecker(new \Sooh2\Valid\Str())->initOptions(array('0'=>'关闭','1'=>'开启')));
                        break;
                    case 'signin_logo_icon':

                        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\File::factory($name,$v['value'],'上传图片',$imgAction)->initChecker(new \Sooh2\Valid\Str()));
                        break;
                    case 'signin_logo_url':
                        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory($name,htmlspecialchars($v['value']),'图片跳转地址','size=50')->initChecker(new \Sooh2\Valid\Str()));
                        break;
                }

            }
        }else {
            foreach($this->configFlag as $k => $v){
                $name = 'config#'.$k;

                switch ($k){
                    case 'signin_logo_change':
                        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Select::factory($name,'',$v)->initChecker(new \Sooh2\Valid\Str())->initOptions(array('0'=>'关闭','1'=>'开启')));
                        break;
                    case 'signin_logo_icon':

                        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\File::factory($name,'',$v,$imgAction)->initChecker(new \Sooh2\Valid\Str()));
                        break;
                    case 'signin_logo_url':

                        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory($name,'',$v,'size=50')->initChecker(new \Sooh2\Valid\Str()));
                        break;
                }
            }
        }

    }

    public function saveForm(){
        if(!empty($this->data['id'])){
            $obj = \Rpt\Manage\ManageActivityScheme::getCopy(\Lib\Misc\StringH::base64DecodePkey($this->pkey));

            $id = \Lib\Misc\StringH::base64DecodePkey($this->pkey);
            $configInfo = \Rpt\Manage\ManageActivitySchemeConfig::getRecord("*",['sid'=>$id,'flag'=>'signin_logo_icon']);


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

        if(!empty($inputs['config#signin_logo_url'])&&!preg_match('/(http|https|ftp|file){1}(:\/\/)?([\da-z-\.]+)\.([a-zA-Z0-9]{2,6})([\/\w \.-?&%-=]*)*\/?/',$inputs['config#signin_logo_url'])){
            return $this->resultError('请输入合法的url地址');
        }
        foreach($inputs as $k => $v){
               if(strpos($k , 'config#') === 0){
                $flag = substr($k , 7);

                $pkey = ['sid' => $id , 'flag' => $flag];
                $tmp = \Rpt\Manage\ManageActivitySchemeConfig::getCopy($pkey);
                $tmp->load();
                $this->log($tmp->dump());
                $tmp->setField('value' , $v);
                try{
                    $tmp->saveToDB();
                }catch (\Exception $e){
                    return $this->resultError($e->getMessage());
                }
            }
        }

        if(array_key_exists("config#signin_logo_icon",$inputs)&&empty($inputs['config#signin_logo_icon'])&&!empty($configInfo)){

            ManageActivitySchemeConfig::updateOne(['value'=>$configInfo['value']],['id'=>$configInfo['id']]);
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
        } catch (Exception $ex) {
            return $this->resultError('添加失败:' . $ex->getMessage());
        }
    }

    public function getForm(){
        return $this->edtForm;
    }
}