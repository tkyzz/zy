<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/14
 * Time: 13:26
 */



class CouponController extends \Prj\Framework\Ctrl {

    public function __construct(){
        \Prj\Loger::setKv('`_`');
    }

    /**
     * @SWG\Post(path="/actives/coupon/myCoupons", tags={"Coupon"},
     *   summary="我的优惠券列表",
     *     description="返回值说明 name=红包名称,typeCH=红包类型,productsCH=适用范围,investAmountCH=起投金额
               ,amount=红包金额(元),finish=过期日",
     *
     *      @SWG\Parameter(name="pageInfo", type="string", in="formData",
     *     description="分页参数 {pageSize:10,pageNo:1}"   ),
     *     @SWG\Parameter(name="status", type="string", in="formData",
     *     description=" ['notUsed']:未使用  ['used','lock']:已使用  ['expired']:已过期 "   ),
     *
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="myCoupons_list", type="array",
     *                   @SWG\Items(
             *                 @SWG\Property(property="name", description="红包名称" , type="string"),
             *                 @SWG\Property(property="typeCH", description="红包类型", type="string"),
             *                 @SWG\Property(property="productsCH", description="适用范围", type="string"),
             *                 @SWG\Property(property="investAmountCH", description="红包金额(元)", type="string"),
             *                 @SWG\Property(property="finish", description="过期日", type="string"),
     *                      )
     *
     *
     *             )
     *         )
     *     ),
     *
     * )
     */
    public function myCouponsAction(){
        $userId = $this->getUidInSession();
        if(!$userId)return $this->assignCodeAndMessage('未登录或会话过期', 10001);
        $status = $this->_request->get('status');
        if($this->_pager == null){
            return $this->assignCodeAndMessage('分页参数不能为空' , 99999);
        }
        $params = [
            'userId' => $userId,
            'status' => $status,
            'rows' => $this->_pager->page_size,
            'page' => $this->_pager->pageid(),
        ];
        $userCoupon = \Prj\Bll\ZY\UserCoupon::getInstance();
        $listRes = $userCoupon::getInstance()->getUserCoupon($params); //查询列表
        $userCoupon::getInstance()->formatForMyCoupons($listRes); //格式化列表
        if(!\Lib\Misc\Result::check($listRes)){
            return $this->assignRes($listRes);
        }else{
            $this->_pager->total = $listRes['data']['total'];
            $this->_pager->page_count = $listRes['data']['totalPages'];
            return $this->assignRes([
                'code' => 99999,
                'data' => [
                    'myCoupons_list' => $listRes['data']['content'],
                ]
            ]);
        }
    }
    /**
     * @SWG\Post(path="/actives/coupon/proCoupons", tags={"Coupon"},
     *     description="返回值说明 name=红包名称,typeCH=红包类型,productsCH=适用范围,investAmountCH=起投金额
    ,amount=红包金额(元),finish=过期日,canUse=是否可用(1可用,0不可用)",
     *   summary="产品可用的优惠券列表",
     *     @SWG\Parameter(name="productId", type="string", in="formData",
     *     description="产品ID"   ),
     *    @SWG\Parameter(name="pageInfo", type="string", in="formData",
     *     description="分页参数 {pageSize:10,pageNo:1}"   ),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="myCoupons_list", type="array",
     *                   @SWG\Items(
     *                 @SWG\Property(property="name", description="红包名称" , type="string"),
     *                 @SWG\Property(property="typeCH", description="红包类型", type="string"),
     *                 @SWG\Property(property="productsCH", description="适用范围", type="string"),
     *                 @SWG\Property(property="investAmountCH", description="红包金额(元)", type="string"),
     *                 @SWG\Property(property="finish", description="过期日", type="string"),
     *                 @SWG\Property(property="canUse", description="是否可用(1可用,0不可用)", type="string"),
     *                      )
     *
     *
     *             )
     *         )
     *     ),
     *
     * )
     */
    public function proCouponsAction(){
        return $this->assignCodeAndMessage('停止服务!!!' , 99999);
        $userId = $this->getUidInSession();
        if(!$userId)return $this->assignCodeAndMessage('未登录或会话过期', 10001);
        if($this->_pager == null){
            return $this->assignCodeAndMessage('分页参数不能为空' , 99999);
        }
        $params = [
            'userId' => $userId,
            'rows' => $this->_pager->page_size,
            'page' => $this->_pager->pageid(),
            'productId' => $this->_request->get('productId'),
        ];
        $userCoupon = \Prj\Bll\ZY\UserCoupon::getInstance();
        $listRes = $userCoupon::getInstance()->getMyListByProId($params);
        $userCoupon::getInstance()->formatForMyCoupons($listRes); //格式化列表
        if(!\Lib\Misc\Result::check($listRes)){
            return $this->assignRes($listRes);
        }else{
            $this->_pager->total = $listRes['data']['total'];
            $this->_pager->page_count = $listRes['data']['totalPages'];
            return $this->assignRes([
                'code' => 99999,
                'data' => [
                    'myCoupons_list' => $listRes['data']['content'],
                ]
            ]);
        }
//        $data = [
//            'proCoupons_list' => array(
//                [
//                    'name' => '注册红包',
//                    'typeCH' => '代金券',
//                    'productsCH' => '仅限投资悦享盈',
//                    'investAmountCH' => '满25800元使用',
//                    'amount' => 258,
//                    'finish' => '2017-07-28',
//                    'canUse' => 1,
//                ],
//                [
//                    'name' => '注册红包',
//                    'typeCH' => '代金券',
//                    'productsCH' => '仅限投资悦享盈',
//                    'investAmountCH' => '满25800元使用',
//                    'amount' => 258,
//                    'finish' => '2017-06-24',
//                    'canUse' => 0,
//                ],
//            ),
//            'pageInfo' => [
//                'pageNo' => 1,
//                'pageSize' => 10,
//                'totalPage' => 1,
//                'totalSize' => 2
//            ],
//        ];
//
//        $this->_view->assign('data' , $data);

    }

