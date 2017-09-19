<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/24
 * Time: 10:40
 */

namespace Prj\Bll;

use Prj\Bll\_BllBase;
use Prj\Loger;
use Prj\Model\Asset\Asset;
use Prj\Model\Asset\AssetPortfolioHold;
use Prj\Model\Asset\AssetSupplyChain;
use Prj\Model\Asset\Dict;
use Prj\Model\Asset\Portfolio;
use Prj\Model\Asset\PortfolioProduct;
use Prj\Model\Asset\SystemCalendar;
use Prj\Model\ZyBusiness\ProductLimit;
use Prj\Model\Product;



class CheckProduct extends _BllBase{
    const Limit = 9999999999;
    const state_newbie=1; // 新手标
    const state_buy = 2;  // 募集中
    const state_startup = 9;
    const state_full= 3;  // 已募集满
    const state_end = 4;  // 募集结束
    const state_blow = 5;    //流标中
    const state_doing = 6;  //成立中、打款中
    const state_topay=7;  // 存续期，还款中
    const state_done= 8;  // 已还款
    const FIX = "REGULAR"; //定期
    const CURRENT ="CURRENT";    //活期
    public function crond(){
        \Prj\Loger::$prefix = '[crond]';
//        if(!\Prj\Tool\Debug::isTestEnv()){
        if(!$this->getProductCount()){
            Loger::out("没有商品信息");
            return;
        }
//        }
        \Prj\Loger::out('crond start...');
        $this->insertProduct(5);
    }
    protected $productList = [];



    /*
     * 标准化产品详情数据
     * @param   array   $info   从其他库中获取到的产品详情数据
     * @param   int     $productId 产品id号
     * @param   array   $assetCate  资产分类信息数据
     * @param   array   $assetInfo  资产信息数据*/
    public function standardDetailFormat($info,$product,$assetCate,$assetInfo,$assetAllCate){
        return [
            'oid'         =>  $product['productOid'],
            'type'        =>    $product['type'],
            'state'         =>  $product['state'],
            'productCode'   =>  $product['productNo'],
            'productName'   =>  $product['name'],
            'productFullName'   =>  $product['name'],
            'expAror'       =>  $product['expArorSec'],
            'rewardInterest'      =>  $product['rewardInterest'],
            'collectedVolume'   =>  $product['collectedVolume'],
            'raisedTotalNumber' =>  $product['raisedTotalNumber'],
            'investMin'         =>  $product['investMin'],
            'tenThousandIncome'   =>  $product['tenThousandIncome'],
            'raisePeriodDays'   =>  !empty($product['raisePeriodDays'])?$product['raisePeriodDays']:0,   //募集期
            'remainMoney'       =>  $product['remainMoney'],
            'interestsDays'     =>  !empty($info['interestsDays'])?$info['interestsDays']:0,   //活期起息日
            'interestsType'     =>  $info['interestsType'],
            'labelList'         =>  $product['labelList'],
            'increaseInvestAmount'  =>  $product['increaseInvestAmount'],
            'incomeCalcBasis'    =>  $product['incomeCalcBasis'], //年计息天数
            'redeemDays'        =>  !empty($info['redeemDays'])?$info['redeemDays']:0,
            'clearDays'         =>  !empty($info['clearDays'])?$info['clearDays']:0,
            'percent'           =>  $product['percent'],
            'investAdditional'  =>  $product['increaseInvestAmount'],
            'clearDays'         =>  !empty($info['clearDays'])?$info['clearDays']:0,
            'clearType'         =>  $info['clearType'],
            'investTime'        =>  date('Y-m-d'),
            'repayDate'      =>  $product['payBackDate'],
            'maxSaleVolume'     =>  $product['maxSaleVolume'],
            'durationEndTime'   =>  $product['durationEndTime'],
//            'raisePeriodType'   =>  $info['raisePeriodType'],   //募集期类型
            'maxTotalAmount'    =>  $product['maxTotalAmount'],
            'netUnitShare'      =>  1,  //理财计划份额
            'AssetAllCate'      =>  $assetAllCate,
            'durationBegTime'   =>  $product['durationBegTime'],
            'payBackDate'       =>  $product['payBackDate'],
            'redeemDaysType'    =>  $product['redeemDaysType'],
            'investDaysType'    =>  $product['investDaysType'],
            'durationPeriodDays'    =>  !empty($product['durationPeriodDays'])?$product['durationPeriodDays']:0,
            'durationPeriodType'    =>  $product['durationPeriodType'], //募集期类型,
            'AssetInfo'         =>  $assetInfo,
//            'financerCapital'   =>  isset($assetInfo['financerCapital'])?$assetInfo['financerCapital']:'',
//            'financer'          =>  isset($assetInfo['financer'])?$assetInfo['financer']:'',
//            'financerDesc'      =>  isset($assetInfo['financerDesc'])?$assetInfo['financerDesc']:'',
//            'warrantor'         =>  isset($assetInfo['warrantor'])?$assetInfo['warrantor']:"",
//            'warrantorDesc'     =>  isset($assetInfo['warrantorDesc'])?$assetInfo['warrantorDesc']:"",
//            'usages'            =>  isset($assetInfo['usages'])?$assetInfo['usages']:'',
//            'repaySource'       =>  isset($assetInfo['repaySource'])?$assetInfo['repaySource']:'',
//            'risk'              =>  isset($assetInfo['risk'])?$assetInfo['risk']:'',
            'assetCate'         =>  isset($assetCate['dictId'])?$assetCate['dictId']:'',
            'assetCateName'     =>  isset($assetCate['name'])?$assetCate['name']:'',
//            'borrower'          =>  isset($assetInfo['borrower'])?$assetInfo['borrower']:'',
//            'drawer'            =>  isset($assetInfo['drawer'])?$assetInfo['drawer']:'',
            'payBackType'       =>  $product['payBackType'],
            'investCompactFile' =>  $info['investCompactFile'],
            'riskWarnFile'      =>  $info['riskWarnFile'],
            'durationBegTime' =>  $product['expectRaisOffDate']
        ];
    }


