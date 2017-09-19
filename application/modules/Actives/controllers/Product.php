<?php

/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/31
 * Time: 16:16
 */
class ProductController extends \Prj\Framework\Ctrl
{
    public function __construct()
    {
        \Prj\Loger::setKv('`_`');
    }


    /**
     * @SWG\Post(path="/actives/product/getRegularProductList", tags={"Product"},
     *   summary="定期产品列表",
     *
     *      @SWG\Parameter(name="durationPeriodDays", type="string", in="formData",
     *     description="投资期限（可填）"   ),
     *     @SWG\Parameter(name="pageInfo", type="string", in="formData",
     *     description="分页参数 {pageSize:10,pageNo:1}"   ),
     *     @SWG\Parameter(name="interestTotal", type="string", in="formData",
     *     description="预计年化收益（可填）"   ),
     *
     *
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="product_list", type="array",
     *                   @SWG\Items(
     *                 @SWG\Property(property="productId", description="产品id号" , type="string"),
     *                 @SWG\Property(property="productNo", description="产品编号", type="string"),
     *                 @SWG\Property(property="productType", description="产品类型(REGULAR代表定期，CURRENT代表活期)", type="string"),
     *                 @SWG\Property(property="name", description="产品名称", type="string"),
     *                 @SWG\Property(property="investMin", description="最小投资额", type="string"),
     *                 @SWG\Property(property="expAror", description="基础利率", type="string"),
     *                 @SWG\Property(property="rewardInterest", description="奖励利率", type="string"),
     *                 @SWG\Property(property="remainMoney", description="剩余投资额", type="string"),
     *                 @SWG\Property(property="percent", description="已售百分比", type="string"),
     *                 @SWG\Property(property="state", description="状态，(RAISING-募集中，，RAISED-已售罄", type="string"),
     *                 @SWG\Property(property="maxTotalAmount", description="最大总投资额", type="string"),
     *                 @SWG\Property(property="maxSaleVolume", description="最大单笔投资额", type="string"),
     *                 @SWG\Property(property="incomeCalcBasis", description="年计息天数", type="string"),
     *                 @SWG\Property(property="labelList", description="标签列表(labelNo-标签编号，labelName-标签名,labelType-标签类型：extend为扩展，general为基础)", type="array"),
     *                 @SWG\Property(property="tenThousandIncome", description="每万元收益", type="string"),
     *                 @SWG\Property(property="collectedVolume", description="实际募集份额", type="string"),
     *                 @SWG\Property(property="maxSaleVolume", description="最大单笔投资额", type="string"),
     *                 @SWG\Property(property="durationPeriodType", description="募集期类型（MONTH表示以月计算，DAY表示以天计算）", type="string"),
     *                      )
     *
     *
     *             ),
     *     @SWG\Property(property="durationPeriodDays", type="array",description="投资期限"),
     *     @SWG\Property(property="interestTotal", type="array",description="预期年化收益"),
     *         )
     *     ),
     *
     * )
     */
    public function getRegularProductListAction()
    {
        $userId = $this->getUidInSession();
//        if (!$userId) $this->assignCodeAndMessage("未登录或会话超时！", 10001);
//        $channelOid = $this->_request->get("channelOid");
        $contractId = $this->_request->get("contractId");
        if(!isset($contractId)) $contractId = $this->_request->get("channelId");

        $version = $this->_request->get("version");
        $channelOid = \Prj\Bll\Channel::getInstance()->getChannelId($contractId,$version);

        if ($this->_pager == null) {
            return $this->assignCodeAndMessage("分页参数不能为空", 99999);
        }
        $durationPeriodDays = $this->_request->get("durationPeriodDays");
        $interestTotal = $this->_request->get("interestTotal");
        $list = ['code'=>10000,'data'=>['content'=>[]]];
        $channelInfo = \Prj\Bll\Product::getInstance()->getChannelInfoCopy($channelOid);

        if(empty($channelInfo)){
            $this->_view->assign('data' ,$list);
            return;
        }
        $parameters = [
            'channelId' => $channelInfo['channelId'],
            'isUsed'    =>  1
        ];


        $productIdList = \Prj\Bll\Product::getInstance()->getProductByChannel($parameters);

        $productIdList = array_column($productIdList,'productId');

        if (!empty($durationPeriodDays)) $params['durationPeriodDays'] = $durationPeriodDays;
        if (!empty($interestTotal)) $params['interestTotal'] = $interestTotal;
        if(empty($productIdList)){
            $this->_view->assign('data' ,$list);
            return;
        }
        $params['productId'] = $productIdList;
        $params['rows'] = $this->_pager->page_size;
        $params['page'] = $this->_pager->pageid();
        $list = \Prj\Bll\Product::getInstance()->getProductList($params);

        if (!\Lib\Misc\Result::check($list)) {
            return $this->assignRes($list);
        } else {
            $this->_pager->total = $list['data']['total'];
            $this->_pager->page_count = $list['data']['totalPages'];
            $data = [
                'data' => [
                    'content' => $list['data']['content'],
                ]
            ];
            if (isset($list['data']['durationPeriodDays'])) $data['data']['durationPeriodDays'] = $list['data']['durationPeriodDays'];

            if (isset($list['data']['interestTotal'])) $data['data']['interestTotal'] = $list['data']['interestTotal'];

            return $this->assignRes($list);


        }

    }









