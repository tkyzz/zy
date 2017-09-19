<?php
/**
 * Author: Tdd
 * Time: 2017-6-29
 */

namespace Prj\Model;



/**
 * Class UserInvite
 * @package Prj\Model
 * @author Tdd
 */
class UserInvite extends \Rpt\KVObjBase
{

    protected function onInit()
    {
        $this->className = 'ManageMenu';
        parent::onInit();
        $this->_tbName = 'jz_user_final';
    }

    /**
     * @param array $key
     * @return \Prj\Model\Msgtpl
     * @author lingtima@gmail.com
     */
    public static function getCopy($key)
    {
        return parent::getCopy(['userId' => $key]);
    }



}