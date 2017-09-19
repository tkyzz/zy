<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-10 13:59
 */

namespace Prj\Tool;

/**
 * 身份证解析类
 * @package Prj\Tool
 * @author lingtima@gmail.com
 */
class IdentityCard extends Base
{
    /**
     * 获取详细信息
     * @param string $id 身份证号
     * @return array
     * @author lingtima@gmail.com
     */
    public function getInfo($id)
    {
        $code = self::checkArg($id);
        $addr = substr($code['body'], 0, 6);
        $birth = ($code['type'] === 18 ? substr($code['body'], 6, 8) :
            substr($code['body'], 6, 6));
        $order = substr($code['body'], -3);

        $info = array();
        $info['addrCode'] = $addr;
        $info ['birth'] = ($code ['type'] === 18 ? (substr($birth, 0, 4) . '-' . substr($birth, 4, 2) . '-' . substr($birth, -2)) : ('19' . substr($birth, 0, 2) . '-' . substr($birth, 2, 2) . '-' . substr($birth, -2)));
        $info['sex'] = ($order % 2 === 0 ? 0 : 1);//0女，1男
        $info['length'] = $code['type'];
        if ($code['type'] === 18) {
            $info['checkBit'] = $code['checkBit'];
        }

        return $info;
    }

    /**
     * @param string $id 身份证号
     * @return array|bool|null
     * @author lingtima@gmail.com
     */
    protected function checkArg($id)
    {
        $id = strtoupper($id);
        $code = null;
        if (strlen($id) === 18) {
            // 18位
            $code = array(
                "body" => substr($id, 0, 17),
                "checkBit" => substr($id, -1),
                "type" => 18
            );
        } else if (strlen($id) === 15) {
            // 15位
            $code = array(
                "body" => $id,
                "type" => 15
            );
        } else {
            return false;
        }
        return $code;
    }
}