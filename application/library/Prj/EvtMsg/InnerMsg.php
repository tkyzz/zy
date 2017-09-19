<?php
namespace Prj\EvtMsg;
/**
 * 站内信发送类
 * 不需要配置，空串即可
 *
 * @author simon.wang
 */
class InnerMsg extends \Sooh2\Messager\Sender{
    protected function init($iniString)
    {
        parse_str($iniString,$this->_ini);
    }
    /**
     * 获取本类型消息需要的用户的哪个联系方式，目前支持 phone,email,innerid,outerid
     * @return string
     */
    public function needsUserField()
    {
        return 'innerid';
    }
    /**
     * 
     * @param mixed $user 如果多个用户，以数组方式提供
     * @param string $content 内容
     * @param string $title 标题，有些情况不需要
     * @throws \ErrorException
     * @return string 消息发送结果
     */
    public function sendTo($user,$content,$title=null)
    {
        $ret = \Prj\Model\Letter::addNew($user, $title,$content);
        if($ret){
            return $ret;
        }else{
            throw new \ErrorException('please check log');
        }
    }
}
