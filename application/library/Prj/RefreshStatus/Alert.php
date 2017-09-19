<?php
/**
 * APP 弹窗配置
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/29
 * Time: 10:08
 */
namespace Prj\RefreshStatus;

class Alert extends Basic
{
    public function getNodeData($uid)
    {
        $isCheckin = \Prj\Bll\User::getInstance()->isCheckin($uid);
        $data = [];
        if(!$isCheckin)$data = [
            [
                'id' => 'Checkin',
                'type' => 'Checkin',
                'img' => '',
                'url' => ''
            ]
        ];
        if( $uid ){
            $res = json_decode(\Prj\Model\DataTmp::getRecord("*",['type'=>'alert'])['value'],true);
            $data[] = [
                'id' => 'Activity',
                'type' => 'Activity',
                'img' => reset($res)['img'],
                'url' => reset($res)['url'],
            ];
        }

        return $data;
    }

}