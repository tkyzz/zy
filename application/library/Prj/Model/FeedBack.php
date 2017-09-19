<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/9
 * Time: 10:15
 */
namespace Prj\Model;
class FeedBack extends _ModelBase
{
    public function onInit()
    {
        $this->className = "Manager";
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = "tb_feedback";
    }


    public static function getByBASE64($base64str)
    {
        $pkey = json_decode(hex2bin($base64str),true);
        return parent::getCopy($pkey);
    }

}