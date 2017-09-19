<?php

namespace Prj\Model;

use Sooh2\DB\KVObj;

/**
 *
 * @package Prj\Model
 */
class Activity extends _ModelBase
{
    const register_code = 'register'; //注册红包
    const bind_code = 'bind'; //认证红包
    const charge_code = 'firstCharge'; //首充红包
    const buy_code = 'firstBuy'; //首购红包
    const onechui_code = 'oneChui'; //一锤定音红包
    const yiming_code = 'yiMing'; //一鸣惊人红包
    const jiaxi_code = 'jiaXi'; //加息券的结算
    const rebate_code = 'rebate'; //返利红包
    const invite_code = 'invite'; //邀请活动

    public static $actCodeMap = [
        self::register_code => '注册',
        self::bind_code => '绑卡',
        self::charge_code => '首次充值',
        self::buy_code => '首次购买',
        self::onechui_code => '一锤定音',
        self::yiming_code => '一鸣惊人',
        self::jiaxi_code => '加息',
    ];
    //发送自定义红包的活动类型
    public static $customRewardCode = [
        self::onechui_code,
        self::yiming_code,
        self::jiaxi_code,
    ];

    public static $canSetRuleActCode = [
        self::onechui_code , self::yiming_code
    ];

    /**
     * 后台的下拉列表
     * @return array
     */
    public static function getAdminCodeMap(){
        return [
            self::register_code => '注册',
            self::bind_code => '绑卡',
            self::charge_code => '首次充值',
            self::buy_code => '首次购买',
            self::onechui_code => '一锤定音',
            self::yiming_code => '一鸣惊人',
        ];
    }
    /*
     [xxx]
      dbs[] = 'mysql.xxx'
     */
    protected function onInit(){
        parent::onInit();
        $this->_tbName = 'tb_activity_0';
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