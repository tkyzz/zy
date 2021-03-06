<?php
namespace Prj\Model;

use Prj\Loger;
use Rpt\KVObjBase;

class AppAsset extends \Rpt\Manage\_ModelBase {
    public function onInit()
    {
        $this->className = "HandMail";
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = "tb_app_config";
    }



    public static function getByBASE64($base64str)
    {
        $pkey = json_decode(hex2bin($base64str),true);
        Loger::outVal("pkey->",$pkey);
        return parent::getCopy($pkey);
    }
}