<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/14
 * Time: 13:26
 */

/**
 * @SWG\Swagger(
 *       @SWG\Tag(
 *     name="Xiaomi",
 *     description="小米收货地址页面",
 *   ),
 * )
 */
class XiaomiController extends \Prj\Framework\Ctrl {

    public function __construct()
    {
        \Prj\Loger::$prefix = '[Xiaomi]';
    }

    /**
     * @SWG\Post(path="/actives/xiaomi/login", tags={"Xiaomi"},
     *   summary="用户登录",
     *   description="",
     *     @SWG\Parameter(name="phone", type="string",  in="formData",
     *     description="手机号"
     *   ),
     * @SWG\Parameter(name="code", type="string",  in="formData",
     *     description="验证码"
     *   ),
     * )
     *
     */
    public function loginAction(){
        if(empty($this->_request->get('phone'))){
            return $this->assignCodeAndMessage('手机号不能为空!' , 99999);
        }
        if(empty($this->_request->get('code'))){
            return $this->assignCodeAndMessage('验证码不能为空!' , 99999);
        }
        $info = $this->getInfo();
        if(!$info){
            return $this->assignCodeAndMessage('手机号或验证码不正确!' , 99999);
        }
        $info['rewards'] = \Prj\Bll\MiActivy::getInstance()->getRewardByUcUid($info['userId']);
        $info['dateInfo'] = \Prj\Bll\MiActivy::getInstance()->getDateInfo();
        $this->_view->assign('data' , $info);
        return $this->assignCodeAndMessage('success');
    }
    /**
     * @SWG\Post(path="/actives/xiaomi/setInfo", tags={"Xiaomi"},
     *   summary="设置收获地址",
     *   description="",
     *     @SWG\Parameter(name="phone", type="string", in="formData",
     *     description="手机号"
     *   ),
     * @SWG\Parameter(name="code", type="string",  in="formData",
     *     description="验证码"
     *   ),
     *     @SWG\Parameter(name="addr", type="string",  in="formData",
     *     description="收货地址"
     *   ),
     * @SWG\Parameter(name="tel", type="string",  in="formData",
     *     description="收获电话"
     *   ),
     *     @SWG\Parameter(name="realname", type="string",  in="formData",
     *     description="收获姓名"
     *   ),
     * )
     */
    public function setInfoAction(){
        $info = $this->getInfo();
        if(!$info){
            return $this->assignCodeAndMessage('登录已过期，请重新登录!' , 99999);
        }
        $phone = $this->_request->get('phone');
        $code = $this->_request->get('code');
        $addr = $this->_request->get('addr');
        $tel = $this->_request->get('tel');
        $realname = $this->_request->get('realname');
        if(empty($realname)){
            return $this->assignCodeAndMessage('没有正确的收货人，可能收不到哦～' , 99999);
        }
        if(empty($addr) || mb_strlen($addr , 'utf-8') < 8 ){
            return $this->assignCodeAndMessage('没有正确的地址，寄到外太空了咋办？' , 99999);
        }
        if(empty($tel) || strlen($tel) < 11){
            return $this->assignCodeAndMessage('没有正确的手机号，快递小哥会联系不上你哦～' , 99999);
        }
        $ret = (new \Prj\Tmp\XiaoMiRun0622)->setinfo($phone , $code , $addr , $tel , $realname);
        if($ret){
            return $this->assignCodeAndMessage('success');
        }else{
            return $this->assignCodeAndMessage('地址设置失败!',99999);
        }
    }
    /**
     * @SWG\Post(path="/actives/xiaomi/getCode", tags={"Xiaomi"},
     *   summary="获取验证码",
     *   description="",
     *      @SWG\Parameter(name="phone", type="string", in="formData",
     *     description="手机号"
     *   ),
     * )
     */
    public function getCodeAction(){
        $phone = trim($this->_request->get('phone'));
        if(empty($phone) || strlen($phone) != 11){
            return $this->assignCodeAndMessage('手机号不正确！' , 99999);
        }
        $msg = (new \Prj\Tmp\XiaoMiRun0622)->sendvc($phone);
        if($msg === true){
            $this->assignCodeAndMessage('success');
        }else{
            \Prj\Loger::out('get code failed # '.$msg);
            $this->assignCodeAndMessage($msg , 99999);
        }

    }
    /**
     * @SWG\Post(path="/actives/xiaomi/getDate", tags={"Xiaomi"},
     *   summary="获取活动起止日期",
     *   description="",
     * )
     */
    public function getDateAction(){
        return $this->assignCodeAndMessage('success');
    }

    protected function getInfo(){
        $phone = $this->_request->get('phone');
        $code = $this->_request->get('code');
        return (new \Prj\Tmp\XiaoMiRun0622)->checkvc($phone , $code);
    }

}