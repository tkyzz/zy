<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/29
 * Time: 13:49
 */

namespace Lib\Services;

use Lib\Misc\Result;

class BaobaoTree extends \Prj\Bll\_BllBase
{
    protected static $iniKey = 'pro';
    protected $rand;

//    protected static $ini = [
//        'BaobaoTree.pro.url.financeOrder' => 'http://promo.babytree.com/platformapi/financeApi/financeOrder',
//        'BaobaoTree.pro.apiSecret' => 'DIOEy?`#c[x6V4A$',
//        'BaobaoTree.pro.platform_id' => 'zhangyuelicai',
//        'BaobaoTree.pro.product_name' => '测试产品1',
//        'BaobaoTree.pro.bbt_uid' => 'u11935715693',
//
//        'BaobaoTree.test.url.financeOrder' => 'http://promo.babytree.com/platformapi/financeApi/financeOrder',
//        'BaobaoTree.test.apiSecret' => 'DIOEy?`#c[x6V4A$',
//        'BaobaoTree.test.platform_id' => 'zhangyuelicai',
//        'BaobaoTree.test.product_name' => '测试产品1',
//        'BaobaoTree.test.bbt_uid' => 'u11935715693',
//    ];

    public function __construct(){
        if(\Prj\Tool\Debug::isTestEnv()){
            self::$iniKey = 'test';
        }else{
            self::$iniKey = 'pro';
        }
    }

