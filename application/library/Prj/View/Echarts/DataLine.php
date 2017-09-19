<?php
/**
 * echart线图的数据
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/9/18
 * Time: 11:17
 */
namespace Prj\View\Echarts;

class DataLine extends \Prj\View\Echarts\_Base
{
    protected $tooltip = [ 'trigger' => 'axis' ];
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
                                0 => 'line',
                                1 => 'bar',
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
    protected $yAxis = [
        0 =>
            array (
                'type' => 'value',
                'splitArea' =>
                    array (
                        'show' => true,
                    ),
            ),
    ];

    /**
     * Hand 添加曲线名称
     * @param $lineName1
     * @param $lineName2
     * @return $this
     */
    public function addLine($lineName1 , $lineName2){
        $argsArr = func_get_args();
        foreach ($argsArr as $v){
            $this->legend['data'][] = $v;
        }
        return $this;
    }

    /**
     * Hand 添加坐标点
     * @param $x
     * @param $value1
     * @param $value2
     * @return $this
     */
    public function addPoint($x , $value1 , $value2){
        $argsArr = func_get_args();
        $this->xAxis[0]['type'] = 'category';
        $this->xAxis[0]['data'][] = $x;
        foreach ($this->legend['data'] as $k => $v){
            $this->series[$k]['name'] = $v;
            $this->series[$k]['type'] = 'line';
//            $this->series[$k]['smooth'] = true; //平滑
//            $this->series[$k]['symbol'] = 'none'; //去掉点
            $this->series[$k]['data'][] = $argsArr[$k + 1] ?: 0;
        }
        return $this;
    }


}

//        $arr = array (
//            'tooltip' =>
//                array (
//                    'trigger' => 'axis',
//                ),
//            'legend' =>
//                array (
//                    'data' =>
//                        array (
//                            0 => '男党员',
//                            1 => '女党员',
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
//                                            0 => 'line',
//                                            1 => 'bar',
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
//            'xAxis' =>
//                array (
//                    0 =>
//                        array (
//                            'type' => 'category',
//                            'data' =>
//                                array (
//                                    0 => '1月',
//                                    1 => '2月',
//                                    2 => '3月',
//                                    3 => '4月',
//                                    4 => '5月',
//                                    5 => '6月',
//                                    6 => '7月',
//                                    7 => '8月',
//                                    8 => '9月',
//                                    9 => '10月',
//                                    10 => '11月',
//                                    11 => '12月',
//                                ),
//                        ),
//                ),
//            'yAxis' =>
//                array (
//                    0 =>
//                        array (
//                            'type' => 'value',
//                            'splitArea' =>
//                                array (
//                                    'show' => true,
//                                ),
//                        ),
//                ),
//            'series' =>
//                array (
//                    0 =>
//                        array (
//                            'name' => '男党员',
//                            'type' => 'line',
//                            'data' =>
//                                array (
//                                    0 => 2,
//                                    1 => 3,
//                                    2 => 10,
//                                    3 => 7,
//                                    4 => 3,
//                                    5 => 1,
//                                    6 => 19,
//                                    7 => 9,
//                                    8 => 4,
//                                    9 => 16,
//                                    10 => 2,
//                                    11 => 14,
//                                ),
//                        ),
//                    1 =>
//                        array (
//                            'name' => '女党员',
//                            'type' => 'line',
//                            'data' =>
//                                array (
//                                    0 => 10,
//                                    1 => 6,
//                                    2 => 18,
//                                    3 => 13,
//                                    4 => 16,
//                                    5 => 17,
//                                    6 => 17,
//                                    7 => 9,
//                                    8 => 7,
//                                    9 => 7,
//                                    10 => 19,
//                                    11 => 13,
//                                ),
//                        ),
//                ),
//        );
//        echo json_encode($arr , 256);