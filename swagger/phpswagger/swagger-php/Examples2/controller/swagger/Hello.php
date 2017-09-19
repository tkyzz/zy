<?php

/**
 * 测试的hello
 *
 * @author simon.wang
 */
class HelloController extends \Prj\Framework\Ctrl {
	 /** 
     * @SWG\Get(
	 *	 path="/hello/history", 
	 *   tags={"User"}, 
	 *   operationId="historyAction",
     *   summary="Get测试接口", 
     *   description="sooh2Get测试接口", 
     *   @SWG\Parameter(name="productId", type="string", required=true, in="formData", 
     *     description="产品id" 
     *   ), 
     * ) 
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
     * @SWG\POST(
	 *	 path="/hello/history", 
	 *   tags={"User"}, 
	 *   operationId="dosignAction",
     *   summary="Post测试接口", 
     *   description="sooh2Post测试接口", 
     *   @SWG\Parameter(name="productId", type="string", required=true, in="formData", 
     *     description="产品id" 
     *   ), 
     * ) 
     */  
    public function dosignAction()
    {
        $this->_view->assign('DaySignResult',1);
        //do call histroy
        $this->historyAction();
    }
}