    /**
     * @SWG\Post(path="/actives/product/getRegularDetail", tags={"Product"},
     *   summary="定期产品详情",
     *
     *     @SWG\Parameter(name="productOid", type="string", in="formData",
     *     description="产品id值"   ),
     *
     *
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="content", type="array",
     *                   @SWG\Items(
     *                 @SWG\Property(property="oid", description="产品id号" , type="string"),
     *                 @SWG\Property(property="type", description="产品类型", type="string"),
     *                 @SWG\Property(property="state", description="状态，(RAISING-募集中，，RAISED-已售罄", type="string"),
     *                 @SWG\Property(property="productCode", description="产品编号", type="string"),
     *                 @SWG\Property(property="productName", description="产品名称", type="string"),
     *                 @SWG\Property(property="annualInterestSec", description="基础收益率百分比", type="string"),
     *                 @SWG\Property(property="expAror", description="基础利率", type="string"),
     *                 @SWG\Property(property="rewardInterest", description="奖励利率", type="string"),
     *                 @SWG\Property(property="tenThousandIncome", description="万元投资预期收益", type="string"),
     *                 @SWG\Property(property="raisePeriodDays", description="募集期", type="string"),
     *                 @SWG\Property(property="labelList", description="标签列表(labelNo-标签编号，labelName-标签名,labelType-标签类型：extend为扩展，general为基础)", type="array"),
     *                 @SWG\Property(property="incomeCalcBasis", description="年计息天数", type="string"),
     *                 @SWG\Property(property="percent", description="已募集份额百分率（小数）", type="string"),
     *                 @SWG\Property(property="collectedVolume", description="实际募集份额", type="string"),
     *                 @SWG\Property(property="investMin", description="投资最小金额", type="string"),
     *                 @SWG\Property(property="investAdditional", description="投资附加额", type="string"),
     *                 @SWG\Property(property="durationPeriodDays", description="投资期限", type="string"),
     *                 @SWG\Property(property="increaseInvestAmount", description="投资增加倍数", type="string"),
     *                 @SWG\Property(property="maxTotalAmount", description="单人最大募集总额", type="string"),
     *                 @SWG\Property(property="maxSaleVolume", description="单人最大单笔投资额", type="string"),
     *                 @SWG\Property(property="netUnitShare", description="理财计划份额", type="string"),
     *                 @SWG\Property(property="financer", description="融资方名", type="string"),
     *                 @SWG\Property(property="financerCapital", description="融资方注册资本", type="string"),
     *                 @SWG\Property(property="financerDesc", description="融资方简介", type="string"),
     *                 @SWG\Property(property="warrantor", description="担保方", type="string"),
     *                 @SWG\Property(property="warrantorDesc", description="担保方简介", type="string"),
     *                 @SWG\Property(property="usages", description="资金用途", type="string"),
     *                 @SWG\Property(property="repaySource", description="还款来源", type="string"),
     *                 @SWG\Property(property="risk", description="风控措施", type="string"),
     *                 @SWG\Property(property="assetCate", description="资产类型编号", type="string"),
     *                 @SWG\Property(property="assetCateName", description="资产类型名", type="string"),
     *                 @SWG\Property(property="borrower", description="债务人名称", type="string"),
     *                 @SWG\Property(property="drawer", description="债权人名称", type="string"),
     *                 @SWG\Property(property="durationBegTime", description="开始计息时间", type="string"),
     *                 @SWG\Property(property="investTime", description="开始投资时间", type="string"),
     *                 @SWG\Property(property="payBackDate", description="还本付息时间", type="string"),
     *                 @SWG\Property(property="AssetAllCate", description="资产配置类型数组(包括资产类型，占比，资金用途)", type="array"),
     *                  @SWG\Property(property="investCompactFile", description="定向委托协议id", type="string"),
     *                  @SWG\Property(property="interestsPayBackDate", description="风险揭示书id", type="string"),
     *                  @SWG\Property(property="paybackName", description="回款方式名称", type="string"),
     *                  @SWG\Property(property="durationPeriodType", description="募集期类型（MONTH表示以月计算，DAY表示以天计算）", type="string"),
     *                      )
     *
     *
     *             )
     *         )
     *     ),
     *
     * )
     */
    public function getRegularDetailAction()
    {


        $productId = $this->_request->get("productOid");
        $params = [
            'productId' => $productId,
            'type' => "REGULAR"
        ];
        $detail = \Prj\Bll\Product::getInstance()->getProductDetail($params);

        $investParams = [
            'productId' => $productId,
            'orderType' => 'INVEST',
            'orderStatus' => "CONFIRMED"
        ];
        $detail['data']['content'] = json_decode($detail['data']['content'], true);
        $investRecords = \Prj\Model\ZyBusiness\TradOrder::getRecords("*", $investParams);

        if (!\Lib\Misc\Result::check($detail)) {
            return $this->assignRes($detail);
        } elseif ($detail['data']['content']['remainMoney']==0 && empty($investRecords)) {
            return $this->_view->assign('data' ,['content'=>[]]);

        } else {

            $detail['data']['content']['paybackName'] = "一次性还本付息";
            $detail['data']['content']['investTime'] = date("Y-m-d");
//            $detail['data']['content']['durationBegTime'] = \Prj\Bll\Product::getInstance()->getRegularCalender(date('Y-m-d'),$detail['data']['content']['investDaysType']);
            return $this->assignRes($detail);
        }

    }