    protected function createTable(){
        $tbName = $this->tbName();
        $sql = <<<SQL
          CREATE TABLE if not exists $tbName (
          `oid` varchar(64) NOT NULL,
          `name` varchar(64) NOT NULL DEFAULT '' COMMENT '请求名称',
          `url` varchar(200) NOT NULL DEFAULT '' COMMENT '请求地址',
          `args` varchar(1000) NOT NULL DEFAULT '' COMMENT '请求参数',
          `response` varchar(500) NOT NULL DEFAULT '' COMMENT '响应数据',
          `statusCode` tinyint(4) NOT NULL DEFAULT '0' COMMENT '请求状态 0=新建  1=成功 -1=失败',
          `createTime` bigint(20) NOT NULL DEFAULT '0',
          PRIMARY KEY (`oid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        return \Prj\Model\User::query($sql);
    }

    protected function tbName(){
        $db = \Prj\Model\User::db();
        $tbInfo = explode('.' , $db->kvobjTable());
        $tbName = 'tb_request_tmp_0';
        $dbName = trim($tbInfo[0]);
        return $dbName.'.'.$tbName;
    }

    protected function getApiSecret(){
        $key = 'BaobaoTree.' .self::$iniKey. '.apiSecret';
        return $this->getIni($key);

    }

    protected function getApiUrlForFinanceOrder(){
        $key = 'BaobaoTree.' .self::$iniKey. '.api_financeOrder';
        $this->rand = \Lib\Misc\StringH::randStr(5);
        return $this->getIni($key) . '?rand=' . $this->rand;
    }

    /**
     * 测试环境
     * @param string $userId
     * @return mixed|string
     * @throws \Exception
     */
    public function getbbtUid($userId = ''){
        \Prj\Loger::out('用户['. $userId .']');
        if(\Prj\Tool\Debug::isTestEnv()){
            $key = 'BaobaoTree.' .self::$iniKey. '.bbt_uid';
            return $this->getIni($key);
        }

        if(empty($userId))throw new \Exception('userId not allow null' , 99999);
        $tbName = 'tb_user_final_0';
        $userFinal = \Prj\Model\User::db()->getRecord(\Prj\Model\User::getTbname($tbName) , '*' , ['uid' => $userId]);
        if(!count($userFinal)){
            \Prj\Loger::out($tbName.' 查无记录 by where uid = '.$userId , LOG_ERR);
            return '';
        }
        $otherArgs = $userFinal['otherArgs'];
        if(empty($otherArgs)){
            \Prj\Loger::out('查无 otherArgs 信息!');
            return '';
        }
        $otherArgs = urldecode($otherArgs);
        if(substr($otherArgs , 0 , 1) == '?'){
            $otherArgs = substr_replace($otherArgs , '' , 0 , 1);
        }
        parse_str($otherArgs , $args);
        \Prj\Loger::out($otherArgs);
        \Prj\Loger::out(json_encode($args , 256));
        if(empty($args) && !isset($args['openid']) && empty($args['openId'])){
            \Prj\Loger::out('未查询到[宝宝树]的 openId !');
            return '';
        }
        return $args['openId'] ? $args['openId'] : $args['openid'];
    }

    /**
     * 测试环境
     * @param $productId
     * @return mixed|string
     */
    protected function getProductName($productId){
        if(!\Prj\Tool\Debug::isTestEnv()){
            $product = \Prj\Model\MimosaProduct::getCopy($productId);
            $product->load();
            return $this->setName($product->getField('name'));
        }
        $key = 'BaobaoTree.' .self::$iniKey. '.product_name';
        return $this->getIni($key);
    }

    protected function setName($name){
        \Prj\Loger::out('产品名称：'.$name);
        $nameArr = explode(',' , \Sooh2\Misc\Ini::getInstance()->getIni('BaobaoTree.pro.product_name'));
        \Prj\Loger::out('产品限制：'. implode(',' , $nameArr));
        foreach ($nameArr as $v){
            if(strpos($name , $v) !== false){
                return $v;
            }
        }
        return '';
    }

    protected function getPlatformId(){
        $key = 'BaobaoTree.' .self::$iniKey. '.platform_id';
        return $this->getIni($key);
    }

    protected function getIni($key){
        $value = \Sooh2\Misc\Ini::getInstance()->getIni($key);
        if(empty($value))$this->fatalErr('ini配置错误['. $key .']');
        return $value;
    }

    public function sendOrder($orderInfo = []){
        if(!Result::paramsCheck($orderInfo , ['oid','investorOid','productOid','orderAmount'])){
            return Result::get(RET_ERR , '参数错误['.Result::$errorParam.']');
        }
        $this->createTable();
        $url = $this->getApiUrlForFinanceOrder(); //获取请求api地址
        $userInfo = $this->getUserInfo($orderInfo['investorOid']);
        $productName = $this->getProductName($orderInfo['productOid']);
        if(empty($productName))return Result::get(RET_ERR , '产品名称不在白名单！');
        $p_id = $this ->getPlatformId();
        $data = [
            'order_num' => $orderInfo['oid'],
            'cellphone' => $userInfo['phone'],
            'bbt_uid' => $userInfo['bbtUid'],
            'product_name' => $productName,
            'platform_id' => $p_id,
            'pay_price' => $orderInfo['orderAmount'],
            'order_time' => strtotime($orderInfo['createTime']),
        ];
        $data['token'] = $this->getSign($data , ['bbt_uid','order_time','platform_id','rand']);

        $curl = \Sooh2\Curl::factory();
        $oid = \Lib\Misc\StringH::createOid('req');
        $ret = $this->addRuqData([
            'oid' => $oid,
            'name' => '宝宝树订单上报',
            'url' => $url,
            'args' => json_encode($data , 256),
            'statusCode' => 0,
        ]);
        if(!$ret){
            return Result::get(RET_ERR , '请求记录入库失败!');
        }
        \Prj\Loger::out('请求地址: '.$url);
        $reqRet = $curl->httpPost($url , $data);
        \Prj\Loger::out('请求参数: '.json_encode($data , 256));
        \Prj\Loger::out('返回值: '.$reqRet);
        $updateData = ['response' => $reqRet];
        $reqArr = json_decode($reqRet , true);
        if($reqArr && ($reqArr['data'] === 0 || $reqArr['data'] === '0') ){
            //成功
            $updateData['statusCode'] = 1;
            $ret = $this->updRuqData($updateData , ['oid' => $oid]);
            if(!$ret)\Prj\Loger::out('致命错误,上报成功,更新失败!!! reqOid: '.$oid , LOG_ERR);
            \Prj\Loger::out('更新结果');
            \Prj\Loger::out($ret);
            \Prj\Loger::out('上报成功!');
            \Prj\Loger::out('-------------------------');
            return Result::get(RET_SUCC , '上报成功!');
        }else{
            //失败
            $updateData['statusCode'] = -1;
            $ret = $this->updRuqData($updateData , ['oid' => $oid]);
            if(!$ret) \Prj\Loger::out('警告,上报失败,更新失败!!! reqOid: '.$oid , LOG_ERR);
            \Prj\Loger::out('更新结果');
            \Prj\Loger::out($ret);
            return Result::get(RET_ERR , '上报失败#' . $reqArr['status']);
        }

    }

    protected function getUserInfo($investorOid){
        \Prj\Loger::out('investorOid: '.$investorOid);
        $miUser = \Prj\Model\MimosaUser::getCopy($investorOid);
        $miUser->load();
        $ucUid = $miUser->getField('userOid');

        \Prj\Loger::out('ucUid: '.$ucUid);
        $ucUser = \Prj\Model\User::getCopy($ucUid);
        $ucUser->load();
        if(!$ucUser->exists())throw new \Exception('用户不存在!' , 99999);
        $info['phone'] = $ucUser->getField('userAcc');
        $info['bbtUid'] = $this->getbbtUid($ucUid);
        return $info;
    }

    protected function addRuqData($data){
        $db = \Prj\Model\User::db();
        $data['createTime'] = date('YmdHis');
        return $db->addRecord($this->tbName() , $data);
    }

    protected function updRuqData($data , $where = []){
        $db = \Prj\Model\User::db();
        return $db->updRecords($this->tbName() , $data , $where);
    }

    protected function getSign($data , $keys = []){
        $data['rand'] = $this->rand;
        $tmp = [];
        foreach ($keys as $v){
            $tmp[$v] = $data[$v];
        }
        ksort($tmp);
        $str = http_build_query($tmp) . $this->getApiSecret();
        \Prj\Loger::out('参数拼接结果: '.$str);
        return md5($str);
    }
}