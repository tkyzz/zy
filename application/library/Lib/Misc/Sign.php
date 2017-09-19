<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Lib\Misc;

/**
 * Description of Sign
 *
 * @author simon.wang
 */
class Sign {
    /**
     * 验签
     * @param type $dt
     * @param type $sign
     * @param type $ip
     * @return bool 验签是否通过
     * @throws \ErrorException
     */
    public static function chkMd5ByDt($dt,$sign,$ip)
    {
        $conf = \Sooh2\Misc\Ini::getInstance()->getIni('InnerSign');
        $signPass = false;
        foreach($conf['keys'] as $key){
            if(md5($dt.$key)==$sign){
                $signPass=true;
                break;
            }
        }
        if(in_array($ip, $conf['innerip']) && $signPass){
            return true;
        }else{
            \Prj\Loger::out('ip: ' . $ip . ' ' . json_encode($conf['innerip']));
            return false; 
        }
    }
    /**
     * 根据dt生成md5签名
     * @param string $dt
     * @return type
     */
    public static function md5ByDt($dt){
        $key = array_pop(\Sooh2\Misc\Ini::getInstance()->getIni('InnerSign.keys'));
        return md5($dt.$key);
    }
}
