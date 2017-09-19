<?php


namespace Prj\RefreshStatus;

/**
 * 获取用户信息
 *
 * @author simon.wang
 */
class MyHome extends Basic
{
    /**
     * Hand 用户的投资统计信息
     * @param $uid
     * @return array|null
     */
    protected function getNodeData($uid)
    {
        if (!$uid) {
            return null;
        }
        $baseAccount=\Prj\Model\MimosaUser::getUserByUcUserId($uid);
        $investorOid=$baseAccount['oid'];
        $balance=$baseAccount['balance'];
        $row=\Prj\Model\MimosaStatistics::getUserByMimosaId($investorOid);
        $arr=array();
        $arr['totalIncomeAmount']=$row['totalIncomeAmount']; //累计收益
        $arr['t0CapitalAmount']=$row['t0CapitalAmount']; //活期金额
        $arr['tnCapitalAmount']=$row['tnCapitalAmount']; //定期金额
        $arr['t0YesterdayIncome']=$row['t0YesterdayIncome']; //活期昨日收益
        $arr['balance']=$balance; //账户余额
        $arr['onWayBalance']=0; //冻结金额
        $arr['rateCouponIncomeAmount'] = 0; //加息收益
        $arr['totalIncomeAmount'] = 0; //累计收益
        $arr['capitalAmount']= $arr['t0CapitalAmount']+$arr['tnCapitalAmount']+$arr['balance']+$arr['onWayBalance']; //资产总额
        return $arr;
    }


}
