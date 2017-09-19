<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-14 16:40
 */

namespace Prj\Model;

/**
 * Class UserChannelRes
 * 用户渠道解析表
 * @package Prj\Model
 * @author lingtima@gmail.com
 */
class UserChannelRes extends _ModelBase
{
    public static function getCopy($id = '')
    {
        return parent::getCopy(['id' => $id]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_user_channel_res';
    }

    public static function getCopyByUid($userId)
    {
        return parent::getCopy(['userOid' => $userId]);
    }
}