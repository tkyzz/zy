<?php


namespace Prj\RefreshStatus;

/**
 * 获取用户信息
 *
 * @author simon.wang
 */
class UserInfo extends Basic
{

    protected function getNodeData($uid)
    {
        if (!$uid) {
            return [];
        }
        $data['userOid']=$uid;
        $user=\Prj\Model\User::getRecord('*',['oid'=>$uid]);
        $data['source']=$user['source'];
        $data['userAcc']=$user['userAcc'];
        $data['isLogin']=true;
        $data['userPwd']= !(empty($user['userPwd'])) ? true : false;
        $data['createTime']=$user['createTime'];
        $data['sceneId']=$user['sceneId'];
        $data['payPwd'] = !(empty($user['payPwd'])) ? true : false;
        $data['channelid']=$user['channelid'];
        $data['memberOid']=$user['memberOid'];
        $data['status']=$user['status'];
        return $data;
    }
}
