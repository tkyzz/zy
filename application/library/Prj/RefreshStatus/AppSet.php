<?php


namespace Prj\RefreshStatus;


/**
 * APP设置
 *
 * @author simon.wang
 */
class AppSet extends Basic
{
    /**
     * Hand App设置
     * @param $uid
     * @return array
     */
    protected function getNodeData($uid)
    {
        /** @var \Yaf_Request_Abstract $arr */
        global $req;
        $channelId = $req->get("channelId");
        $version = $req->get("version");
        $channelId = \Prj\Bll\Channel::getInstance()->getChannelId($channelId,$version);
        $params = [
            'channel'   =>  $channelId,
            'status'    =>  1
        ];
        $appAsset = \Prj\Model\AppAsset::getRecord("*",$params);
        if(empty($appAsset)){
            $params['channel'] = '*';
            $appAsset = \Prj\Model\AppAsset::getRecord("*",$params);
        }
        $appAssetConfig = json_decode($appAsset['config'],true);
        unset($appAssetConfig['hasAD']);unset($appAssetConfig['img']);
        unset($appAssetConfig['url']);unset($appAssetConfig['duration']);
        unset($appAssetConfig['refreshNotice']);

        return $appAssetConfig;
    }


}
