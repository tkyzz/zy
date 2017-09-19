<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/15
 * Time: 13:51
 */
namespace Prj\Model\ZyManager;

class SpreadResource extends \Prj\Model\_ModelBase
{
    protected function onInit(){
        $this->className = 'ZyManager';
        parent::onInit();
        $this->_tbName = 't_spread_resource';
    }

    public static function getValidRecordByspreadUrl($spreadUrl){
        return self::getRecord(null , [
            'spreadUrl' => $spreadUrl,
            'state' => 1,
            '!basicId' => [248,249,250]
        ] , 'rsort createTime');
    }
}