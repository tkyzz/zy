<?php

namespace Prj\Tool;

/**
 * Description of PayProxy
 *
 * @author simon.wang
 */
class PayProxy extends \Sooh2\Misc\ForwardCtrl{
    protected function ipWithPortlist(){
        return array(// 127.0.0.1:3456
        '10.30.232.9',//预发布 
        '10.28.97.253',//168那台
            '127.0.0.1',//.8那台
        );
    }
    /**
     * 如果是成功处理的，返回需要返回给外部调用者的信息，否则返回空串
     * @param type $retOfCrul
     * @return boolean
     */
    protected function checkRet($retOfCrul)
    {
        if($retOfCrul=='S0000000'){
            return $retOfCrul;
        }else{
            return '';
        }
    }
    /**
     * 根据情况替换接口地址（默认不换）
     * @param type $ipWithPort
     * @param type $uri
     * @return type
     */
    protected function rewriteUri($ipWithPort,$uri)
    {
        switch($uri){
            case '/payment/callback/jytPayeeCallback':
            case '/settlement/jytNoticeUrl/jytPayeeCallback':
                return 'http://'.$ipWithPort.'/ZYSettlement/callback/jytPayeeCallback';
                
            case '/settlement/jytNoticeUrl/jytPayCallback':
            case '/payment/callback/jytPayCallback':
                return 'http://'.$ipWithPort.'/ZYSettlement/callback/jytPayCallback';   
                
            case '/payment/callback/jytNetPayeeCallback':
            case '/settlement/jytNoticeUrl/jytNetPayCallback':
                return 'http://'.$ipWithPort.'/ZYSettlement/callback/jytNetPayeeCallback';
                
            default:
                return 'http://'.$ipWithPort.'/'.$uri;
        }
        
    }
}
