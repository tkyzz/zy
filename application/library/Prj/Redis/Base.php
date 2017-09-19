<?php
namespace Prj\Redis;
/**
 * Description of Base
 *
 * @author simon.wang
 */
class Base {
    const maxPerHour = 100;
    /**
     * @return \Sooh2\DB\Interfaces\DB $db
     */
    public static function getDB()
    {
        $conf = \Sooh2\Misc\Ini::getInstance()->getIni('DB.redis');
        return \Sooh2\DB::getConnection($conf);
    }
    /**
     * 检查该ip下1小时内敏感行为的计数
     * @param \Sooh2\DB\Interfaces\DB $db
     * @param string $ip
     * @return bool  计数是否允许继续后续操作
     */
    protected static function chkip($db,$ip)
    {
        $dt = time();
        $hNow = date('H',$dt);
        if(date('i',$dt)<30){
            $h2 = date('H',$dt-3600);
        }else{
            $h2 = date('H',$dt+3600);
        }
        $key1 = 'limitsChk:iplimit:'.$ip.':'.$hNow;
        $key2 = 'limitsChk:iplimit:'.$ip.':_'.$h2;
        
        if($db->exec(array(['exists',$key1]))){
            $n1 = $db->exec(array(['incr',$key1]));
        }else{
            $db->exec(array(['set',$key1,$n1 = 1]));
            $db->exec(array(['setTimeout',$key1,3600]));//60分钟过期
        }
        if($db->exec(array(['exists',$key2]))){
            $n2 = $db->exec(array(['incr',$key2]));
        }else{
            $db->exec(array(['set',$key2,$n2 = 1]));
            $db->exec(array(['setTimeout',$key2,3600]));//60分钟过期
        }
        
        $maxPerHour = self::maxPerHour;
        if (\Prj\Tool\Debug::isTestEnv()) {
            $maxPerHour *= 10;
        }

        if ($n1 > $maxPerHour || $n2 > $maxPerHour) {
            return false;
        }else{
            return true;
        }
    }

    public static function set($key , $value , $second = null){
        $value = is_array($value) ? json_encode($value , 256) : $value;
        if(empty($second)){
            $cmd = ['SET' , $key , $value];
        }else{
            $cmd = ['SETEX' , $key , $second , $value];
        }
        return self::getDB()->exec([$cmd]);
    }

    public static function get($key){
        $ret = self::getDB()->exec([
            ['GET' , $key]
        ]);
        if($ret === false)return false;
        if(empty($ret))return null;
        $res = json_decode($ret , true);
        return !empty($res) ? $res : $ret;
    }

    public static function del($key){
        return self::getDB()->exec([
            ['DELETE' , $key]
        ]);
    }
}
