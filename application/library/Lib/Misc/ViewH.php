<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/19
 * Time: 10:35
 */
namespace Lib\Misc;

class ViewH
{
    /**
     * 生成一个表格
     * @param $arr
     * @return string
     */
    public static function table($arr){
        $html = '<table class="table table-bordered">';
        foreach($arr as $k => $v){
            $td = 'td';
            if($k == 0)$td = 'th';
            $tmp = '<tr><'. $td .'>';
            $tmp .= implode('</'. $td .'><'. $td .'>' , $v);
            $tmp .= '</'. $td .'></tr>';
            $html .= $tmp;
        }
        $html .= '</table>';
        return $html;
    }

    /**
     * 生成一行html
     * $v[0]=宽度比例，$v[1]=内容
     * $arr = [ [3 , '<span>test</span>'],[4 , '<span>test</span>'] ]
     * @param $arr
     * @return string
     */
    public static function row($arr){
        $html = '<div class="row">';
        foreach($arr as $v){
            $html .= '<div class="col-md-'.$v[0].'">'.$v[1].'</div>';
        }
        $html .= '</div>';
        return $html;
    }

    public static function color($str , $color = 'red'){
        return '<span style="color: '.$color.'">'.$str.'</span>';
    }
}