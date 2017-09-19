<?php
/**
 * 支付网关回调转发
 * @author simon.wang
 */
class PaycallbackController extends \Prj\Framework\OldApiCtrl
{
    public function forwardAction()
    {
//        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
//        phpinfo();
//        exit;
        $ctrl = new \Prj\Tool\PayProxy();
        $ctrl->http();

    }
    
}
