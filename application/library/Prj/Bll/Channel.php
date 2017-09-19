<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/10
 * Time: 13:47
 */
namespace Prj\Bll;
use Prj\Loger;
use Sooh2\Misc\Ini;

class Channel extends _BllBase
{
    public $channelPC = [1];
    public $channelApp = [3,4];
    public function getChannelId($contractId='',$versionId=''){
        $app = \Sooh2\Misc\Ini::getInstance()->getIni("channelConfig");


        if(!empty($versionId)&&!empty($app)){
            foreach($app as $k => $v){

                if($v['version'] == $versionId&&$contractId == $v['contractId']) $contractId = $v['redirect'];
            }
        }

        $channelId = substr($contractId,0,4);
        $count = \Prj\Model\ZyBusiness\ProductPublishChannel::getCount(['channelId'=>$channelId]);
        if(!$count){
            $channelId = substr($contractId,12,1);
            $secondCount = \Prj\Model\ZyBusiness\ProductPublishChannel::getCount(['channelId'=>$channelId]);

            if(!$secondCount) {
                $channelId = 3;
                Loger::out("此渠道无产品数据，已转为默认渠道3，表示ios端");
            }
        }

        return $channelId;
    }




    public function getBannerChannel($contractId){
        if(in_array($contractId,$this->channelPC)){
            $params['code'] = ['PC','pc','Pc'];
        }else{
            $params['code'] = ['App','APP','app'];
        }
        $channelInfo = \Prj\Model\CmsChannel::getRecord("oid",$params,'rsort updateTime');
        return $channelInfo['oid'];
    }
}