    /*标准化产品列表数据
    @param  array   $info   从其他库产品表中获取到的数据
    @param  json    $label  标签json串
    @param  string $percent   出售百分百
    @remainMoney  string    $remainMoney    剩余金额  */
    public function standardProductList($info,$label,$percent,$remainMoney){
        return [
            'productOid'            =>  $info['productId'],
            'productNo'             =>  $info['productNo'], //产品编号
            'productType'           =>  $info['productType'],
            'type'                  =>  'PRODUCTTYPE_01',
            'name'                  =>  $info['productName'],
            'investMin'             =>  $info['minSingleInvestAmount'],
            'expAror'               =>  sprintf('%.2f',$info['baseRate']),//基础收益率
            'activityRate'          =>  sprintf("%.2f",$info['activityRate']),   //活动收益率
            'rewardInterest'        =>  sprintf("%.2f",$info['rewardRate']),
            'expArorSec'            =>  sprintf("%.2f",$info['baseRate']),
            'expArrorDisp'          =>  sprintf('%.2f',$info['baseRate']),
            'raisePeriodDays'       =>  $info['raisePeriodDays'],
            'incomeCalcBasis'    =>  $info['incomeCalcBasis'],
            'increaseInvestAmount'  =>  $info['increaseInvestAmount'],
            'remainMoney'           =>  sprintf("%.2f",$remainMoney),   //剩余可售金额
            'percent'               =>  sprintf("%.2f",$percent),  //已售百分比
            'state'                 =>  $info['productStatus'], // 产品状态
            'maxTotalAmount'        =>  $info['maxTotalInvestAmount']?$info['maxTotalInvestAmount']:sprintf("%.2f",$info['raiseAmount']),
            'maxSaleVolume'         =>    $info['maxSingleInvestAmount']?(($info['maxSingleInvestAmount']>$remainMoney)?$remainMoney:$info['maxSingleInvestAmount']):sprintf("%.2f",$remainMoney),
            'increaseAmount'        =>  sprintf("%.2f",$info['increaseAmount']),    //投资追加份额
            'raisedTotalNumber'     =>  sprintf("%.2f",$info['raiseAmount']),       //募集份额
            'stateDisp'             =>  null,
            'durationBegTime'       =>  $info['durationBegTime'],
            'durationEndTime'       =>  $info['durationEndTime'],
            'increaseInvestAmount'  =>  sprintf("%.2f",$info['increaseInvestAmount']),
            'payBackType'           =>  $info['payBackType'],
            'stateOrder'            =>  '3',
            'repayDate'             =>  $this->getTradeCalender(date('Y-m-d'),$info['investDaysType']),
            'payBackDate'           =>  $info['expectClearDate'],
            'redeemDaysType'        =>  $info['redeemDaysType'],
            'investDaysType'        =>  $info['investDaysType'],
            'showType'              =>  'double',
            'durationPeriodDays'    =>  $info['durationPeriodDays'],
            'labelList'             =>  $label,
            'tenThousandIncome'     =>  sprintf("%.2f",(float)(10000*(0.01*($info['baseRate']+$info['rewardRate']))*($info['durationPeriodType']=='DAY'?($info['durationPeriodDays']/$info['incomeCalcBasis']):($info['durationPeriodDays']/12)))),
            'collectedVolume'       =>  sprintf("%.2f",$info['actualRaiseAmount']), //实际募集份额
            'updateTime'            =>  $info['updateTime'],
            'maxSingleDayRedeemAmount'  => sprintf("%.2f",$info['maxSingleDayRedeemAmount']),
            'expectClearDate'       =>  $info['expectClearDate'],
            'durationPeriodType'    =>  $info['durationPeriodType'],
            'expectRaisOffDate'     =>  $info['expectRaisOffDate']
        ];
    }

