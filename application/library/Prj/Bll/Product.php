<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Prj\Bll;

use function GuzzleHttp\Psr7\str;
use \Lib\Misc\Result;
use \Prj\Loger;
use Prj\Model\ZyBusiness\SystemCalendar;
use Prj\Model\CmsChannel;
use Prj\Model\UcUser;
use Prj\Model\ZyBusiness\PlatformChannel;
use Prj\Model\ZyBusiness\ProductInfo;
use Sooh2\Misc\Ini;

/**
 * Description of User
 *
 * @author simon.wang
 */
class Product extends _BllBase
{
    const APP = "APP";
    const PC = "PC";

    const state_newbie = 1; // 新手标
    const state_buy = 3;  // 募集中
    const state_full = 6;  // 已募集满
    const state_que = 5;  // 待售
    const state_topay = 7;  // 存续期，还款中
    const state_done = 8;  // 已还款

    public function checkProductLabel($productId, $labelName)
    {
        $labelInfo = \Prj\Model\MimosaLabel::getOneByLabelName($labelName);
        if (empty($labelInfo)) return Result::get(RET_SUCC, '标签不存在!', ['check' => false]);
        if ($labelInfo['isOk'] != 'yes') return Result::get(RET_SUCC, '标签未启用!', ['check' => false]);
        $productLabel = \Prj\Model\MimosaLabelProduct::getOne([
            'labelOid' => $labelInfo['oid'],
            'productOid' => $productId,
        ]);
        if (empty($productLabel)) {
            return Result::get(RET_SUCC, '产品不含此标签!', ['check' => false]);
        } else {
            return Result::get(RET_SUCC, '', ['check' => true]);
        }
    }


    public function getList($where = [], $rows = '', $page = '')
    {

        $rsForm = isset($page) ? ($page - 1) * $rows : '';

        if (isset($params['status'])) $where['productStatus'] = $params['status'];
        $list = \Prj\Model\Product::getRecords('*', $where, "sort weight", $rows, $rsForm);
//       $total = \Prj\Model\Product::getRecord("count(*) as total",$where)['total'];
        return $list;
    }

    public function getTotal($where = [])
    {
        $total = \Prj\Model\Product::getRecord("count(*) as total", $where)['total'];
        return $total;
    }




