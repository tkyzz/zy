<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-06 18:22
 */

namespace Prj\Model;

use Sooh2\DB\KVObj;

class PlatformMailType extends _ModelBase
{
//    public static function getCopy($id = null)
//    {
//        if ($id == null) {
//            return parent::getCopy(null);
//        } else {
//            return parent::getCopy(['id' => $id]);
//        }
//    }
    public static $types=array(
        'cash'      =>array('name'=>'回款','where'=>array('%回款%','%提前还款%')),
        'deposit'   =>array('name'=>'充值','where'=>array('%充值%')),
        'invest'    =>array('name'=>'投资','where'=>array('%投资%','%流标%','%计息%')),
        'notice'    =>array('name'=>'通知','where'=>array('%通知%')),
        'redpacket' =>array('name'=>'红包','where'=>array('%红包%')),
        'withdraw'  =>array('name'=>'提现','where'=>array('%提现申请%','%提现到账%')),
    );
    protected function onInit()
    {
        parent::onInit();
        $this->_tbName = 'jz_platform_mail_type';
    }
}