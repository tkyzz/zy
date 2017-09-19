<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/9/16
 * Time: 13:30
 */
namespace Prj\View\Echarts;

class DataPie extends \Prj\View\Echarts\_Base
{
    protected $tooltip = [
        'trigger' => 'item',
        'formatter' => '{a} <br/>{b} : {c} ({d}%)',
    ];

    protected $legend = [
        'orient' => 'vertical',
        'x' => 'left',
        'data' => []
    ];

    protected $toolbox = [
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
    ];

    protected $series = [
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
                'data' => []
            ),
    ];

    /**
     * Hand 添加大饼
     * @param $name
     * @param $value
     * @return $this
     */
    public function addItem($name , $value){
        $this->legend['data'][] = $name;
        $this->series[0]['data'][] = [
            'name' => $name,
            'value' => $value,
        ];
        return $this;
    }
}










//        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
//        $arr = array (
//            'tooltip' =>
//                array (
//                    'trigger' => 'item',
//                    'formatter' => '{a} <br/>{b} : {c} ({d}%)',
//                ),
//            'legend' =>
//                array (
//                    'orient' => 'vertical',
//                    'x' => 'left',
//                    'data' =>
//                        array (
//                            0 => '优秀党员',
//                            1 => '困难党员',
//                            2 => '一般党员',
//                            3 => '劳模党员',
//                            4 => '党员干部',
//                        ),
//                ),
//            'toolbox' =>
//                array (
//                    'show' => true,
//                    'feature' =>
//                        array (
//                            'mark' =>
//                                array (
//                                    'show' => true,
//                                ),
//                            'dataView' =>
//                                array (
//                                    'show' => true,
//                                    'readOnly' => false,
//                                ),
//                            'magicType' =>
//                                array (
//                                    'show' => true,
//                                    'type' =>
//                                        array (
//                                            0 => 'pie',
//                                            1 => 'funnel',
//                                        ),
//                                    'option' =>
//                                        array (
//                                            'funnel' =>
//                                                array (
//                                                    'x' => '25%',
//                                                    'width' => '50%',
//                                                    'funnelAlign' => 'left',
//                                                    'max' => 1548,
//                                                ),
//                                        ),
//                                ),
//                            'restore' =>
//                                array (
//                                    'show' => true,
//                                ),
//                            'saveAsImage' =>
//                                array (
//                                    'show' => true,
//                                ),
//                        ),
//                ),
//            'calculable' => true,
//            'series' =>
//                array (
//                    0 =>
//                        array (
//                            'name' => '访问来源',
//                            'type' => 'pie',
//                            'radius' => '55%',
//                            'center' =>
//                                array (
//                                    0 => '50%',
//                                    1 => '60%',
//                                ),
//                            'data' =>
//                                array (
//                                    0 =>
//                                        array (
//                                            'value' => 35,
//                                            'name' => '优秀党员',
//                                        ),
//                                    1 =>
//                                        array (
//                                            'value' => 69,
//                                            'name' => '困难党员',
//                                        ),
//                                    2 =>
//                                        array (
//                                            'value' => 375,
//                                            'name' => '一般党员',
//                                        ),
//                                    3 =>
//                                        array (
//                                            'value' => 30,
//                                            'name' => '劳模党员',
//                                        ),
//                                    4 =>
//                                        array (
//                                            'value' => 4,
//                                            'name' => '党员干部',
//                                        ),
//                                ),
//                        ),
//                ),
//        );
//        echo json_encode($arr , 256);