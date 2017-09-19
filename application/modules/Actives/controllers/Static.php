<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/8/16
 * Time: 14:55
 */
use \Prj\Bll\Protocol;
class StaticController extends \Prj\Framework\Ctrl {

    public function indexAction(){
        return $this->assignCodeAndMessage(10001);
    }

    /**
     * @SWG\Get(
     *     path="/actives/static/protocolDetail",
     *     tags={"Static"},
     *     summary="协议详情",
     *     description="获取协议详情（'register' =>'注册协议','recharge'=>'充值协议','buy'=>'购买协议','fengxian'=>'风险提示书','fast'=>'快捷支付','regular_dingxiang'=>'定向投资委托协议-定期','current_dingxiang'=>'定向投资委托协议-活期','service'=>'服务协议'）",
     *     @SWG\Parameter(name="id",description="协议ID",type="integer",in="query",required=false,),
     *     @SWG\Parameter(name="version",description="协议版本号",type="string",in="query",required=false,),
     *     @SWG\Parameter(name="type",description="协议类型(默认最新)",type="string",in="query",required=false,),
     *     @SWG\Parameter(name="replace",description="替换字符,字段与文档内容的字段一样",type="string",in="query",required=false,),
     *     @SWG\Response(response=200, description="successful operation",
     *         )
     *     )
     * )
     */
    public function protocolDetailAction(){
        //用户信息
        $userId = $this->getUidInSession();
//        if( empty($userId) ){
//            die('请登录！');
//        }
        $where = array();
        //版本号
        $version = $this->_request->get('version');
        //产品ID
        $productId = $this->_request->get("pid");
        //订单ID
        $orderId = $this->_request->get("orderId");
        if( !empty($version) ) {
            $where['version'] = $version;
        }
        //id
        $id = $this->_request->get('id');
        //type
        $type = $this->_request->get('type');
        if( !empty($type) ) {
            if( empty($id) && empty($version) ){
                $where = ['type' => $type];
                $orderby = "rsort createTime";
            }else{
                $where['type'] = $type;
            }
        }
        //id优先级最高
        if( !empty($id) ) {
            $where = ['id'=>$id];
        }
        if( empty($where) ){
            die('参数错误！');
        }

        //后台不需要替换
        $isManager =  $this->_request->get("manager");
        $replace = [];
        if( ($type == 'current_dingxiang' || $type == 'regular_dingxiang') && $isManager !=1 && $productId){
            $member = \Prj\Model\UserFinal::getRecord(null,['uid'=>$userId]);
            if( $orderId ){
                $order = \Prj\Model\ZyBusiness\TradOrder::getRecord('userId,productId,holdStatus,orderAmount',['orderId'=>$orderId]);
                if( $order['userId'] != $userId ){
                    die('此orderId不属于当前用户！');
                }
                $productId = $order['productId'];
            }
            $product = \Prj\Model\Product::getRecord("weight,detailJson", ['productId'=>$productId]);
            $productDetail = json_decode($product['detailJson'],true);
            // 合同编号
            $replace['{pactId}'] = ' ';
            // 产品类型 活期/定期
            if( $product['weight'] == 0 ){
                $productDetail['type'] = 'current_dingxiang';
                if( !empty($order) ){
                    $orderStatus = $order['holdStatus'];
                    $replace['{orderAmount}'] = number_format($order['orderAmount'],2);
                    if( in_array($orderStatus,['HOLDING','CLOSED','CLEARED']) ){
                        $replace['{pactId}'] = $productDetail['productCode'];
                    }
                }
            }else{
                if( !empty($order) ){
                    $orderStatus = $order['holdStatus'];
                    $replace['{orderAmount}'] = number_format($order['orderAmount'],2);
                    if( in_array($orderStatus,['HOLDING','CLOSED','CLEARED']) ){
                        $replace['{pactId}'] = $productDetail['productCode'];
                        $replace['{durationPeriodEndDate}'] = $productDetail['durationEndTime'] ? $productDetail['durationEndTime']:'收益起始日+存续期';
                        $replace['{durationBegTime}'] = $productDetail['durationBegTime'] ? $productDetail['durationBegTime']:'产品成立日';
                        $replace['{payBackDate}'] = $productDetail['payBackDate'] ? $productDetail['payBackDate']: '还本付息日';
                    }
                }
                $productDetail['type'] = 'regular_dingxiang';
            }
            $where['type'] = $productDetail['type'];
//            print_r($productDetail);
            //获取已替换内容的字段
            $replace = array_merge($replace,\Prj\Bll\Protocol::getInstance()->getProductReplaceField($productDetail));
            //投资金额
            $orderAmount = $this->_request->get('orderAmount');
            $orderAmount = $orderAmount ? $orderAmount.'.00' : ' ';
            // 有限选择订单里的金额
            $replace['{orderAmount}'] = $replace['{orderAmount}'] ? $replace['{orderAmount}']:$orderAmount;
            // 姓名
            $replace['{realname}'] = $member['realname']?Protocol::getInstance()->mbSubstringReplace($member['realname'],1,2):'';
            // 用户名
            $replace['{username}'] = $member['nickname'];
            // 身份证
            $replace['{idCard}'] = $member['certNo']?Protocol::getInstance()->mbSubstringReplace($member['certNo'],0,14):'';


        }
        $data = \Prj\Model\Protocol::getVersionDetail($where,$orderby)['content'];
        if( empty($data) ){
            die('数据为空！');
        }
//        print_r($replace);
        //常规替换
        $data = str_replace(array_keys($replace), $replace, stripslashes($data));
        //自定义遍历替换
        foreach ($_GET as $k => $v) {
            $data = str_replace("{".$k."}",$v,$data);
        }
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        echo  $data;

    }


    /**
     * @SWG\Post(
     *     path="/actives/static/getUpdateApp",
     *     tags={"Static"},
     *     summary="更新APP",
     *     produces={"application/xml", "application/json"},
     *     @SWG\Parameter(name="avReq",description="必要的认证信息(a11ae4a9c161506c602fede06260918d)",type="body",in="formData "),
     * )
     */
    public function getUpdateAppAction(){
        $data = array();
        //参数
        $params = $this->_request->get('avReq');
//        if( !isset($params['contractId'])|| !isset($params['curver']) ){
//            return $this->assignCodeAndMessage('参数错误' , 99999);
//        }
        $res = \Lib\Services\GetUpdateApp::sendUpdateApp($params);
        \Prj\Loger::outVal('sendUpdateApp' , $res);
        $data['versions'] = $res['versions'];
        $this->_view->assign('data' , $data);
        return $this->assignCodeAndMessage(10001);
    }
}