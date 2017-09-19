<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-08 10:25
 */

namespace Prj\Tool;

class Common extends Base
{
    public function parseOldPlatform($platForm)
    {
        if (in_array($platForm, ['app', 'pc', 'wx'])) {
            return strtoupper($platForm);
        }
        return $platForm;
    }

    /**
     * 获取真实姓名的脱敏+性别
     * @param string $realname 真实姓名
     * @param bool $gender true男，false女
     * @return string
     * @author lingtima@gmail.com
     */
    public function getNameByRealnameAndGender($realname, $gender = true)
    {
        $strlen = mb_strlen($realname);
        if ($strlen > 3) {
            $surname = mb_substr($realname, 0, 2);
        } else {
            $surname = mb_substr($realname, 0, 1);
        }

        return $surname . ($gender ? '先生' : '女士');
    }

    /**
     * 姓名或者手机号脱敏
     * @param string|int $name 姓名或者手机号
     * @param string $replaceStr 替代字符串
     * @return mixed|string 脱敏后的名称
     * @author lingtima@gmail.com
     */
    public function getUnsentitiveName($name, $replaceStr = '*')
    {
        if (preg_match('#^\d{11}$#', $name)) {
            return $this->getUnsentitiveNameByPhone($name, $replaceStr);
        }
        return $this->getUnsentitiveNameByRealname($name, $replaceStr);
    }

    public function getUnsentitiveNameByPhone($phone, $replaceStr = '*')
    {
        return substr_replace($phone, '****', 3, 4);
    }

    public function getUnsentitiveNameByRealname($realname, $replaceStr = '*')
    {
        if (mb_strlen($realname) > 3) {
            //复姓保留前两个字
            return mb_substr($realname, 0, 2) . str_repeat($replaceStr, mb_strlen($realname) - 2);
        }
        return mb_substr($realname, 0, 1) . str_repeat($replaceStr, mb_strlen($realname) - 1);
    }

}