    /**
     * @SWG\Post(path="/actives/product/getCurrentDetail", tags={"Product"},
     *   summary="活期产品详情",
     *
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="content", type="array",
     *                   @SWG\Items(
     *                 @SWG\Property(property="oid", description="产品id号" , type="string"),
     *                 @SWG\Property(property="type", description="产品类型", type="string"),
     *                 @SWG\Property(property="productCode", description="产品编号", type="string"),
     *                 @SWG\Property(property="productName", description="产品名称", type="string"),
     *                 @SWG\Property(property="expAror", description="基础利率", type="string"),
     *                 @SWG\Property(property="rewardInterest", description="奖励利率", type="string"),
     *                 @SWG\Property(property="remainMoney", description="剩余投资额", type="string"),
     *                 @SWG\Property(property="percent", description="已售百分比", type="string"),
     *                 @SWG\Property(property="state", description="状态，(RAISING-募集中，NOTSTARTRAISE-待售，RAISEEND-售罄.DURATIONING-续存期,CLEARED-已还款)", type="string"),
     *                 @SWG\Property(property="tenThousandIncome", description="万元投资预期收益", type="string"),
     *                 @SWG\Property(property="labelList", description="标签列表(labelNo-标签编号，labelName-标签名,labelType-标签类型：extend为扩展，general为基础)", type="array"),
     *                 @SWG\Property(property="investMin", description="投资最小金额", type="string"),
     *                 @SWG\Property(property="increaseInvestAmount", description="投资增加倍数", type="string"),
     *                 @SWG\Property(property="maxTotalAmount", description="单人最大募集总额", type="string"),
     *                 @SWG\Property(property="maxSaleVolume", description="单人最大单笔投资额", type="string"),
     *                 @SWG\Property(property="netUnitShare", description="理财计划份额", type="string"),
     *                 @SWG\Property(property="singleDailyMaxRedeem", description="单人单日最大赎回金额", type="string"),
     *                 @SWG\Property(property="investTime", description="今日申购时间", type="string"),
     *                 @SWG\Property(property="interestsFirstDate", description="收益计算时间", type="string"),
     *                 @SWG\Property(property="interestsPayBackDate", description="收益发放时间", type="string"),
     *                  @SWG\Property(property="investCompactFile", description="定向委托协议id", type="string"),
     *                  @SWG\Property(property="interestsPayBackDate", description="风险揭示书id", type="string"),
     *                  @SWG\Property(property="paybackName", description="回款方式名称", type="string"),
     *                      )
     *
     *
     *             )
     *         )
     *     ),
     *
     * )
     */
    public function getCurrentDetailAction()
    {
        $productIdList = $this->_request->get("productOid");
        if(empty($productIdList)){
            $contractId = $this->_request->get("contractId");
            if(!isset($contractId)) $contractId = $this->_request->get("channelId");
            $version = $this->_request->get("version");
            $channelOid = \Prj\Bll\Channel::getInstance()->getChannelId($contractId,$version);
            $channelInfo = \Prj\Bll\Product::getInstance()->getChannelInfoCopy($channelOid);
            if(empty($channelInfo)){
                $this->_view->assign('data' ,['content'=>[]]);
                return;
            }
            $parameters = [
                'channelId' => $channelInfo['channelId'],
                'isUsed'    =>  1
            ];
            $productIdList = \Prj\Bll\Product::getInstance()->getProductByChannel($parameters);

            $productIdList = array_column($productIdList,'productId');

        }

        $params = [
            'type' => "CURRENT",
            'productId' =>  $productIdList
        ];

        $detail = \Prj\Bll\Product::getInstance()->getProductDetail($params);


        if (!\Lib\Misc\Result::check($detail)) {
            return $this->assignRes($detail);
        } else {

            $detail['data']['content'] = json_decode($detail['data']['content'], true);
            $detail['data']['content']['paybackName'] = "复利计息";
            $detail['data']['content']['investTime'] = date('Y-m-d');
            $detail['data']['content']['interestsFirstDate'] = \Prj\Bll\Product::getInstance()->getTradeCalender(date("Y-m-d"), $detail['data']['content']['investDaysType']);
            $detail['data']['content']['interestsPayBackDate'] = \Prj\Bll\Product::getInstance()->getTradeCalender($detail['data']['content']['interestsFirstDate'], $detail['data']['content']['investDaysType']);

            return $this->assignRes($detail);
        }
    }


