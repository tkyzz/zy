<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-20 10:22
 */

namespace Prj\EvtMsg;

use Sooh2\Misc\Ini;

class JavaApiPush extends \Sooh2\Messager\Sender
{
    protected $templateId = 1;

    public $conf = [
        'custom' => false, //是否开启自定义推送,使用PHP自定义模板进行推送
        'data' => [
            'type' => 1 ,               //推送客户群类型 0-全局 1-个人
            'customType' => '',          //推送客户端类型
            'templateContent' => [] ,   //透传内容 {\"content\":{\"jumpinfo\":{\"pagename\":\"currentDetail\"},\"type\":\"sign\"}}
        ]
    ];

    public function setTemplateId($id){
        $this->templateId = $id;
    }

    protected function init($iniString)
    {
        parse_str($iniString, $this->_ini);
    }

    /**
     * 获取本类型消息需要的用户的哪个联系方式，目前支持 phone,email,innerid,outerid
     * @return string
     */
    public function needsUserField()
    {
        //是否开启自定义模板的推送
        if($this->conf['custom'])return 'custom';
        return 'phone';
    }

    /**
     *
     * @param mixed $user 如果多个用户，以数组方式提供
     * @param string $content 内容
     * @param string $title 标题，有些情况不需要
     * @throws \ErrorException
     * @return string 消息发送结果
     */
    public function sendTo($user, $content, $title = null)
    {
        //是否开启自定义模板的推送
        if($this->conf['custom'])return $this->sendTo2($user , $content , $title);

        $arrRequest = [
            'data' => [
                'codeValue' => [$content],
                'customType' => 3,
                'phones' => $user,
                'templateId' => $this->templateId,
                'type' => 1,
            ],
            'reqSystem' => 'php',
            'reqTime' => time(),
        ];

        $url ='http://' . Ini::getInstance()->getIni('application.serverip.push') . Ini::getInstance()->getIni('Urls.javaApiSendPush');
        \Prj\Loger::out(__METHOD__.' 请求地址: ' .$url);
        $Curl = \Sooh2\Curl::factory();
        \Prj\Loger::out(__METHOD__.' 请求参数: ' . json_encode($arrRequest));
        $ret = $Curl->httpPost($url, json_encode($arrRequest));
        \Prj\Loger::out(__METHOD__.' 请求结果: ' .$ret);
        $ret = json_decode($ret, true);

        if ($ret && !empty($ret) && isset($ret['code']) && $ret['code'] === 10000) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Hand 自定义模板的推送
     * @param $user
     * @param $content
     * @param null $title
     * @return bool
     */
    public function sendTo2($user , $content , $title = null){
        $customType = $this->conf['data']['customType'];
        $customType = !is_array($customType) ? $customType : implode(',' , $customType);
        $templateContent = $this->conf['data']['templateContent'];
        $templateContent = !is_array($templateContent) ? $templateContent : json_encode($templateContent , 256);
        if(empty($customType))$customType = \Prj\Model\SystemPushConfig::getIdsStr();
        if(empty($this->conf['data']['templateContent'])){
            $templateContent = json_encode([
                "content"  =>  [
                    "jumpinfo" => [
                        "oid" => "",
                        "pagename" => null,
                        "url" => ""
                    ],
                    "type" => null
                ],
                "text" => $title,
                "title" => $content,
            ] , 256);
        }
        $arrRequest = [
            'data' => [
                'title' => $title,
                'titleText' => $content,
                'templateContent' => $templateContent,
                'type' => $this->conf['data']['type'],
                'userIds' => $user,
                'customType' => $customType,
            ],
            'reqSystem' => 'php',
            'reqTime' => time(),
        ];

        $url ='http://' . Ini::getInstance()->getIni('application.serverip.push') . Ini::getInstance()->getIni('Urls.javaApiSendPush2');
        \Prj\Loger::out(__METHOD__.' 请求地址: ' .$url);
        $Curl = \Sooh2\Curl::factory();
        \Prj\Loger::out(__METHOD__.' 请求参数: ' . json_encode($arrRequest , 256));
        $ret = $Curl->httpPost($url, json_encode($arrRequest , 256));
        \Prj\Loger::out(__METHOD__.' 请求结果: ' .$ret);
        $ret = json_decode($ret, true);

        if ($ret && !empty($ret) && isset($ret['code']) && $ret['code'] === 10000) {
            return true;
        } else {
            return false;
        }
    }

    public function getUser($user = []){
        foreach ($user as $k => $v){
            $u = \Prj\Model\User::getCopy($v);
            $u->load();
            $phone = $u->getField('userAcc');
            $user[$k] = \Prj\Bll\User::getInstance()->getIdForPush($phone);
        }

        return $user;
    }
}