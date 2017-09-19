<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/9/16
 * Time: 13:30
 */
namespace Prj\View\Echarts;

class _Base extends \Prj\Bll\_Base
{
    protected $tooltip = [];
    protected $legend = [];
    protected $toolbox = [];
    protected $calculable = true;
    protected $xAxis = [];
    protected $yAxis = [];
    protected $series = [];

    /**
     * Hand 拼装返回值
     * @return array
     */
    protected function getDataArr(){
        $arr = [
            'tooltip' => $this->tooltip,
            'legend' => $this->legend,
            'toolbox' => $this->toolbox,
            'calculable' => true,
            'xAxis' => $this->xAxis,
            'yAxis' => $this->yAxis,
            'series' => $this->series,
        ];
        foreach ($arr as $k => $v){
            if(empty($v))unset($arr[$k]);
        }
        return $arr;
    }

    /**
     * Hand 渲染数据
     */
    public function render(){
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        echo json_encode($this->getDataArr() , 256);
    }

}