    public function getRegularIntroAction()
    {

        $productId = $this->_request->get("productOid");
        $params = [
            'productId' => $productId,
            'type' => "REGULAR"
        ];
        $detail = \Prj\Bll\Product::getInstance()->getProductDetail($params);
        if (!\Lib\Misc\Result::check($detail)) {
            return $this->assignRes($detail);
        } else {

            $params = [
                'tplcode' => 'regularIntro',
                'status' => 1
            ];
            $detail = json_decode($detail['data']['content'], true);
            $AssetAllCate = "";
            \Prj\Loger::out($detail);
            foreach ($detail['AssetAllCate'] as $k => $v) {
                $AssetAllCate .= "<tr><td  style='width:30%;'>" . $v['CateName'] . "</td><td style='width:30%;'>" . number_format($v['CapitalRate'],2) . "%</td><td>" . $v['usages'] . "</td>";
            }
            $assetAllName = array_column($detail['AssetAllCate'],'CateName');
            $lastAssetName = array_pop($assetAllName);
            $investRange = !empty($assetAllName)?implode("、",$assetAllName)."和".$lastAssetName:$lastAssetName;

            $intro = \Prj\Bll\Product::getInstance()->getProductDetailTpl($params);
            if (empty($intro['content'])) {
                echo "";
                exit;
            }


            $protocol = '<a class="goWebView" style="color: #e45038;text-decoration: underline;" data-url="/actives/static/protocolDetail?type=regular_dingxiang&pid='.$productId.'&id='.$detail['investCompactFile'].'" data-title="定向委托投资协议">《定向委托投资协议》</a>、<a class="goWebView" style="color: #e45038;text-decoration: underline;" data-url="/actives/static/protocolDetail?type=fengxian&pid='.$productId.'&id='.$detail['riskWarnFile'].'" data-title="风险揭示书">《风险揭示书》</a>';
            $assetInfo = $detail['AssetInfo'];
            $replace = [
                '{investRange}' =>  $investRange,
                '{productName}' =>  $detail['productName'],
                '{financerCapital}' => floatval($assetInfo['financerCapital']),
                '{financer}' => $assetInfo['financer'],
                '{usages}' => $assetInfo['usages'],
                '{repaySource}' => $assetInfo['repaySource'],
                '{risk}' => $assetInfo['risk'],
                '{warrantor}' => $assetInfo['warrantor'],
                '{AssetAllCate}' => $AssetAllCate,
                '{assetCateName}' => $detail['assetCateName'],
                '{investMin}' => number_format($detail['investMin'],2),
                '{increaseInvestAmount}' => floatval($detail['increaseInvestAmount']),
                '{protocol}'    =>  $protocol
            ];
//            $replace = [
//                '{productName}' =>  $detail['productName'],
//                '{financerCapital}' => $detail['financerCapital'],
//                '{financer}' => $detail['financer'],
//                '{usages}' => $detail['usages'],
//                '{repaySource}' => $detail['repaySource'],
//                '{risk}' => $detail['risk'],
//                '{warrantor}' => $detail['warrantor'],
//                '{AssetAllCate}' => $AssetAllCate,
//                '{assetCateName}' => $detail['assetCateName'],
//                '{investMin}' => number_format($detail['investMin'],2),
//                '{increaseInvestAmount}' => floatval($detail['increaseInvestAmount']),
//                '{protocol}'    =>  $protocol
//            ];
            $introContent = str_replace(array_keys($replace), $replace, stripslashes($intro['content']));

            \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
            echo $introContent;

        }
    }


