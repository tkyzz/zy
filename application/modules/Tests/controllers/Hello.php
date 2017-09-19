<?php

/**
 * 测试的hello
 *
 * @author simon.wang
 */

/**
 * @SWG\Swagger(
 *       @SWG\Tag(
 *     name="Xiaomi",
 *     description="小米活动相关页面",
 *   ),
 *
 *     @SWG\Tag(
 *     name="Coupon",
 *     description="优惠券相关接口",
 *   ),
 *
 *     @SWG\Tag(
 *     name="Userpub",
 *     description="用户注册登录相关接口",
 *   ),
 *
 *     @SWG\Tag(
 *     name="Daysign",
 *     description="签到相关接口",
 *   ),
 *
 *     @SWG\Tag(
 *     name="Member",
 *     description="用户（要求登录状态）相关接口",
 *   ),
 *
 *     @SWG\Tag(
 *     name="Activity",
 *     description="活动相关",
 *   ),
 * )
 */

class HelloController extends \Prj\Framework\Ctrl {
    /**
     * 获取签到历史数据
     */
    public function historyAction()
    {
        $history = array(
            'signhistory'=>array(20170601,2070602),
            'params'=> $this->_request->get('productId'),
            '_cookie'=>$_COOKIE,
        );
        $this->_view->assign('DaySignHistory',$history);
        $this->assignCodeAndMessage('success');
        $this->assignPageInfo();
    }
    /**
     * 签到
     */
    public function dosignAction()
    {
        $this->_view->assign('DaySignResult',1);
        //do call histroy
        $this->historyAction();
    }
}