    public function getProductCount(){
            return \Prj\Model\ZyBusiness\ProductInfo::getRecord("count(productId)");
    }


    public function insertProduct($loop = 0){
        $where = array();
        if($loop>0){    //自动循环扫描时，只检查最近7天有变动的产品，否则查所有的产品
            $where["]updateTime"] = date("Y-m-d",strtotime("-7 days"));
        }
        $where['!productStatus'] = ['CREATE','PENDING','PASSED','REBUTED','REVIEWED','STARTUP','INVAIN'];
        $dtloop = date("YmdHis");
        do {
            \Sooh2\Misc\Loger::getInstance()->traceLevel(0);
            $productList = \Prj\Model\ZyBusiness\ProductInfo::getRecords("*", $where, "rsort updateTime");

            if (!$productList) {
                Loger::out("没有符合的商品");
                return;
            }
            $list = array();

            foreach ($productList as $k => $v) {
                if ($v['raiseAmount'] - $v['actualRaiseAmount'] <= $v['minSingleInvestAmount']) {
                    $percent = 100;
                    if($v['productStatus'] != self::CURRENT) $v['productStatus'] = "RAISED";

                } else {
                    $percent = number_format($v['actualRaiseAmount'] / $v['raiseAmount'] * 100, 2);
                }

                $label = $this->getLabel($v['productId'], $v['productType']);    //获取标签，如果为定期，则全为扩展标签

                $records = $this->standardProductList($v, $label, $percent, $v['raiseAmount'] - $v['actualRaiseAmount']);

                if (substr_count($v['productName'], "新手标")) {
                    if($v['productStatus']!='RAISING'&&$v['productStatus']!='DOING_RAISING'){
                        $weg = $this->calcWeight($v,$percent,$label);
                        $list[$weg] = $records;
                        $list[$weg]['weight'] = $weg;
                    }else{
                        $records['state'] = $v['productStatus'];
                        $weg = self::state_newbie . "0000" . (self::Limit-strtotime( $v['startUpTime']));
                        $list[$weg] = $records;
                        $list[$weg]['weight'] = $weg;
                    }

                    continue;
                }
                if ($v['productType'] == 'CURRENT') {

                    $ret = $this->getCurrentRate($v['productId']);
                    if ($ret) {
                        $records['expAror'] = $ret['baseRate'];
                        $records['rewardInterest'] = $ret['rewardRate'];
                    }
                    $records['weight'] = 0;

                    array_unshift($list,$records);

                    continue;

                }
                $weight = $this->calcWeight($v, $percent, $label);

                if ($weight > 0) {
                    $records['weight'] = $weight;
                    $list[$weight] = $records;
                }


            }


            ksort($list);

            try {
                foreach ($list as $k => $info) {
                    $detailInfo = \Prj\Model\ZyBusiness\ProductDetail::getRecord("*", ["productId" => $info['productOid']]);
                    $assetCateInfo = $this->getAssetCate($info['productOid'], $info['productType']);    //获取资产分类信息

                    $assetInfo = $this->getAssetInfo($info['productOid'], $info['productType']);


                    $assetAllCate = $this->getAllAssetCate($info['productOid'], $info['productType']);
                    $detail = $this->standardDetailFormat($detailInfo, $info, $assetCateInfo, $assetInfo, $assetAllCate);
                    if ($info['productType'] == "CURRENT") {
                        $info['paybackName'] = $detail['paybackName'] = "复利计息";
                        $info['durationName'] = $detail['durationName'] = "灵活存取";
                        $detail['maxSingleDayRedeemAmount'] = $info['maxSingleDayRedeemAmount'];
                        $info['type'] = $detail['type'] = 'PRODUCTTYPE_02';
                        $redeemFeeRate = \Prj\Model\ZyBusiness\RedeemFeeRate::getRecord("*",['feeType'=>"RATE",'[effectTime'=>date('Y-m-d H:i:s')],'rsort createTime');
                        $detail['redeemFeeRate'] = $redeemFeeRate;
                        $info['tenThousandIncome'] = $detail['tenThousandIncome'] = sprintf("%.2f", (float)(100*($info['expAror'] + $info['rewardInterest'])/ $info['incomeCalcBasis']));
                        
                    }else{
                        $info['paybackName'] = $detail['paybackName'] = "一次性还本付息";
                    }
                    $state = $info['state'];
                    $info['state']=$detail['state'] = $this->switchStatus($info['state'],$info['productType']);
                    $where = [
                        'rowVersion=rowVersion+1',
                        'productId' => $info['productOid'],
                        'productName' => $info['name'],
                        'weight' => $info['weight'],
                        'remainMoney' => $info['remainMoney'],
                        'totalRaiseAmount' => $info['raisedTotalNumber'],
                        'labels' => "," . implode(",", $this->getLabelId($info['labelList'])) . ",",
                        'productStatus' => $state,
                        'listJson' => json_encode($info),
                        'detailJson' => json_encode($detail),
                        'durationPeriodDays' => ($this->bitDuration($info['durationPeriodType'])).$info['durationPeriodDays'],
                        'interestTotal' => sprintf("%.2f",round( $info['expAror'] + $info['rewardInterest']))
                    ];

                    array_push($this->productList,$info['productOid']);
                    $ret = Product::updateOne($where, ['productId' => $info['productOid']]);
                    if ($ret === true) {
                        array_shift($where);
                        Product::saveOne($where);

                    }
                }
                Product::deleteOne(['!productId'=>$this->productList]);
            }catch (\Exception $ex){
                Loger::out("插入产品出错#".$ex->getMessage());
            }


            if($loop>0){
                sleep($loop);
                if($dtloop !=date("YmdHis")){
                    $loop = 0;
                }
            }
        }while($loop);



    }


