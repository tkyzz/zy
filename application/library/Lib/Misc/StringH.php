<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/19
 * Time: 10:35
 */
namespace Lib\Misc;

class StringH
{
    /**
     * 获取一串随机字符串
     * @param int $length
     * @param string $chars
     * @return string
     */
    public static function randStr($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
    {
        // Length of character list
        $chars_length = (strlen($chars) - 1);

        // Start our string
        $string = $chars{rand(0, $chars_length)};

        // Generate random string
        for ($i = 1; $i < $length; $i = strlen($string))
        {
            // Grab a random character from our list
            $r = $chars{rand(0, $chars_length)};

            // Make sure the same two characters don't appear next to each other
            if ($r != $string{$i - 1}) $string .=  $r;
        }

        // Return the string
        return $string;
    }

    /**
     * 生成数据库使用的oid
     * @param string $prefix
     * @return string
     * @throws \Exception
     */
    public static function createOid($prefix = ''){
        return floor(microtime(true) * 1000) . mt_rand(10000 , 99999);
    }

    /**
     * Hand 创建cdk
     * @param int $len
     * @return string
     */
    public static function createCdk($len = 10){
        $chars = 'ABCDEFGHJKMNPQRSTUVWXY3456789';
        return self::randStr($len , $chars);
    }

    public static function createOidGh($prefix = ''){
        $str = $prefix . floor(microtime(true) * 1000);
        $len = strlen($str);
        if($len > 30)throw new \Exception('prefix too long' , 99999);
        return $str . \Lib\Misc\StringH::randStr(32 - $len);
    }

    public static function createUid($length = 28)
    {
        return floor(microtime(true) * 1000) . self::randStr($length - 17) . mt_rand(1000, 9999);
    }

    /**
     * 将数组编码成字符串
     * @param $arr
     * @return string
     */
    public static function base64EncodePkey($arr)
    {
        return bin2hex(json_encode($arr));
    }

    /**
     * 将字符串解码成数组
     * @param $base64str
     * @return array
     */
    public static function base64DecodePkey($base64str)
    {
        return json_decode(hex2bin($base64str),true);
    }

    /**
     * Hand 字符串脱敏
     * @param $str
     * @param $start
     * @param $len
     * @return string
     */
    public static function hideStr($str , $start , $len){
        $str1 = mb_substr($str , 0 , $start , 'utf-8');
        $str2 = mb_substr($str , $start , $len , 'utf-8');
        $str2len = mb_strlen($str2 , 'utf-8');
        $hide = str_pad('' , $str2len , '*');
        $str3 = mb_substr($str , $start + $len ,null , 'utf-8');
        return $str1 . $hide . $str3;
    }
}