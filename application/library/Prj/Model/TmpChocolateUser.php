<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/16
 * Time: 15:44
 */

namespace Prj\Model;

class TmpChocolateUser extends _ModelBase
{
    protected function onInit(){
        $this->className = 'User';
        parent::onInit();
        $this->_tbName = 'tb_tmp_chocolate_user_0';
    }

    /**
     * Hand Hand
     * @param $phone
     * @param int $dayChocolateTimesLimit
     * @return null|static
     */
    public static function getOneCopy($phone , $dayChocolateTimesLimit = 3){
        $model = self::getCopy(['phone' => $phone]);
        $model->load();
        if(!$model->exists()){
            $model->setField('createTime' , date('Y-m-d H:i:s'));
            $model->setField('dayChocolateTimesLimit' , $dayChocolateTimesLimit);
            $ret = $model->saveToDB();
            if(!$ret)return null;
            $model->load(true);
        }
        return $model;
    }
}