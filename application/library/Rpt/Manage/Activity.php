<?php

namespace Rpt\Manage;

/**
 *
 * @package Prj\Model
 */
class Activity extends \Rpt\Manage\_ModelBase
{
    const register_code = 'register';
    const bind_code = 'bind';
    const charge_code = 'firstCharge';
    const buy_code = 'firstBuy';
    const onechui_code = 'oneChui';
    const yiming_code = 'yiMing';

    public static $actCodeMap = [
        self::register_code => '注册',
        self::bind_code => '绑卡',
        self::charge_code => '首次充值',
        self::buy_code => '首次购买',
        self::onechui_code => '一锤定音',
        self::yiming_code => '一鸣惊人',
    ];

    public static $canSetRuleActCode = [
        self::onechui_code , self::yiming_code
    ];
    /*
     [xxx]
      dbs[] = 'mysql.xxx'
     */
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 'tb_activity_0';
    }

    public static function getOne($where){
        $db = static::getCopy('')->dbWithTablename();
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    public static function ruleEncode($form = []){
        $data = [];
        foreach ($form as $v){
            if(empty($v[0]) && empty($v[1]))continue;
            if(empty($v[0]) && $v[0] !== 0 && $v[0] !== '0')return false;
            if(empty($v[2]))return false;
            $v[1] = !empty($v[1]) ? $v[1] : self::MAX_NUM;
            $v[0] -= 0;
            $v[1] -= 0;
            $data[$v[0] .'_' . $v[1]] = $v[2];
        }
        return $data;
    }

    public static function ruleDecode($rules){
        $data = [];
        foreach ($rules as $k => $v){
            list($tmp[0] , $tmp[1]) = explode('_' , $k);
            $tmp[1] = $tmp[1] == (self::MAX_NUM) ? '' : $tmp[1];
            $tmp[2] = $v;
            $data[] = $tmp;
        }
        return $data;
    }

}