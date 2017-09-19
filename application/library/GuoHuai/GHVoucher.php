<?php
namespace Libs\GuoHuai;
/**
 * 国槐tulip系统中voucher读写封装类
 */
class GHVoucher
{
    protected static $_instance = null;
    /**
     * 
     * @return \Libs\GuoHuai\GHVoucher
     */
    public static function getInstance()
    {
        if(self::$_instance ==null){
            self::$_instance = new GHVoucher;
            //self::$_instance->load();
        }
        return self::$_instance;
    }

//    protected function load()
//    {
//        
//    }
    /**
     * 获取指定用户的券的列表
     * @param string $action
     * @return bool 
     */
    public function getVouchers($uid)
    {
        return true;
    }
}