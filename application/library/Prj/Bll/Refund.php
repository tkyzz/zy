<?php

namespace Prj\Bll;

/**
 * 回款相关
 * 更新文件
 * \Prj\Model\_ModelBase
 *
 * Class Refund
 * @package Prj\Bll
 */
class Refund extends \Prj\Bll\_BllBase {

   public function calendarTpl($params = []){
        $data = [
            'currentMonth' => '2017-01',
            'cashMonths' => [
                '2017' => [1,3,5],
                '2018' => [2]
            ],
            'cashList' => [
                [
                    'productName' => '新手标1期',
                    'capitalAmount' => '10000',
                    'incomeAmount' => '100',
                    'state' => 'holding',
                    'cashDate' => '2017-01-07',
                ],
                [
                    'productName' => '新手标1期',
                    'capitalAmount' => '10000',
                    'incomeAmount' => '100',
                    'state' => 'closed',
                    'cashDate' => '2017-01-08',
                ]
            ]
        ];
        return $this->resultOK($data);
   }

    /**
     * Hand 还款日历
     * 说明 默认显示 最近待回月份 > 最近已回月份 > 当前月
     * @param array $params
     * @return array
     */
    public function calendar($params = []){
        $params['month'] = isset($params['month']) ? $params['month'] : '';
        if(!\Lib\Misc\Result::paramsCheck($params , ['userId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $investorRecord = \Prj\Model\MimosaUser::getUserByUcUserId($params['userId']);
        if(empty($investorRecord))return $this->resultError('投资者信息不存在!');
        $investorId = $investorRecord['oid'];
        $dbName = \Prj\Model\User::getDbname();

        $sql = <<<sql
select p.`name` productName, h.totalInvestVolume capitalAmount, h.expectIncome incomeAmount, DATE_ADD(p.durationPeriodEndDate ,INTERVAL 1 DAY) cashDate, h.holdStatus state
from $dbName.t_money_publisher_hold h
LEFT JOIN  $dbName.t_gam_product p on h.productOid = p.oid
where h.investorOid = '$investorId' and p.type = 'PRODUCTTYPE_01' and p.state in ('DURATIONING','DURATIONEND','CLEARING','CLEARED')
AND h.holdStatus NOT IN ('refunded' , 'refunding')
ORDER BY cashDate
sql;

        $records = \Prj\Model\User::query($sql);

        $currentMonth = date('Y-m'); //当前月初始化
        $cashMonths = []; //有回款的月份初始化
        $cashList = []; //当前月的回款列表

        $listByDate = []; //回款数据按月份分组

        $closeMonth = ''; //已回月份
        $holdingMonth = ''; //待回月份

        foreach ($records as $v){
            $dataArr = explode('-' , $v['cashDate']);
            list($year , $mon , $day) = $dataArr;
            $listByDate[$year . '-' . $mon][] = $v; //按年月分组
            if(!isset($cashMonths[$year]))$cashMonths[$year] = [];
            if(!in_array($mon , $cashMonths[$year]))$cashMonths[$year][] = (int)$mon; //有还款记录的月份

            if($v['state'] == 'closed'){
                //已回
                $closeMonth = $year . '-' . $mon;
            }else{
                //待回
                if(empty($holdingMonth))$holdingMonth = $year . '-' . $mon;
            }
        }
        $thisMonth = $currentMonth;
        $currentMonth = ($holdingMonth ?: $closeMonth) ?: $currentMonth;
        //如果本月有已回就显示本月
        if($closeMonth == $thisMonth)$currentMonth = $thisMonth;

        if(!empty($params['month']))$currentMonth = date('Y-m' , strtotime($params['month'])); //如果传入了月份就采用,不然使用既定的月份
        $cashList = isset($listByDate[$currentMonth]) ? $listByDate[$currentMonth] : $cashList;
        $this->log($listByDate , '$listByDate');
        return $this->resultOK([
            'currentMonth' => $currentMonth,
            'cashMonths' => $cashMonths,
            'cashList' => $cashList,
        ]);
    }

    public function test($params = []){
        $ret = $this->calendar1($params);
        var_dump($ret);
    }
}
