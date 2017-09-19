<?php
/**
 * 后台定时任务的入口
 * /etc/crontab 里的配置方式  php /usr/local/openresty/nginx/html/php/console/run.php "request_uri=/console/evthour/runtype/crond" 2>&1 & 
 * 
 * /console/evthour  定时扫事件表的任务，是个高频任务，需要的服务器单独设置，每小时0分启动，每次运行一小时
 * /console/hourly z
 *
 * @author simon.wang
 */
class ConsoleController extends \Yaf_Controller_Abstract
{
    public function hourlyAction()
    {
        error_log(__CLASS__."->".__FUNCTION__.' start');
        $erorfile = ini_get('error_log');//防止root创建文件，别人写不了
        chmod($erorfile, 666);
        
        $ctrl = \Sooh2\Crond\Ctrl::factory(false)
                ->initCrondDir(realpath(__DIR__.'/../..')."/application/Cronds")
                ->initCmdTpl("php /usr/local/openresty/nginx/html/php/console/run.php \"request_uri=/console/hourly?task={task}&ymdh={ymdh}\"")
                ->initLoger(\Sooh2\Crond\CrondLog::getCopy(null))
                ->initNamespace('PrjCronds')
                ;

        $ctrl->runCrond($this->_request->get('task'), $this->_request->get('ymdh'));
    }

    public function manualAction()
    {
        error_log(__CLASS__."->".__FUNCTION__.' start');
        $erorfile = ini_get('error_log');//防止root创建文件，别人写不了
        if(file_exists($erorfile)){
            chmod($erorfile, 666);
        }
        echo $this->_request->get('task');
        $ctrl = \Sooh2\Crond\Ctrl::factory(false)
                ->initCrondDir(realpath(__DIR__.'/../..')."/application/Cronds")
   //             ->initCmdTpl("php console.php request_uri=/console/hourly")
                ->initLoger(\Sooh2\Crond\CrondLog::getCopy(null))
                ->initNamespace('PrjCronds')
                ;

        $ctrl->runManually($this->_request->get('task'), $this->_request->get('ymdh'));
    }
    public function evthourAction()
    {
        error_log(__CLASS__."->".__FUNCTION__.' start');
        $erorfile = ini_get('error_log');//防止root创建文件，别人写不了
        chmod($erorfile, 666);
        \Sooh2\Misc\Loger::getInstance()->traceLevel(6);
        \Sooh2\EvtQue\EvtProcess::$msSleep = \Sooh2\Misc\Ini::getInstance()->getIni('application.msloopEvt')-0;
        \Sooh2\EvtQue\EvtProcess::loopHourly('\\Prj\\Events\\','\\Sooh2\\EvtQue\\QueData');
    }
    public function manual2Action()
    {
        $ctrl = new \Rpt\OrderCheck\Investor\OdrChkTask();
        $ctrl->step_prepare(date('Ymd'));
        $ctrl->confirm(date('Ymd'));
    }
    public function testsmsAction()
    {
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('cmd');
        error_log("\n\n\n\n\n");
        \Prj\EvtMsg\Sender::getInstance()->sendEvtMsg('BindOk', 'userId001', array('{bonus}'=>'xxxx元红包'));
        \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('skip', 'Post测试 at '.date("m-d H;i:s"), 'ff8080815ca5f6d1015ca6190f410000', array('msg'), 'test');

        //\Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('skip', 'Post测试', 'ff8080815ca5f6d1015ca6190f410000', array('smsmarket'), 'test');
//        echo \Sooh2\Misc\ViewExt::getInstance()->getRenderType();
//        var_dump($tmp = $this->_request->get('runtype'));
//        $this->_view->assign('total parsed task',0);
//        $this->_view->assign('test',array(
//            'a1'=>'b1',
//            'a2'=>array('b21','b22'),
//            'a3'=>array('b33'=>array('c1','c2')),
//            'a4'=>'end',
//            
//        ));
    }

    public function remindSigninAction()
    {
        (new \Prj\Bll\Remind())->remindSignin();
    }

    public function remindUseSigninCouponAction()
    {
        (new \Prj\Bll\Remind())->remindUseSigninCoupon();
    }

    /**
     * 小米活动每天的推送
     * 添加到定时任务
     */
    public function xiaomiPushAction(){
        //todo 转移到/Cronds/Standalone/ActivityPush.php中
    }
    
    public function prechkwithdrawAction()
    {
        $file=$this->_request->get('file',null);
        $u = new \Prj\GH\GHWithdrawPreChk;
        $u->checkallAction($file);
        if($file){
            $email = new \Prj\Tool\Email();
            $email->dailyChkResultAction($file);
        }
    }



    public function getProductInfoAction(){
        $second = $this->_request->get("second",0);
        \Prj\Bll\CheckProduct::getInstance()->insertProduct($second);
    }
}
