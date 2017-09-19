<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-10 15:11
 */

namespace Prj\Model;

/**
 * 邀请关系表
 * @package Prj\Model
 * @author lingtima@gmail.com
 */
class Recommender extends _ModelBase
{
    public static function getCopy($oid = '')
    {
        return parent::getCopy(['oid' => $oid]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 't_wfd_recommender';
    }
}