    /**
     * @SWG\Post(path="/actives/coupon/useCdkey", tags={"兑换码"},
     *     description="",
     *   summary="兑换码兑换",
     *     @SWG\Parameter(name="words", type="string", in="formData",
     *     description="兑换码"   )
     *
     * )
     */
    public function useCdkeyAction(){
        $userId = $this->getUidInSession();
        if(empty($userId))return $this->assignCodeAndMessage(null, 10001);

        $words = $this->_request->get('words');
        $words = strtoupper(trim($words));

        $res = \Prj\Bll\Cdkey::getInstance()->useKey([
            'userId' => $userId,
            'words' => $words,
        ]);

        $this->assignRes($res);
    }

    /**
     * @SWG\Post(path="/actives/coupon/getCdkey", tags={"兑换码"},
     *     description="",
     *   summary="获取兑换码",
     *     @SWG\Parameter(name="cdkeyId", type="string", in="formData",
     *     description="兑换活动ID"   )
     *
     * )
     */
    public function getCdkeyAction(){
        $userId = $this->getUidInSession();
        $cdkeyId = $this->_request->get('cdkeyId');
        if(empty($userId))return $this->assignCodeAndMessage(null, 10001);

        $res = \Prj\Bll\Cdkey::getInstance()->getKey([
            'userId' => $userId,
            'cdkeyId' => $cdkeyId,
        ]);

        $this->assignRes($res);
    }







    /**
     * @SWG\Post(path="/actives/coupon/detail", tags={"Coupon"},
     *   summary="优惠券详情",
     *     @SWG\Parameter(name="ucId", type="string", in="formData",
     *     description="代金券id"   ),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="data", type="array",
     *                   @SWG\Items(
     *                 @SWG\Property(property="userId", description="用户id" , type="string"),
     *                 @SWG\Property(property="name", description="优惠券", type="string"),
     *                 @SWG\Property(property="couponType", description="类型；REDPACKETS-现金红包 COUPON-优惠券 RATECOUPON-加息券", type="string"),
     *                 @SWG\Property(property="couponStatus", description="状态，只返回NOTUSED,表示未使用", type="string"),
     *                 @SWG\Property(property="couponAmount", description="卡券额度", type="string"),
     *                  @SWG\Property(property="limitLabels", description="限制标签", type="string"),
     *                  @SWG\Property(property="isLimitLabel", description="是否限制标签", type="string"),
     *                  @SWG\Property(property="chargeAmount", description="充值金额", type="string"),
     *                  @SWG\Property(property="CouponTypeCh", description="中文类型", type="string"),
     *                  @SWG\Property(property="recommendProducts", description="推荐产品列表", type="array"),
     *                      )
     *
     *
     *             )
     *         )
     *     ),
     *
     * )
     */
    public function detailAction(){
        $userId = $this->getUidInSession();
        if(empty($userId)) return $this->assignCodeAndMessage("未登录或会话超时！", 10001);
        $couponId = $this->_request->get("ucId");
        if(empty($couponId)) return $this->assignCodeAndMessage("未传入优惠券id",99999);

        $contractId = $this->_request->get("contractId");
        $version = $this->_request->get("version");
        $channelOid = \Prj\Bll\Channel::getInstance()->getChannelId($contractId,$version);
        $channelInfo = \Prj\Bll\Product::getInstance()->getChannelInfoCopy($channelOid);
        if(empty($channelInfo)){
            $this->_view->assign('data' ,[]);
            return;
        }

        $parameters = [
            'channelId' => $channelInfo['channelId'],
            'isUsed'    =>  1
        ];

        $productIdList = \Prj\Bll\Product::getInstance()->getProductByChannel($parameters);
        $productIdList = array_column($productIdList,'productId');

        $params = [
            'ucId'  =>  $couponId,
            'userId'    =>  $userId,
            'productId' =>  $productIdList
        ];
        $coupon = \Prj\Bll\Coupon::getInstance()->getCouponDetail($params);
        $this->assignRes($coupon);

    }
}