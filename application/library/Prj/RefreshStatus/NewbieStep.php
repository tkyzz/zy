<?php

namespace Prj\RefreshStatus;
use Prj\Loger;

/**
 * 获取新手引导的相关状态
 * Class NewbieStep
 * @package Prj\RefreshStatus
 */
class NewbieStep extends Basic{

    protected function getNodeData($uid)
    {
        \Prj\Loger::setKv('`_`');
        Loger::outVal("uid:",$uid);
        $user = \Prj\Bll\Newbie::getInstance($uid);
        //查询状态
        try{
            $stepDetail['register']['status'] = !empty($uid) ? 1 : 0;
            $stepDetail['bindcard']['status'] = $user->checkBind();
            $stepDetail['charge']['status'] = $user->checkRecharge() ? 1 : 0;
            $stepDetail['buy']['status'] = $user->checkBuy() ? 1 : 0;
        }catch (\Exception $e){
            \Prj\Loger::out($e->getMessage());
            return [];
        }

        //奖励
        $coupon['type'] = 'coupon';
        $newbieStepbonus = \Prj\Bll\ActivityConfig::getInstance()->getActiveScheme('新手引导');
        $newbieList = \Rpt\Manage\ManageActivitySchemeConfig::getListByBASE64(bin2hex(json_encode($newbieStepbonus['id'])));
        foreach ($newbieList as $k=>$v){
            if($v['flag'] == 'signin_register_bonus') $stepDetail['register']['bonus'] = !empty($v['value'])?$coupon['type']."_".$v['value']:'';
            if($v['flag'] == 'signin_bindingCard_bonus') $stepDetail['bindcard']['bonus'] = !empty($v['value'])?$coupon['type']."_".$v['value']:'';
            if($v['flag'] == 'signin_recharge_bonus') $stepDetail['charge']['bonus'] = !empty($v['value'])?$coupon['type']."_".$v['value']:'';
            if($v['flag'] == 'signin_investment_bonus') $stepDetail['buy']['bonus'] = !empty($v['value'])?$coupon['type']."_".$v['value']:'';
        }

        //下一步的步骤
        switch (true){
            case $stepDetail['buy']['status'] : $NewbieStepNext = '';break;
            case $stepDetail['charge']['status'] && (!$stepDetail['bindcard']['status'] || $stepDetail['bindcard']['status'] == 2) : $NewbieStepNext = 'bindcard';break;
            case $stepDetail['charge']['status'] && $stepDetail['bindcard']['status'] : $NewbieStepNext = 'buy';break;
            case $stepDetail['bindcard']['status'] : $NewbieStepNext = 'charge';break;
            case $stepDetail['register']['status'] : $NewbieStepNext = 'bindcard';break;
            case !$stepDetail['register']['status'] : $NewbieStepNext = 'register';break;
            default : $NewbieStepNext = '';
        }
        //排序的权重
        $stepDetail['buy']['weight'] = 1;
        $stepDetail['charge']['weight'] = 2;
        $stepDetail['bindcard']['weight'] = 3;
        $stepDetail['register']['weight'] = 4;

//        $stepDetail['buy']['status'] = 1;
//        $stepDetail['charge']['status'] = 1;
//        $stepDetail['bindcard']['status'] = 0;
//        $stepDetail['register']['status'] = 1;
        //排序数据
        foreach ($stepDetail as $k => $v){
            if($v['status'] > 0)$stepDetail[$k]['weight'] += 10;
        }
        $sordData = \Lib\Misc\ArrayH::rdsort2d($stepDetail , 'weight');

        //拼装数据
        foreach ($sordData as $k => $v){
            $tmp = $v;
            $tmp['step'] = $k;
            unset($tmp['weight']);
            $NewbieStep[] = $tmp;
        }

        $data = compact('NewbieStep' , 'NewbieStepNext');

        //活动图标
        $acticityName = "活动图标";
        $activityList = \Prj\Bll\ActivityConfig::getInstance()->getActiveScheme($acticityName);
        $logo_list = \Rpt\Manage\ManageActivitySchemeConfig::getListByBASE64(bin2hex(json_encode($activityList['id'])));

        if($logo_list){
            foreach($logo_list as $k => $v){
                if($v['flag'] =='signin_logo_change'){
                    if(trim($v['value'])){
                        $data['floatnewbieicon']['action'] = 'change';
                        continue;
                    }else{
                        $data['floatnewbieicon'] = array('action'=>'default','icon'=>'','url'=>'');
                        break;
                    }
                }
                if($v['flag'] == 'signin_logo_icon') $data['floatnewbieicon']['icon'] = "http://".$_SERVER['HTTP_HOST'].$v['value'];
                if($v['flag'] == 'signin_logo_url') $data['floatnewbieicon']['url'] = $v['value'];
            }
        }else{
            $data['floatnewbieicon'] = array('action'=>'default','icon'=>'','url'=>'');
        }
        return $data;
    }
}
