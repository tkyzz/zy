<?php
namespace Prj\Model\RW;

/**
 * 站内信 读取类
 *
 * @author simon.wang
 */
class LetterReader extends \Sooh2\DB\KVObj{
    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 't_platform_mail';
    }
}
