<?php

namespace Prj\Model\Tmp;

use Sooh2\DB\KVObj;

/**
 * 公司成员表 用户发送短信+邮件
 * @package Prj\Model
 */
class CompanyMember extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'User';
        parent::onInit();
        $this->_tbName = 'tb_tmp_company_member';
    }

}