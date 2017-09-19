<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-15 15:19
 */

namespace Prj\Model;

class EventQueue extends _ModelBase
{
    /**
     * @var \Sooh2\DB\Interfaces\DB
     */
    protected $db;

    public static function getCopy($evtid = '')
    {
        return parent::getCopy(['evtid' => $evtid]);
    }

    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'tb_evtque_0';
    }

    /**
     * 新增一条事件
     * @param string $evt 事件标识
     * @param string $objid 相关ID
     * @param string $uid 用户ID
     * @param string $args 相关参数
     * @param string $ret ret
     * @return bool|int|string
     * @author lingtima@gmail.com
     */
    public static function addOne($evt, $objid, $uid, $args, $ret = '')
    {
        $fields = [
            'evt' => $evt,
            'objId' => $objid,
            'uid' => $uid,
            'args' => $args,
            'ret' => $ret,
        ];
        $BrokerDB = self::getCopy('')->dbWithTablename();
        if ($ret = $BrokerDB->addRecord($BrokerDB->kvobjTable(), $fields)) {
            \Sooh2\Misc\Loger::getInstance()->app_trace($ret);
            return $ret;
        } else {
            return false;
        }
    }
}