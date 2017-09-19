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
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        $arr = array (
            'tooltip' =>
                array (
                    'trigger' => 'item',
                    'formatter' => '{a} <br/>{b} : {c} ({d}%)',
                ),
            'legend' =>
                array (
                    'orient' => 'vertical',
                    'x' => 'left',
                    'data' =>
                        array (
                            0 => '优秀党员',
                            1 => '困难党员',
                            2 => '一般党员',
                            3 => '劳模党员',
                            4 => '党员干部',
                        ),
                ),
            'toolbox' =>
                array (
                    'show' => true,
                    'feature' =>
                        array (
                            'mark' =>
                                array (
                                    'show' => true,
                                ),
                            'dataView' =>
                                array (
                                    'show' => true,
                                    'readOnly' => false,
                                ),
                            'magicType' =>
                                array (
                                    'show' => true,
                                    'type' =>
                                        array (
                                            0 => 'pie',
                                            1 => 'funnel',
                                        ),
                                    'option' =>
                                        array (
                                            'funnel' =>
                                                array (
                                                    'x' => '25%',
                                                    'width' => '50%',
                                                    'funnelAlign' => 'left',
                                                    'max' => 1548,
                                                ),
                                        ),
                                ),
                            'restore' =>
                                array (
                                    'show' => true,
                                ),
                            'saveAsImage' =>
                                array (
                                    'show' => true,
                                ),
                        ),
                ),
            'calculable' => true,
            'series' =>
                array (
                    0 =>
                        array (
                            'name' => '访问来源',
                            'type' => 'pie',
                            'radius' => '55%',
                            'center' =>
                                array (
                                    0 => '50%',
                                    1 => '60%',
                                ),
                            'data' =>
                                array (
                                    0 =>
                                        array (
                                            'value' => 35,
                                            'name' => '优秀党员',
                                        ),
                                    1 =>
                                        array (
                                            'value' => 69,
                                            'name' => '困难党员',
                                        ),
                                    2 =>
                                        array (
                                            'value' => 375,
                                            'name' => '一般党员',
                                        ),
                                    3 =>
                                        array (
                                            'value' => 30,
                                            'name' => '劳模党员',
                                        ),
                                    4 =>
                                        array (
                                            'value' => 4,
                                            'name' => '党员干部',
                                        ),
                                ),
                        ),
                ),
        );
        echo json_encode($arr , 256);
    }
}



