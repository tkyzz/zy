<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/9
 * Time: 22:55
 */
namespace Rpt\Misc\ConfTpl;

class Main extends \Prj\Bll\_BllBase
{

//    protected $activityIconArr = [
//
//    ];
//
//    protected $newbiwRewardArr = [
//
//    ];

    public function getTpl($pkeyArr){

        if(!isset($pkeyArr['id'])){
            $name = $pkeyArr['type_name'];
//
//            $data['__pkey__'] = $pkeyArr['__pkey__'];
//            switch ($name){
//                case $this->tplMap['新手引导']:
//                    $data['text'] = $this->newbiwRewardArr;break;
//                case $this->tplMap['活动图标']:
//                    $data['text'] = $this->activityIconArr;
//                    break;
//            }
//

        }else{
            $obj = \Rpt\Manage\ManageActivityScheme::getCopy($pkeyArr['id']);
            $obj->load();
            if(!$obj->exists())return $this->resultError('记录不存在');

            $name = $this->getClassName($obj->dump());

            $data = $obj->dump();
        }


        if(empty($name))return $this->resultError('无法识别的配置类型');
        $className = '\Rpt\Misc\ConfTpl\\'.$name;

        if(!class_exists($className))return $this->resultError('无法识别的配置类型');
        /** @var \Rpt\Misc\ConfTpl\Signin $tpl */
        $tpl = $className::getInstance();


        $this->log($data);
        $data['__pkey__'] = $pkeyArr['__pkey__'];
        $tpl->initForm($data);
        return $this->resultOK([
            'obj' => $tpl,
        ]);
    }

    protected function getClassName($info){
        return $info['type_name'];
    }
}