    public function getRegularIncomeAction()
    {
        $productId = $this->_request->get("productOid");
        $params = [
            'productId' => $productId,
            'type' => "REGULAR"
        ];
        $detail = \Prj\Bll\Product::getInstance()->getProductDetail($params);
        if (!\Lib\Misc\Result::check($detail)) {
            return $this->assignRes($detail);
        } else {
            $params = [
                'tplcode' => 'regularIncome',
                'status' => 1
            ];
            $detail = json_decode($detail['data']['content'], true);
            $intro = \Prj\Bll\Product::getInstance()->getProductDetailTpl($params);
            if (empty($intro['content'])) $this->assignCodeAndMessage("没有此模板信息", 10001);
            \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
            echo stripslashes($intro['content']);

        }
    }

    public function getCurrentIncomeAction()
    {
        $userId = $this->getUidInSession();
//        if (!$userId) $this->assignCodeAndMessage("未登录或会话超时", 10001);
        $productOid = $this->_request->get("productOid");
        if(empty($productOid)){
            $contractId = $this->_request->get("contractId",0000000000000000000);
            $version = $this->_request->get("version",0);
            $channelOid = \Prj\Bll\Channel::getInstance()->getChannelId($contractId,$version);
            $channelInfo = \Prj\Bll\Product::getInstance()->getChannelInfoCopy($channelOid);
            if(empty($channelInfo)){
                $this->_view->assign('data' ,['content'=>[]]);
                return;
            }
            $parameters = [
                'channelId' => $channelInfo['channelId'],
                'isUsed'    =>  1
            ];
            $productIdList = \Prj\Bll\Product::getInstance()->getProductByChannel($parameters);
            $productOid = array_column($productIdList,'productId');
        }
        $params = [
            'type' => "CURRENT",
            'productId' =>  $productOid
        ];
        $detail = \Prj\Bll\Product::getInstance()->getProductDetail($params);
        if (!\Lib\Misc\Result::check($detail)) {
            return $this->assignRes($detail);
        } else {
            $params = [
                'tplcode' => 'currentIncome',
                'status' => 1
            ];
            $detail = json_decode($detail['data']['content'], true);
            $intro = \Prj\Bll\Product::getInstance()->getProductDetailTpl($params);
            if (empty($intro['content'])) $this->assignCodeAndMessage("没有此模板信息", 10001);
            $replace = [
                '{productName}' => $detail['productName'],
            ];
            $introContent = str_replace(array_keys($replace), $replace, $intro['content']);
            \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
            echo stripslashes($introContent);

        }
    }