    /*
     * 获取产品详情
     * $params['productId'] string     产品id号
     * $params['type']  string      产品类型，是定期还是活期*/
    public function getProductDetail($params)
    {
        if (!Result::paramsCheck($params, ['type'])) {
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        //当type等于CURRENT时为活期详情，如果等于FIXED时为定期详情

        if (strtoupper($params['type']) == "CURRENT") {
            $where['weight'] = 0;
            if(!empty($params['productId'])) $where['productId'] = $params['productId'];

        } else {
            $where['productId'] = $params['productId'];
            $where['!weight'] = 0;
        }

        $detailInfo = \Prj\Model\Product::getRecord("detailJson", $where,'rsort productId');


        return $this->resultOK(['content' => $detailInfo['detailJson']]);


    }


    public function getProductDetailTpl($params)
    {
        $info = \Prj\Model\ProductDetailTpl::getRecord("*", $params, 'rsort updateTime');
        return $info;
    }

    /*
     * 根据券筛选定期列表
     * $params['labels']    array   标签数组*/
    public function getListByLabel($params)
    {
        if (!Result::paramsCheck($params, ['labels'])) {
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $labelStr = "";
        sort($params['labels']);
        foreach ($params['labels'] as $v) {
            $labelStr .= "%," . $v . ",%";
        }
        $where['*labels'] = $labelStr;
        $where['!weight'] = 0;
        $list = $this->getList($where, $params['rows'], $params['page']);
        $total = $this->getTotal(['!weight' => 0]);
        return $this->resultOK([
            'content' => $list['listJson'],
            'size' => $params['rows'],
            'total' => $total,
            'totalPages' => ceil($total / $params['rows']),
        ]);
    }




    public function getIndexProductList($params)
    {

        if (!Result::paramsCheck($params, ['channelOid', 'isNew'])) {
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }

        $list = [];
        $parameters = [
            'channelId' => $params['channelOid'],
            'isUsed' => 1
        ];
        $productIdList = \Prj\Bll\Product::getInstance()->getProductByChannel($parameters);
        if (empty($productIdList)) return $this->resultError("此渠道暂无商品");
        $channelInfo = $this->getChannelInfo($params['channelOid']);
        if (empty($channelInfo)) return $this->resultError("此渠道不可用");
        $productIdList = array_column($productIdList, 'productId');
        $notAllowedStatus = ['STARTUP','CREATE','PENDING','PASSED','REBUTED','REVIEWED','REFUSED'];

        $data = $this->getList(['productId' => $productIdList,'productStatus'=>['RAISING','DURATIONING']]);
        $userOid = $this->getUidInSession();
        $flag = false;
        $count = 0;
        $hasNewbie = false;
        if (empty($userOid) || $params['isNew']) {//未登录、新手:第1个为新手标,第2个为活期,另外选取定期列表页前2个（不含位序1的新手标）

            foreach ($data as $k => $v) {

                if (substr($v['weight'], 0, 1) == 1 && !$hasNewbie) {
                    if($v['productStatus']!='RAISING'&&$v['productStatus']!='DOING_RAISING') continue;
                    $list[0] = $v['listJson'];
                    $hasNewbie = true;

                    continue;
                }
                if (substr($v['weight'], 0, 1) == 0 && !$flag) {
                    $list_json = json_decode($v['listJson'],true);
                    $labelList = $this->sortLabels($list_json['labelList']);
                    $list_json['labelList'] = $labelList;
                    $list[1] = json_encode($list_json);
                    $flag = true;
                    continue;

                }
                if ($v['weight'] > 0 && $count < 2 && substr($v['weight'], 0, 1) > self::state_newbie) {
                    if($v['productStatus']!='RAISING'&&$v['productStatus']!='DOING_RAISING') continue;
                    $list_json = json_decode($v['listJson'],true);

                    $labelList = $this->sortLabels($list_json['labelList']);

                    $list_json['labelList'] = $labelList;
                    $list[$count + 2] = json_encode($list_json);
                    $count++;
                    continue;
                } elseif ($count >= 2) {
                    break;
                }

            }


        } else {      //非新手:第一个为活期,另外选取定期列表页前3

            foreach ($data as $k => $v) {
                if ($v['weight'] == 0 && !$flag) {
                    $flag = true;
                    $list[0] = $v['listJson'];
                    continue;
                }
                if ($v['weight'] > 0 && $count < 3 && substr($v['weight'], 0, 1) > self::state_newbie) {
                    if($v['productStatus']!='RAISING'&&$v['productStatus']!='DOING_RAISING') continue;
                    $list[$count + 1] = $v['listJson'];
                    $count++;
                    continue;
                } elseif ($count >= 3) {
                    break;
                }


            }
        }

        ksort($list);
        return $this->resultOK(['content' => $list]);
    }


    public function getTradeCalender($calender, $investDaysType = 'trad')
    {
        if (strtoupper($investDaysType) == 'NORMAL') { //20170916 Hand
            $time = date("Y-m-d", strtotime($calender . " + 1 day"));

            return $time;
        } else {
            $nextCal = \Prj\Model\ZyBusiness\SystemCalendar::getRecord("calendarDate", ['prevWorkDate' => $calender, 'isWork' => 1]);
            return $nextCal['calendarDate'];
        }

    }


    public function getRegularCalender($calender,$investDaysType = 'trad'){
        switch ($investDaysType){
            case "NORMAL":
                $day = $time = date("Y-m-d", strtotime($calender . " + 2 day"));break;
            case "WORK":
                $nextCal = \Prj\Model\ZyBusiness\SystemCalendar::getRecord("calendarDate", ['prevWorkDate' => $calender, 'isWork' => 1])['calendarDate'];
                $day = \Prj\Model\ZyBusiness\SystemCalendar::getRecord("calendarDate", ['prevWorkDate' => $nextCal, 'isWork' => 1])['calendarDate'];break;
            case "TRAD":
                $nextCal = \Prj\Model\ZyBusiness\SystemCalendar::getRecord("calendarDate", ['prevTradeDate' => $calender, 'isTrade' => 1])['calendarDate'];
                $day = \Prj\Model\ZyBusiness\SystemCalendar::getRecord("calendarDate", ['prevTradeDate' => $nextCal, 'isTrade' => 1])['calendarDate'];break;

        }
        return $day;

    }


    public function getChannelInfo($channelId)
    {
        $params = [
            'channelId' => $channelId,
            'isUsed' => 1
        ];
        $platformChannel = \Prj\Model\ZyBusiness\PlatformChannel::getRecord("*", $params, 'rsort updateTime');
        return $platformChannel;
    }


    public function getChannelInfoCopy($channelId)
    {
        $params = [
            'channelNo' => $channelId,
            'isUsed' => 1
        ];
        $platformChannel = \Prj\Model\ZyBusiness\PlatformChannel::getRecord("*", $params, 'rsort updateTime');
        return $platformChannel;
    }


    public function getProductByChannel($params)
    {

        $product = \Prj\Model\ZyBusiness\ProductPublishChannel::getRecords("*", $params, 'rsort updateTime');
        return $product;
    }


    public function getProductList($params)
    {
        if (!Result::paramsCheck($params, ['productId', 'rows', 'page'])) {
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
//        $channel = CmsChannel::getRecord("code",['oid'=>$params['channelOid']])['code'];
//        if($channel == self::PC){
        if (!empty($params['durationPeriodDays'])) $where['durationPeriodDays'] = $params['durationPeriodDays'];
        if (!empty($params['interestTotal'])) $where['interestTotal'] = $params['interestTotal'];
//        }
        $where['!weight'] = 0;
        $where['!productStatus'] = ['STARTUP','CREATE','PENDING','PASSED','REBUTED','REVIEWED','REFUSED'];
        $where['productId'] = $params['productId'];
        $data = $this->getList($where, $params['rows'], $params['page']);
        $list = array();
        $hasNewbie = false;


        foreach ($data as $k => $v) {

            if (substr($v['weight'], 0, 1) == self::state_newbie && !$hasNewbie) {
                $listJson = json_decode($v['listJson'],true);
                $labelList = $this->sortLabels($listJson['labelList']);
                $listJson['labelList'] = $labelList;
                array_unshift($list,$listJson);
                $hasNewbie = true;

            } elseif (substr($v['weight'],0,1)>1){
                $listJson = json_decode($v['listJson'],true);
                $listJson['labelList'] = $this->sortLabels($listJson['labelList']);
                ;
                $list[] = $listJson;
            }


        }


        $durationPeriodDaysList = \Prj\Model\Product::getRecords("distinct(durationPeriodDays) as durationPeriodDays,left(durationPeriodDays,1) as type", ['!weight' => 0,'productId'=>$params['productId']], "sort left(durationPeriodDays,1)");
        Loger::outVal("durata",$durationPeriodDaysList);
//        foreach ($durationPeriodDaysList as $k => $v) {
//            $durationPeriodDays[] = sprintf("%4b",$v['durationPeriodDays']);
//        }
        $durationPeriodDays = array_column($durationPeriodDaysList,'durationPeriodDays');
        $durationPeriodDays = $this->sortDuration($durationPeriodDays);
        $interestTotalList = \Prj\Model\Product::getRecords("interestTotal", ['!weight' => 0,'productId'=>$params['productId']], "group interestTotal sort interestTotal");
        foreach ($interestTotalList as $k => $v) {
            $interestTotal[] = $v['interestTotal'];
        }

//        }

        $total = $this->getTotal($where);

        $datas = [
            'content' => $list,
            'size' => $params['rows'],
            'total' => $total,
            'totalPages' => ceil($total / $params['rows']),
        ];


        if (!empty($durationPeriodDays)) $datas['durationPeriodDays'] = $durationPeriodDays;
        if (!empty($interestTotal)) $datas['interestTotal'] = $interestTotal;
        return $this->resultOK($datas);
    }


    /*
     * 判断用户未登录或是否是新手*/
    public function getUserTiro()
    {
        $userId = $this->getUidInSession();

        if (empty($userId)) return 1;
        $info = \Prj\Model\UserFinal::getRecord("isTiro", ['uid' => $userId]);
        Loger::outVal("istiro",$info);
        if (empty($info)) return 1;
        return $info['isTiro'];

    }


    public function sortDuration($durations){
        $data1 = $data2 = [];
        foreach ($durations as $k => $v){
            switch (substr($v,0,1)){
                case 1:$data1[] = $v;
                    break;
                case 2:
                    $data2[] = $v;break;
            }
        }
        sort($data1);
        sort($data2);
        $data = array_merge($data1,$data2);
        return $data;
    }


    /**
     *获取用户登陆信息
     */
    protected function getUidInSession($userOid = null)
    {
        if (!empty($userOid)) return $userOid;
        return \Prj\Session::getInstance()->getUid();
    }


    public $OneMing = "R";
    public function getInvestRecord($params){
        if (!Result::paramsCheck($params, ['productId', 'rows', 'page'])) {
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $labelId = \Prj\Model\ZyBusiness\SystemLabel::getRecord("labelId",['labelName'=>"一鸣惊人"])['labelId'];
        $hammerId = \Prj\Model\ZyBusiness\SystemLabel::getRecord("labelId",['labelName'=>"一锤定音"])['labelId'];
        $fields = "orderId,userPhone,createTime,orderAmount,activityTypes";
        $investRecord = [
            'productId' =>  $params['productId'],
            'orderType' =>  "INVEST",
            'orderStatus'  =>  "CONFIRMED"
        ];
        $productRecord = \Prj\Model\Product::getRecord("*",['productId'=>$params['productId'],'*labels'=>"%,".$labelId.",%"]);

        $product = \Prj\Model\Product::getRecord("productStatus,weight",['productId'=>$params['productId']]);
        $total = \Prj\Model\ZyBusiness\TradOrder::getCount($investRecord);
        $rsForm = isset($params['page']) ? ($params['page'] - 1) * $params['rows'] : '';
        if(!empty($productRecord)){

            $maxRecord = \Prj\Model\ZyBusiness\TradOrder::getRecord($fields,$investRecord,'rsort orderAmount rsort createTime');

//            $investRecord['!productId'] = $productRecord['productId'];
            $investRecord['!orderId'] = $maxRecord['orderId'];
            $productList = \Prj\Model\ZyBusiness\TradOrder::getRecords($fields,$investRecord,'rsort createTime',$params['rows'],$rsForm);

            if(empty($maxRecord['activityTypes'])){
                $maxRecord['activityTypes'] = "famous";
            }elseif ($maxRecord['activityTypes'] == 'hammer'){
                $maxRecord['activityTypes'] = $maxRecord['activityTypes'].",famous";
            }

            array_unshift($productList,$maxRecord);
        }else{
            $productList = \Prj\Model\ZyBusiness\TradOrder::getRecords($fields,$investRecord,'rsort createTime',$params['rows'],$rsForm);
        }
        foreach($productList as $k => $v){
            $productList[$k]['userPhone'] = substr_replace($v['userPhone'],'****',3,4);
            $productList[$k]['createTime'] = strtotime($v['createTime'])."000";
        }

        $hammerRecord = \Prj\Model\Product::getRecord("*",['productId'=>$params['productId'],'*labels'=>"%,".$hammerId.",%"]);
        $hammer = false;
        if(!empty($hammerRecord)) $hammer = true;
        if(!$product['weight']){
            $titleData = ["title"=>"","content"=>""];
        }else{
            $titleData = $this->getActivityTitle($productList,$product['productStatus'],$hammer,$total);
        }

        $data = [
            'documents' =>  $titleData,
            'content' => $productList,
            'size' => $params['rows'],
            'total' => $total,
            'totalPages' => ceil($total / $params['rows']),
        ];
        return $this->resultOK($data);

    }



    public function getActivityTitle($arr,$productStatus,$isHammer,$count){
        $first = current($arr);
        switch($productStatus){
            case "RAISING":

                $data['title'] = '已认购'.$count."笔";

                break;
            case "DURATIONING":
                $data['title'] = '已认购'.$count."笔";

                break;
            default:$data['title'] = "已募集结束";

                break;
        }
        if(strpos($first['activityTypes'],'hammer,famous')!==false){

            $data['content'] = "项目结束后，此项目单笔投资最高者及满标者可获惊喜红包";

        }elseif (strpos($first['activityTypes'],'famous')!==false){
            if($isHammer){
                $data['content'] = "项目结束后，此项目单笔投资最高者及满标者可获惊喜红包";
            }else{
                $data['content'] = "项目结束后，此项目单笔投资最高者可获惊喜红包";
            }

        }elseif (strpos($first['activityTypes'],'hammer')!==false||$isHammer){
            $data['content'] = "项目结束后，此项目满标者可获惊喜红包";
        }else{
            $data['title'] = '';
            $data['content'] = "";
        }
        return $data;

    }


    public function sortLabels($labels){
        $listLabel = [];
        foreach($labels as $key => $label){
            if($label['labelType'] != 'general'){
                if($label['labelName'] == '一锤定音'||$label['labelName'] == "一鸣惊人"){
                    array_unshift($listLabel,$label);

                }else{
                    array_push($listLabel,$label);
                }
            }

        }
        return $listLabel;
    }



    public function switchStatus($productStatus){
        switch ($productStatus){
            case "DOING_RAISING": $status = "RAISING";break;
            case "RAISING":$status = "RAISING";break;
            case "":$status = "RAISING";break;
            default: $status = "RAISED";break;
        }
        return $status;
    }







}
