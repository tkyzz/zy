<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/26
 * Time: 14:07
 */
namespace Prj\Bll;

use EasyWeChat\Core\Exception;
use Prj\EvtMsg\Sender;
use Prj\Loger;
use Prj\Model\Mimosa\Label;
use Prj\Tool\Warning;
use Sooh2\BJUI\Ini;

class SendWarningMail extends _BllBase
{
    public function crond(){
        \Prj\Loger::$prefix = '[crond]';
        if(!\Prj\Tool\Debug::isTestEnv()){
            Loger::out("此为测试环境");
            return;
        }


        \Prj\Loger::out('crond start...');
        $this->sendMail();
    }


    protected function sendMail(){
        $loopArr = [60*1,60*5,60*30];
        $APP = 'APP';
        $SERVER = "SERVER";
        $PC = "PC";
        $appDealer = \Prj\Model\Tmp\CompanyMember::getRecords("*",['*group'=>["%".$APP."%"],'statusCode'=>0]);
        $serverDealer = \Prj\Model\Tmp\CompanyMember::getRecords("*",['*group'=>["%".$SERVER."%"],'statusCode'=>0]);
        $pcDealer = \Prj\Model\Tmp\CompanyMember::getRecords("*",['*group'=>["%".$PC."%"],'statusCode'=>0]);
        $title = "警报通知";
        do{
            $params = [
                'status'    =>  0
            ];
            $total = \Prj\Model\WarningLog::getCount($params);
            if(!$total) {
                $loop = reset($loopArr);
                sleep($loop);
//                $loop = $loop*5;
            }else{
                $list = \Prj\Model\WarningLog::getRecords($params);
                $appContent = "";
                $serverContent = "";
                $pcContent = "";
                foreach($list as $k=>$v){

                    if(strpos(strtoupper($v['source']),$APP)!==false){
                        $appContent .= "设备信息为：".$v['deviceInfo'].",警报信息为：".$v['warningContent'].",错误类型为:".$v['source']."，时间为：".$v['createTime']."\n";
                    }elseif (strpos(strtoupper($v['source']),$SERVER)!==false){
                        $serverContent .= "设备信息为：".$v['deviceInfo'].",警报信息为：".$v['warningContent'].",错误类型为:".$v['source']."，时间为：".$v['createTime']."\n";
                    }elseif (strpos(strtoupper($v['source']),$PC)!==false){
                        $pcContent .= "设备信息为：".$v['deviceInfo'].",警报信息为：".$v['warningContent'].",错误类型为:".$v['source']."，时间为：".$v['createTime']."\n";
                    }
                }
                try {

                    $appAddrArr = array_column($appDealer, 'email');
                    $serverAddrArr = array_column($serverDealer, "email");
                    $pcAddrArr = array_column($pcDealer,'email');
                    $appPhoneArr = array_column($appDealer, "phone");
                    $serverPhoneArr = array_column($serverDealer, "phone");
                    $pcPhoneArr = array_column($pcDealer,'phone');
                    \Prj\Tool\Warning::getInstance()->sendMAIL($appAddrArr, $title, $appContent);
                    \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($title, $appContent, $appPhoneArr, ['smsnotice']);
                    \Prj\Tool\Warning::getInstance()->sendMAIL($serverAddrArr, $title, $serverContent);
                    \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($title, $serverContent, $serverPhoneArr, ['smsnotice']);
                    \Prj\Tool\Warning::getInstance()->sendMAIL($pcAddrArr, $title, $serverContent);
                    \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg($title, $serverContent, $pcPhoneArr, ['smsnotice']);
                    \Prj\Model\WarningLog::updateOne(['statusCode' => 0], null);
                    $loop = next($loop);
                    sleep($loop);
                }catch (Exception $ex){
                    Loger::out("发生错误！".$ex->getMessage());
                }
            }
        }while($loop>0);

    }



}