    public function getCurrentIntroAction()
    {

//        if (!$userId) $this->assignCodeAndMessage("未登录或会话超时", 10001);
        $productOid = $this->_request->get("productOid");
        if(empty($productOid)){
            $contractId = $this->_request->get("contractId",0000000000000000000);
            $version = $this->_request->get("version",0);
            $channelOid = \Prj\Bll\Channel::getInstance()->getChannelId($contractId,$version);
            $channelInfo = \Prj\Bll\Product::getInstance()->getChannelInfoCopy($channelOid);
            if(empty($channelInfo)){
                $this->_view->assign('data' ,['content'=>[]]);
                return;
            }
            $parameters = [
                'channelId' => $channelInfo['channelId'],
                'isUsed'    =>  1
            ];
            $productIdList = \Prj\Bll\Product::getInstance()->getProductByChannel($parameters);
            $productOid = array_column($productIdList,'productId');
        }

        $params = [
            'type' => "CURRENT",
            'productId' =>  $productOid
        ];
        $detail = \Prj\Bll\Product::getInstance()->getProductDetail($params);
        if (!\Lib\Misc\Result::check($detail)) {
            return $this->assignRes($detail);
        } else {
            $params = [
                'tplcode' => 'currentIntro',
                'status' => 1
            ];
            $detail = json_decode($detail['data']['content'], true);
            $intro = \Prj\Bll\Product::getInstance()->getProductDetailTpl($params);
            $AssetAllCate = '';
            if (!empty($detail['AssetAllCate'])) {
                foreach ($detail['AssetAllCate'] as $k => $v) {
                    $AssetAllCate .= "<tr><td>" . $v['CateName'] . "</td><td>" . number_format($v['CapitalRate'],2) . "%</td></tr>";

                }
                $AssetAllCate .="<tr><td colspan='2'>更新于：".date('Y-m-d',strtotime($detail['AssetAllCate'][0]['updateTime']))."</td></tr>";
            }


            $assetAllName = array_column($detail['AssetAllCate'],'CateName');
            $lastAssetName = array_pop($assetAllName);
            $investRange = !empty($assetAllName)?implode("、",$assetAllName)."和".$lastAssetName:$lastAssetName;

            if (empty($intro['content'])) $this->assignCodeAndMessage("没有此模板信息", 10001);
            $protocol = '<a class="goWebView" style="color: #e45038;text-decoration: underline;" data-url="/actives/static/protocolDetail?type=current_dingxiang&pid='.$detail['oid'].'&id='.$detail['investCompactFile'].'" data-title="定向委托投资协议">《定向委托投资协议》</a>、<a class="goWebView" style="color: #e45038;text-decoration: underline;" data-url="/actives/static/protocolDetail?type=fengxian&pid='.$detail['oid'].'&id='.$detail['riskWarnFile'].'" data-title="风险揭示书">《风险揭示书》</a>';
            $replace = [
                '{investRange}'   =>  $investRange,
                '{limitDay}'    =>  $detail['redeemFeeRate']['limitDay'],
                '{feeAmount}'   =>  floatval($detail['redeemFeeRate']['feeAmount']),
                '{minFee}'      =>  floatval($detail['redeemFeeRate']['minFee']),
                '{productName}' => $detail['productName'],
                '{AssetAllCate}' => $detail['AssetAllCate'],
                '{investMin}' => number_format($detail['investMin'],2),
                '{increaseInvestAmount}' => $detail['increaseInvestAmount'],
                '{maxSaleVolume}' =>number_format( $detail['maxSaleVolume'] / 10000,2),
                '{maxSingleDayRedeemAmount}' => floatval($detail['maxSingleDayRedeemAmount'] / 10000),
                '{AssetAllCate}' => $AssetAllCate,

                '{protocol}'    =>  $protocol
            ];
            $introContent = str_replace(array_keys($replace), $replace, $intro['content']);
            \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
            echo $introContent;
            
        }
    }