    public function getLabel($productId,$productType){
        $labelInfo = array();
        $labelArr = \Prj\Model\ZyBusiness\ProductLabel::getRecords("labelId",['productId'=>$productId]);

        foreach($labelArr as $k=>$v){
            $where['labelId'] = $v['labelId'];
            $where['isUsed'] = 1;
            $labelInfo[] = \Prj\Model\ZyBusiness\SystemLabel::getRecord("labelId,labelNo,labelName,labelType",$where,"sort labelNo");

        }

        return $labelInfo;
    }

    public function getLabelNo($label){
//        $arr = [];
//        foreach($label as $k=>$v){
//            $arr[$k] =  $v['labelNo'];
//        }
        $arr = array_column($label,'labelNo');
        return $arr;
    }


    public function getLabelId($label){
        Loger::outVal("label",$label);
        $arr = array_column($label,'labelId');
        Loger::outVal("labelId",$arr);
        return $arr;
    }
    protected function bitDuration($durationPeriodType){
        switch ($durationPeriodType){
            case "DAY":$flag = 1;break;
            case "MONTH":$flag = 2;break;
        }
        return $flag;
    }


    public function calcWeight($info,$percent,$label){
        $time = strtotime($info['startUpTime']);
        $matches=null;
        $prdtIndex = preg_replace('/\D/s', '', $info['productName']);   //获得产品期数
        switch (strtoupper($info['productStatus'])){
            case "DOING_RAISING":
            case 'RAISING'://募集中
                if($percent>=100){
                    $weight = self::state_full."0000".(self::Limit-$time);
                    $info['productStatus']='RAISEEND';
                }else{
                    $flgsNum = count($label);
                    //标签越多越靠前，产品期数约小约靠前
                    $pre = (9-$flgsNum).sprintf('%03d',$prdtIndex);

                    //echo $r['labelIDList'].' = '.$flgsNum;
                    $weight = self::state_buy.$pre.$time;
                }break;

            case "BLEED":$weight = self::state_full.sprintf("%04d",$prdtIndex).(self::Limit-$time);break;
            case 'REVIEWPASS':
            case 'NOTSTARTRAISE':

                //echo mb_substr($r['name'],3,-1,'utf-8')."\n";
                $weight = self::state_que.sprintf("%04d",$prdtIndex).(self::Limit-$time);
                break;
            case 'RAISEEND':
            case "DOING_RAISED":
            case 'RAISED':
                $weight = self::state_end.sprintf("%04d",$prdtIndex).(self::Limit-$time);break;
            case "DOING_BLEED":
            case "BLEED":
                $weight = self::state_blow.sprintf("%04d",$prdtIndex).(self::Limit-$time);break;
            case "DOING_SETUP":
            case "SETUP":
            case "PAYING":$weight = self::state_doing.sprintf("%04d",$prdtIndex).(self::Limit-$time);break;
//            case "STARTUP":
//                $weight = self::state_startup.sprintf("%04d",$prdtIndex).(self::Limit-$time);break;
            case "DOING_DURATIONEND":
            case "DURATIONEND":
            case 'DURATIONING':
                $weight = self::state_topay.'0000'.(self::Limit-$time);
                break;
            case "OVERDUE":
            case 'CLEARING':
            case 'CLEARED':
                $weight = self::state_done.'0000'.(self::Limit-$time);
                break;
            default:
                $weight = 0;
        }
        return $weight;
    }

