<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/14
 * Time: 20:58
 */
namespace Prj\Bll;

class Td extends _BllBase
{
    /**
     * Hand TD上报信息
     * @param array $params
     * @return array
     */
    public function infoFromTd($params = []){
        $map = [
            'appkey' , 'activietime' , 'osversion' , 'devicetype' , 'idfa' , 'tdid' , 'activieip' , 'spreadurl' , 'spreadname' ,
            'ua' , 'clickip' , 'clicktime' , 'appstoreid' , 'adnetname' , 'channelpackage' , 'other'
        ];
        if(!\Lib\Misc\Result::paramsCheck($params , $map)){
            //可能的参数
        }
        $trackingData = \Prj\Model\TD\TrackingData::getCopy(true);
        $trackingData->load();
        if($trackingData->exists())return $this->resultError('服务繁忙[0]');
        foreach ($map as $v){
            if(isset($params[$v])){
                $trackingData->setField($v , $params[$v]);
            }
        }

        $trackingData->setField('createTime' , date('Y-m-d H:i:s'));
        $ret = $trackingData->saveToDB();
        if(!$ret)return $this->resultError('服务繁忙[1]');
        return $this->resultOK();
    }

    /**
     * Hand 客户端上报信息
     * @param array $params type='register' content={TDID:"",IDFA:""}
     * @return array
     */
    public function infoFromApp($params = []){
        \Prj\Loger::setKv('TD_APP');
        if(!\Lib\Misc\Result::paramsCheck($params , [
            'userId' , 'type' , 'content'
        ])){
            return $this->resultError('参数错误');
        }

        if(!is_array($params['content']))$content = json_decode($params['content'] , true);
        if(empty($content))return $this->resultError('content的结构无法被解析');
        $clientTrans = \Prj\Model\TD\ClientTransparent::getCopy(true);
        $clientTrans->load();
        if($clientTrans->exists())return $this->resultError('服务繁忙[0]');
        $clientTrans->setField('userId' , $params['userId']);
        $clientTrans->setField('type' , $params['type']);
        $clientTrans->setField('content' , $params['content']);
        $ret = $clientTrans->saveToDB();
        if(!$ret)return $this->resultError('服务繁忙[1]');

        //反填
        $res = $this->setContractId($clientTrans);
        return $res;
    }

    protected function setContractId(\Prj\Model\TD\ClientTransparent $clientTrans){
        $content = $clientTrans->getField('content');
        if(!is_array($content))$content = json_decode($content , true);
        $tbid = $content['TDID'];
        $idfa = $content['IDFA'];
        $userId = $clientTrans->getField('userId');

        if(!empty($tbid)){
            $record = \Prj\Model\TD\TrackingData::getLastRecord(['tdid' => $tbid]);
        }else if(empty($idfa)){
            $record = \Prj\Model\TD\TrackingData::getLastRecord(['idfa' => $idfa]);
        }else{
            return $this->resultError('无效的参数content');
        }

        if(empty($record) || empty($record['spreadurl'])){
            //没有匹配到
            $clientTrans->setField('remark' , '未匹配到Track信息');
            $ret = $clientTrans->saveToDB();
            return $this->resultError('未匹配到Track信息');
        }else{
            $contractInfo = \Prj\Model\ZyManager\SpreadResource::getValidRecordByspreadUrl($record['spreadurl']);
            if(empty($contractInfo) || empty($contractInfo['contractId'])){
                //没有匹配到
                $clientTrans->setField('remark' , '未匹配到Contract信息');
                $ret = $clientTrans->saveToDB();
            }else{
                //有匹配信息 更新用户的 contractId
                $user = \Prj\Model\User::getCopy($userId);
                $user->load();
                if(!$user->exists()){
                    $clientTrans->setField('remark' , '[error]用户不存在');
                    \Prj\Loger::out('用户不存在 ' . $userId , LOG_ERR);
                    $ret = $clientTrans->saveToDB();
                    return $this->resultError('用户不存在');
                }
                //更新用户的channelid
                $user->setField('channelid' , $contractInfo['contractId']);
                $ret = $user->saveToDB();
                if(!$ret){
                    $clientTrans->setField('remark' , '[error]用户channelid更新失败');
                    \Prj\Loger::out('用户channelid更新失败' . $userId , LOG_ERR);
                    $ret = $clientTrans->saveToDB();
                    return $this->resultError('用户channelid更新失败');
                }
                //更新final表
                $res = \Prj\Bll\UserFinal::getInstance()->setInfo([
                    'uid' => $userId,
                    'contractId' => $contractInfo['contractId'],
                ]);
                if(!$this->checkRes($res)){
                    \Prj\Loger::out('用户统计更新失败' , LOG_ERR);
                }

                $clientTrans->setField('remark' , 'SUCCESS');
                $ret = $clientTrans->saveToDB();
                if(!$ret){
                    $this->fatalErr('更新失败!!!');
                }else{
                    return $this->resultOK();
                }
            }
        }
    }

    public function test(){
        $params = [
            'appkey' => 'B0C87C85912148A392CE44DDA4508F19',
            'activietime' => '1502784009410',
            'osversion' => '10.0.2',
            'devicetype' => 'iPhone7,2',
            'idfa' => 'B7D2CB57-2C75-46C9-9AB5-0FC1F590C6BC',
            'tdid' => 'ha668f1476a7c0008b976aa2f79f97b26',
            'activieip' => '183.0.215.15',
            'spreadurl' => '4sckYh',
            'appstoreid' => '1203692435',
            'adnetname' => 'toutiao',
            'channelpackage' => 'AppStore',
            'other' => '{"imei_md5":"1e7320ded9c08bd3d4c856abb59add9f","ip":"183.214.197.4","chn":"toutiao","useragent":"","callback":"COWFofj0ARDhsK_49AEYlvL8rvEBIMjf68CMASiIzIb29AEwDDihnAFCIDIwMTcwODE1MTYwMzAyMDEwMDA4MDU5MDMzMTc5MEIw","action":"none","clicktime":"1502784201000","osversion":"0","androidid":"a923b9e899584ff8"}'
        ];

//        $res = $this->infoFromTd($params);
//        var_dump($res);

        $list = \Prj\Model\User::getRecords(null , [] , 'rsort createTime' , 1 , mt_rand(0 , 352));
        $user = $list[0];
        $params = [
            'userId' => $user['oid'],
            'content' => '{"TDID":"ha668f1476a7c0008b976aa2f79f97b26","IDFA":"B7D2CB57-2C75-46C9-9AB5-0FC1F590C6BC"}',
            'type' => 'register'
        ];

        $res = $this->infoFromApp($params);
        var_dump($res);

        $user = \Prj\Model\User::getCopy($user['oid']);
        $user->load();
        var_dump($user->getField('channelid'));

        $user->load(true);
        var_dump($user->getField('channelid'));
    }
}