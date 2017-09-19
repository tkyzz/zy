<?php
/**
 * Author: Tdd
 * Time: 2017-6-29
 */

namespace Prj\Model;



/**
 * Class Msgsentlog
 * @package Prj\Model
 * @author Tdd
 */
class Msgsentlog extends \Rpt\KVObjBase
{

    protected function onInit()
    {
        $this->className = 'ManageMenu';
        parent::onInit();
        $this->_tbName = 'tb_msgsentlog_0 ';
    }

    /**
     * @param array $key
     * @return \Prj\Model\Msgtpl
     * @author lingtima@gmail.com
     */
    public static function getCopy($key)
    {
        return parent::getCopy(['logid' => $key]);
    }


}