    /*
     * 获取资产分类*/
    public function getAssetCate($productId,$productType){
        $ret = [];

        if($productType == self::FIX){
            $portId = PortfolioProduct::getRecord("portId",['productId'=>$productId]);

            $assetId = AssetPortfolioHold::getRecord("assetId",['portId'=>$portId['portId']],"rsort updateTime ");

            $assetCate = Asset::getRecord("assetCate",["assetId"=>$assetId['assetId']]);

            $ret = Dict::getRecord("dictId,name",['dictId'=>$assetCate['assetCate']]);

        }
        return $ret;
    }


    public function getAllAssetCate($productId,$productType){
        $ret = [];
//        if($productType == self::FIX){
            $portId = PortfolioProduct::getRecord("portId",['productId'=>$productId]);
            Loger::outVal("portId:",$portId);
            $totalCapital = Portfolio::getRecord("totalCapital",['portId'=>$portId['portId']])['totalCapital'];
            $asset = AssetPortfolioHold::getRecords("assetId,holdAmount,updateTime",['portId'=>$portId['portId']],"rsort updateTime ");
            foreach($asset as $k => $v){
                $ret[$k]['CapitalRate'] = ($v['holdAmount']/$totalCapital*100)."%";
                $usages = AssetSupplyChain::getRecord("usages",['assetId'=>$v['assetId']]);
                $ret[$k]['usages'] = !empty($usages)?$usages['usages']:'';
                $assetCate = Asset::getRecord("assetCate",["assetId"=>$v['assetId']]);
                $ret[$k]['updateTime'] = $v['updateTime'];
                $ret[$k]['CateName'] = Dict::getRecord("name",['dictId'=>$assetCate['assetCate']])['name'];

            }
//        }
        return $ret;
    }

