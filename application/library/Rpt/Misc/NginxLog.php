<?php
namespace Rpt\Misc;
/**
 * nginx 日志(线上专用，测试环境不部署)
 *
 * @author simon.wang
 */
class NginxLog extends \Sooh2\Misc\HttpServer\Loger{
    protected function logDefine()
    {
        return array('remove_addr','http_x_forwarded_for','time','request','code','length','referer','ua','ignore','cookie','skip');
    }
    protected function arrToErr($r,$serverId)
    {
        $e = parent::arrToErr($r, $serverId);
        if(empty($e)){
            return $e;
        }
        if($e->code==200 ||$e->code==206 || $e->code==301 || $e->code==304 || $e->code==499 ){
            return null;
        }
        if(substr($e->fullRequest,0,2)=='\\x'){
            return null;
        }
        if($this->inIgnoreList($e->errUri)){
            return null;
        }
        $e->time = strtotime($r['time']);
        $e->cdnip = $r['remove_addr'];
        $e->ip = array_shift(explode(',', $r['http_x_forwarded_for']));
        return $e;
    }
    protected function inIgnoreList($uri){
        $uri = str_replace('//', '/', $uri);
        if(substr($uri,0,4)=='/wp-'){
            return true;
        }
        $parts = explode('/', strtolower(trim($uri,'/')));
        $ext = strtolower(array_pop(explode('.',$uri)));
        $skip = array('asp','aspx','jsp','zip','rar','gz','tar','bak','map','cgi','jsf');
        if(in_array($ext,$skip)){
            return true;
        }
        $ignore = array('information',
            'plus','admin','install','console','phpmyadmin','myadmin','dede','mail','enterprise',
            'zabbix','cfide');
        if(in_array($parts[0], $ignore)){
            return true;
        }


        if(substr($uri,-10)=='/test.html'){
            return true;
        }

        //
        //  //news/html/?410'union/**/select/**/1/**/from/**/(select/**/count(*),concat(floor(rand(0)*2),0x3a,(select/**/concat(user,0x3a,password)/**/from/**/pwn_base_admin/**/limit/**/0,1),0x3a)a/**/from/**/information_schema.tables/**/group/**/by/**/a)b/**/where'1'='1.h
        
        return in_array($uri, array(
            '/actives/daysign/history',
            '/apple-touch-icon.png',
            '/apple-touch-icon-precomposed.png',
            '/shell?%75%6E%61%6D%65%20%2D%61',
//            '/information/xwdt/50.html',
//            '/information/xwdt/58.html',
//            '/information/xwdt/62.html',
//            '/information/xwdt/76.html',
//            '/information/xwdt/84.html',
//            '/information/hydt/524.html',
//            '/information/hydt/531.html',
//            '/information/hlwjr/747.html',
//            '/information/hlwjr/767.html',
//            '/information/lczs/408.html',
//            '/information/lczs/420.html',
//            '/information/lczs/1204.html',
//            '/information/lczs/1226.html',
//            '/information/lczs/1255.html',
//            '/information/lczs/1610.html',
//            '/information/lczs/1830.html',
//            '/information/lczs/1949.html',
//            '/information/lczs/5182.html',
//            '/information/mtbd/4658.html',
//            '/information/mtbd/1087.html',
            '/about/',
            '/jobs/',
            '/warning',
            '/pleasereadthis',
            '/please_read/_search',
            '/jenkins/',
            '/master-status',
            '/moo',
            '/main.do',
            '/master-status',
            '/status.taobao',
            'www.baidu.com',
            'www.baidu.com:443',
            '/guanli/',
            '/jsp/user/loginAction.do',
            '/uc_server/admin.php',
            '/manage/admin.php',
            '/manage/Login.php',
            '/manage/index.php',
            '/login.php',
            '/Login.php',
            '/mail/index.php',
            '/rs-status',
            '/invoker/JMXInvokerServlet',
            '/integration/saveGangster.action',
            '/struts2-showcase/integration/saveGangster.action',
            //'/enterprise/index.php/admin/index',
            'http://www.baidu.com/cache/global/img/gs.gif',
            '/static/fonts/glyphicons-halflings-regular.eot',
            //  '/static/js/public/maps/swiper.min.js.map',
            '/cache/global/img/gs.gif',
            '/login.htm',
            '/yjgntqazulsi',
            '/login.action',
           // '/admin/config.php',
            '/Runtime/Conf/config.php',
            '//plus/recommend.php',
            '/data.zip',
            '/jobs/',
            '/hwi/',
            '/site.zip',
            '/administrator/',
            '/admin.php',
            '/user',
            '/data/admin/ver.txt',
            '/cache/global/img/gs.gif',
            '/was5/admin/',
            '/img/jumpPcTips.png',
            '/static/images/index_icon.png',
            '/static/images/safety_bg.png',
            '/static/images/dqd.png',
            '/static/images/khb.png',
            '/h5/micro/img/mark_new.jpg ',
            '/images/gs.png',
            '/',// OPTIONS / HTTP/1.0
            '/nice%20ports%2C/Tri%6Eity.txt%2ebak',
        ));
    }
}