    public function testAction(){
        Prj\Bll\Channel::getInstance()->getChannelId();
    }


    /**
     * @SWG\Post(path="/actives/product/getInvestRecord", tags={"Product"},
     *   summary="产品详情投资记录",
     *      @SWG\Parameter(name="productOid", type="string", in="formData",
     *     description="产品id"   ),
     *     @SWG\Parameter(name="pageInfo", type="string", in="formData",
     *     description="分页参数 {pageSize:10,pageNo:1}"   ),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="content", type="array",
     *                   @SWG\Items(
     *                 @SWG\Property(property="orderId", description="订单id号" , type="string"),
     *                 @SWG\Property(property="userPhone", description="用户手机号", type="string"),
     *                 @SWG\Property(property="createTime", description="用户下单时间", type="string"),
     *                 @SWG\Property(property="orderAmount", description="订单金额", type="string"),
     *                 @SWG\Property(property="activityTypes", description="活动标识,多个标识之间以逗号隔开，famous表示一鸣惊人,hammer表示一锤定音，空表示无标识,默认为空", type="string"),
     *                      ),
     *
     *
     *             ),
     *              @SWG\Property(property="documents", type="array",
     *                   @SWG\Items(
     *                 @SWG\Property(property="title", description="文案第一行" , type="string"),
     *                 @SWG\Property(property="content", description="文案第二行", type="string"),
     *                      ),
     *
     *
     *             ),
     *         ),
     *     ),
     *
     * )
     */
    public function getInvestRecordAction(){
        $arr1 = [
            ['userId'=>1],['userId'=>2]
        ];
        $ret = array_search(['userId'=>2],$arr1);

        $productId = $this->_request->get("productOid");
        if(empty($productId)) return $this->assignCodeAndMessage("产品id不能为空！",99999);
        if ($this->_pager == null) {
            return $this->assignCodeAndMessage("分页参数不能为空", 99999);
        }
        $params['rows'] = $this->_pager->page_size;
        $params['page'] = $this->_pager->pageid();
        $params['productId'] = $productId;
        $data = \Prj\Bll\Product::getInstance()->getInvestRecord($params);
        $data['data']['content'] = $data['data']['content']?$data['data']['content']:array();

        $this->_pager->total = $data['data']['total'];
        $this->_pager->page_count = $data['data']['totalPages'];
        return $this->assignRes($data);
    }





}