    /*获得资产配置信息*/
    public function getAssetInfo($productId,$productType){
        $ret = [];
        if($productType == self::FIX){
            $portIdOneRow = PortfolioProduct::getRecord("portId",['productId'=>$productId]);
            if(!$portIdOneRow) return [];
            $assetId = AssetPortfolioHold::getRecord("assetId",['portId'=>$portIdOneRow['portId']],"rsort `updateTime`");
            $assetCate = \Prj\Model\Asset\Asset::getRecord("assetCate",['assetId'=>$assetId['assetId'],'status'=>'DURATION']);
            $portInfo = \Prj\Model\Asset\Portfolio::getRecord("portName,totalCapital,manageRate,trusteeRate,calcBasis,organization,bank,planName",['portId'=>$portIdOneRow['portId']]);
            switch ($assetCate['assetCate']){
                case "SUPPLY_CHAIN":{
                    $ret = AssetSupplyChain::getRecord("*",["assetId"=>$assetId['assetId']],"rsort updateTime");
                    unset($ret['drawerID']);unset($ret['borrowerID']);
                }break;
                case "MONETARY_FUND":$ret = \Prj\Model\Asset\MonetaryFund::getRecord("*",["assetId"=>$assetId['assetId']],"rsort updateTime");break;
                case "BONDS":$ret =\Prj\Model\Asset\CreditRight::getRecord("*",["assetId"=>$assetId['assetId']],"rsort updateTime");
                    unset($ret['drawerID']);unset($ret['borrowerID']);unset($ret['warrantorID']);
                    break;
            }

            $data = array_merge($ret,$portInfo);
        }else{
            $data = [];
            $portIdOneRow = PortfolioProduct::getRecord("portId",['productId'=>$productId]);
            $assetId = AssetPortfolioHold::getRecords("assetId",['portId'=>$portIdOneRow['portId']],"rsort `updateTime`");
            $portInfo = \Prj\Model\Asset\Portfolio::getRecord("*",['portId'=>$portIdOneRow['portId']]);
            $data['portInfo'] = $portInfo;
            foreach ($assetId as $k => $v){
                $assetCate = \Prj\Model\Asset\Asset::getRecord("assetCate,assetName",['assetId'=>$v['assetId'],'status'=>'DURATION']);
                switch ($assetCate['assetCate']){
                    case "SUPPLY_CHAIN":{
                        $assetData = AssetSupplyChain::getRecord("*",["assetId"=>$v['assetId']],"rsort updateTime");
                        unset($assetData['drawerID']);unset($assetData['borrowerID']);
                        $data['assetInfo'][] = $assetData;
                    }break;
                    case "MONETARY_FUND":$data['assetInfo'][] = \Prj\Model\Asset\MonetaryFund::getRecord("*",["assetId"=>$v['assetId']],"rsort updateTime");break;
                    case "BONDS":$ret =\Prj\Model\Asset\CreditRight::getRecord("*",["assetId"=>$assetId['assetId']],"rsort updateTime");
                        unset($ret['drawerID']);unset($ret['borrowerID']);unset($ret['warrantorID']);
                        $data['assetInfo'][] = $ret;
                        break;
                }
            }
        }

        return $data;
    }


    public function getTradeCalender($calender,$investDaysType){
        switch ($investDaysType){
            case "NORMAL":
                $day = date($calender,strtotime("+1 day"));
                break;
            case "WORK":
                $day = \Prj\Model\ZyBusiness\SystemCalendar::getRecord("calendarDate",['prevWorkDate'=>$calender,'isWork'=>1])['calendarDate'];
                break;
            case "TRAD":
                $day = \Prj\Model\ZyBusiness\SystemCalendar::getRecord("calendarDate",['prevTradeDate'=>$calender,'isTrade'=>1])['calendarDate'];
                break;
        }
        return $day;
    }



    public function getCurrentRate($productId){
        if(!empty($productId)){
            $ret = \Prj\Model\ZyBusiness\ProductRate::getRecord("*",['productId'=>$productId],'sort ABS(effectDate-CURDATE())');
            if(empty($ret)){
                return false;
            }
            return $ret;
        }else{
            return false;
        }
    }



    public function switchStatus($productStatus,$productType){
        switch (trim($productStatus)){
            case "DURATIONING":
                if($productType == self::CURRENT){
                    $status = $productStatus;
                }else{
                    $status = 'RAISED';
                }break;
            case "DOING_RAISING": $status = "RAISING";break;
            case "RAISING":$status = "RAISING";break;
//            case "DOING_RAISED":$status = "RAISING";break;
            default: $status = "RAISED";break;
        }
        return $status;
    }
}