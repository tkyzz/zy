<?php
/**
 * Description of Wangning
 *
 * @author simon.wang
 */
class TangaohangController extends \Rpt\Manage\ManageCtrl{
    public function indexAction()
    {
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        echo $this->getHtml();
    }

    protected function getHtml(){
        $html = <<<HTML
 <div style="mini-width:400px;height:350px" data-toggle="echarts" data-type="pie,funnel" data-url="/manage/tangaohang/echartsData2"></div>
 <div style="mini-width:400px;height:350px" data-toggle="echarts" data-type="bar,line" data-url="/manage/tangaohang/echartsData1"></div>

HTML;
        return $html;
    }

    /**
     * Hand 折线图
     */
    public function echartsData1Action(){

        $list = [
            ['20170904' , 1 , 2],
            ['20170905' , 3 , 4],
            ['20170906' , 5 , 6],
            ['20170907' , 7 , 8],
            ['20170907' , 7 , 8],
            ['20170907' , 7 , 8],
            ['20170907' , 7 , 8],
            ['20170907' , 7 , 8],
        ];
        $eData = \Prj\View\Echarts\DataLine::getInstance();
        $eData->addLine('男党员' , '女党员' , '非党员');
        foreach ($list as $v){
            $eData->addPoint($v[0] , mt_rand(0,10) ,  mt_rand(0,10) , mt_rand(0,10));
        }

        $eData->render();

    }

    /**
     * Hand 饼图
     */
    public function echartsData2Action(){
        $list = [
            '男党员' => 10,
            '女党员' => 10,
            '非党员' => 10,
            '1党员' => 10,
            '2党员' => 10,
        ];
        $eData = \Prj\View\Echarts\DataPie::getInstance();
        foreach ($list as $k => $v){
            $eData->addItem($k , mt_rand(0 , 100));
        }
        $eData->render();
    }
}



